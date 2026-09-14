"use server";

import { redirect } from "next/navigation";
import { JobStatus, Role } from "@prisma/client";
import { prisma } from "@/lib/prisma";
import {
  clearSession,
  hashPassword,
  markAttendance,
  nextNumber,
  requireProvider,
  requireShop,
  setSession,
  verifyPassword,
} from "@/lib/auth";
import { can } from "@/lib/roles";
import { toFils } from "@/lib/money";
import { moveStock } from "@/lib/stock";

function slugify(raw: string) {
  return raw
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "")
    .slice(0, 32);
}

export async function providerLogin(formData: FormData) {
  const email = String(formData.get("email") || "").toLowerCase().trim();
  const password = String(formData.get("password") || "");
  const user = await prisma.providerUser.findUnique({ where: { email } });
  if (!user || !(await verifyPassword(password, user.passwordHash))) {
    redirect("/provider/login?error=1");
  }
  await setSession({ kind: "provider", userId: user.id, email: user.email, name: user.name });
  redirect("/provider");
}

export async function providerLogout() {
  await clearSession();
  redirect("/provider/login");
}

export async function createShop(formData: FormData) {
  await requireProvider();
  const name = String(formData.get("name") || "").trim();
  const username = slugify(String(formData.get("username") || ""));
  const ownerName = String(formData.get("ownerName") || "").trim();
  const ownerEmail = String(formData.get("ownerEmail") || "").toLowerCase().trim();
  const ownerPassword = String(formData.get("ownerPassword") || "");
  if (!name || !username || !ownerEmail || ownerPassword.length < 6) {
    redirect("/provider/shops/new?error=1");
  }
  const exists = await prisma.shop.findUnique({ where: { username } });
  if (exists) redirect("/provider/shops/new?error=taken");

  const shop = await prisma.shop.create({
    data: {
      name,
      username,
      legalName: String(formData.get("legalName") || "") || null,
      trn: String(formData.get("trn") || "") || null,
      status: "active",
    },
  });
  const branch = await prisma.branch.create({
    data: { shopId: shop.id, name: "Main branch", isDefault: true },
  });
  await prisma.warehouse.create({
    data: { shopId: shop.id, branchId: branch.id, name: "Main godown" },
  });
  await prisma.user.create({
    data: {
      shopId: shop.id,
      name: ownerName || "Owner",
      email: ownerEmail,
      passwordHash: await hashPassword(ownerPassword),
      role: "owner",
    },
  });
  redirect("/provider?created=" + username);
}

export async function toggleShop(formData: FormData) {
  await requireProvider();
  const id = String(formData.get("id") || "");
  const shop = await prisma.shop.findUnique({ where: { id } });
  if (!shop) return;
  await prisma.shop.update({
    where: { id },
    data: { status: shop.status === "suspended" ? "active" : "suspended" },
  });
  redirect("/provider");
}

export async function shopLogin(username: string, formData: FormData) {
  const email = String(formData.get("email") || "").toLowerCase().trim();
  const password = String(formData.get("password") || "");
  const shop = await prisma.shop.findUnique({ where: { username } });
  if (!shop || shop.status === "suspended") redirect(`/s/${username}/login?error=shop`);
  const user = await prisma.user.findUnique({ where: { shopId_email: { shopId: shop.id, email } } });
  if (!user || !user.active || !(await verifyPassword(password, user.passwordHash))) {
    redirect(`/s/${username}/login?error=1`);
  }
  await setSession({
    kind: "shop",
    userId: user.id,
    shopId: shop.id,
    shopUsername: shop.username,
    shopName: shop.name,
    email: user.email,
    name: user.name,
    role: user.role,
  });
  await markAttendance(user.id, shop.id);
  redirect(`/s/${username}`);
}

export async function shopLogout(username: string) {
  await clearSession();
  redirect(`/s/${username}/login`);
}

export async function savePart(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "parts")) throw new Error("forbidden");
  const id = String(formData.get("id") || "");
  const data = {
    sku: String(formData.get("sku") || "").trim(),
    name: String(formData.get("name") || "").trim(),
    oemNumber: String(formData.get("oemNumber") || "") || null,
    brand: String(formData.get("brand") || "") || null,
    minQty: Number(formData.get("minQty") || 0),
    salePriceFils: toFils(String(formData.get("price") || 0)),
  };
  if (id) {
    await prisma.part.update({ where: { id }, data });
  } else {
    await prisma.part.create({ data: { ...data, shopId: s.shopId } });
  }
  redirect(`/s/${username}/parts`);
}

export async function raisePurchase(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "purchases")) throw new Error("forbidden");
  const partId = String(formData.get("partId") || "");
  const qty = Number(formData.get("qty") || 0);
  const cost = toFils(String(formData.get("unitCost") || 0));
  if (!partId || qty < 1) redirect(`/s/${username}/purchases/new?error=1`);
  const number = await nextNumber(s.shopId, "po", "PO");
  const supplierName = String(formData.get("supplier") || "").trim();
  let supplierId: string | undefined;
  if (supplierName) {
    const sup =
      (await prisma.supplier.findFirst({ where: { shopId: s.shopId, name: supplierName } })) ??
      (await prisma.supplier.create({ data: { shopId: s.shopId, name: supplierName } }));
    supplierId = sup.id;
  }
  await prisma.purchaseOrder.create({
    data: {
      shopId: s.shopId,
      supplierId,
      number,
      status: "awaiting_godown",
      notes: String(formData.get("notes") || "") || null,
      createdById: s.userId,
      lines: { create: { partId, orderedQty: qty, unitCostFils: cost } },
    },
  });
  redirect(`/s/${username}/purchases`);
}

export async function confirmReceipt(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "godown")) throw new Error("forbidden");
  const orderId = String(formData.get("orderId") || "");
  const receivedQty = Number(formData.get("receivedQty") || 0);
  const order = await prisma.purchaseOrder.findFirst({
    where: { id: orderId, shopId: s.shopId, status: "awaiting_godown" },
    include: { lines: true },
  });
  if (!order) redirect(`/s/${username}/godown`);
  const line = order.lines[0];
  const warehouse = await prisma.warehouse.findFirst({ where: { shopId: s.shopId } });
  if (!warehouse) throw new Error("No warehouse");
  await prisma.purchaseLine.update({ where: { id: line.id }, data: { receivedQty } });
  await prisma.purchaseOrder.update({
    where: { id: order.id },
    data: { status: "received", receivedById: s.userId, receivedAt: new Date() },
  });
  if (receivedQty > 0) {
    await moveStock({
      partId: line.partId,
      warehouseId: warehouse.id,
      qty: receivedQty,
      type: "receipt",
      userId: s.userId,
      note: `GRN ${order.number}`,
    });
  }
  const amount = receivedQty * line.unitCostFils;
  await prisma.vendorBill.create({
    data: { shopId: s.shopId, orderId: order.id, amountFils: amount, status: "unpaid" },
  });
  redirect(`/s/${username}/godown`);
}

export async function markVendorBill(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "bills")) throw new Error("forbidden");
  const id = String(formData.get("id") || "");
  const status = String(formData.get("status") || "") as "paid" | "unpaid";
  await prisma.vendorBill.update({
    where: { id },
    data: { status, paidAt: status === "paid" ? new Date() : null },
  });
  redirect(`/s/${username}/bills`);
}

export async function createPosSale(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "pos")) throw new Error("forbidden");
  const partId = String(formData.get("partId") || "");
  const qty = Number(formData.get("qty") || 0);
  const unit = toFils(String(formData.get("unitPrice") || 0));
  const paymentMode = String(formData.get("paymentMode") || "cash");
  const customerName = String(formData.get("customerName") || "").trim();
  if (!partId || qty < 1) redirect(`/s/${username}/pos?error=1`);
  const shop = await prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } });
  const warehouse = await prisma.warehouse.findFirst({ where: { shopId: s.shopId } });
  if (!warehouse) throw new Error("No warehouse");
  await moveStock({
    partId,
    warehouseId: warehouse.id,
    qty,
    type: "sale",
    userId: s.userId,
    note: "POS sale",
  });
  let customerId: string | undefined;
  if (customerName) {
    const c =
      (await prisma.customer.findFirst({ where: { shopId: s.shopId, name: customerName } })) ??
      (await prisma.customer.create({ data: { shopId: s.shopId, name: customerName, type: "account" } }));
    customerId = c.id;
  }
  const subtotal = unit * qty;
  const vat = Math.round(subtotal * (shop.vatPercent / 100));
  const number = await nextNumber(s.shopId, "inv", "INV");
  await prisma.invoice.create({
    data: {
      shopId: s.shopId,
      customerId,
      number,
      status: paymentMode === "cash" || paymentMode === "card" ? "paid" : "unpaid",
      paymentMode,
      subtotalFils: subtotal,
      vatFils: vat,
      totalFils: subtotal + vat,
      salespersonId: s.userId,
      lines: { create: { partId, qty, unitPriceFils: unit, lineTotalFils: subtotal } },
    },
  });
  redirect(`/s/${username}/invoices`);
}

export async function saveCustomer(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "customers")) throw new Error("forbidden");
  await prisma.customer.create({
    data: {
      shopId: s.shopId,
      name: String(formData.get("name") || "").trim(),
      phone: String(formData.get("phone") || "") || null,
      trn: String(formData.get("trn") || "") || null,
      type: (String(formData.get("type") || "account") as "walk_in" | "account" | "wholesale") || "account",
    },
  });
  redirect(`/s/${username}/customers`);
}

export async function saveStaff(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "staff")) throw new Error("forbidden");
  const email = String(formData.get("email") || "").toLowerCase().trim();
  const password = String(formData.get("password") || "password");
  await prisma.user.create({
    data: {
      shopId: s.shopId,
      name: String(formData.get("name") || "").trim(),
      email,
      passwordHash: await hashPassword(password),
      role: String(formData.get("role") || "cashier") as Role,
      monthlySalaryFils: toFils(String(formData.get("salary") || 0)),
      incentivePercent: Number(formData.get("incentive") || 0),
    },
  });
  redirect(`/s/${username}/staff`);
}

export async function adjustStock(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "stock")) throw new Error("forbidden");
  const partId = String(formData.get("partId") || "");
  const qty = Number(formData.get("qty") || 0);
  const warehouse = await prisma.warehouse.findFirst({ where: { shopId: s.shopId } });
  if (!warehouse) throw new Error("No warehouse");
  await moveStock({
    partId,
    warehouseId: warehouse.id,
    qty,
    type: "adjustment",
    userId: s.userId,
    note: String(formData.get("note") || "Damage"),
  });
  redirect(`/s/${username}/stock`);
}

export async function saveJob(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "workshop")) throw new Error("forbidden");
  await prisma.jobCard.create({
    data: {
      shopId: s.shopId,
      vehicle: String(formData.get("vehicle") || "") || null,
      complaint: String(formData.get("complaint") || "").trim(),
      labourFils: toFils(String(formData.get("labour") || 0)),
    },
  });
  redirect(`/s/${username}/workshop`);
}

export async function updateJobStatus(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "workshop")) throw new Error("forbidden");
  const id = String(formData.get("id") || "");
  const status = String(formData.get("status") || "") as JobStatus;
  await prisma.jobCard.update({ where: { id }, data: { status } });
  redirect(`/s/${username}/workshop`);
}

export async function saveSettings(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "settings")) throw new Error("forbidden");
  await prisma.shop.update({
    where: { id: s.shopId },
    data: {
      name: String(formData.get("name") || "").trim(),
      legalName: String(formData.get("legalName") || "") || null,
      trn: String(formData.get("trn") || "") || null,
      vatPercent: Number(formData.get("vatPercent") || 5),
    },
  });
  redirect(`/s/${username}/settings`);
}
