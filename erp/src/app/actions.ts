"use server";

import { redirect } from "next/navigation";
import { isRedirectError } from "next/dist/client/components/redirect-error";
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
import { deny } from "@/lib/access";
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

const PAY_MODES = new Set(["cash", "card", "on_account"]);

export async function providerLogin(formData: FormData) {
  const email = String(formData.get("email") || "")
    .toLowerCase()
    .trim();
  const password = String(formData.get("password") || "");
  const user = await prisma.providerUser.findUnique({ where: { email } });
  if (!user || !(await verifyPassword(password, user.passwordHash))) {
    redirect("/provider/login?error=1");
  }
  const days = formData.get("remember") ? 14 : 1;
  await setSession({ kind: "provider", userId: user.id, email: user.email, name: user.name }, days);
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
  const ownerEmail = String(formData.get("ownerEmail") || "")
    .toLowerCase()
    .trim();
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
  if (!shop) redirect("/provider");
  await prisma.shop.update({
    where: { id: shop.id },
    data: { status: shop.status === "suspended" ? "active" : "suspended" },
  });
  redirect("/provider");
}

export async function shopLogin(username: string, formData: FormData) {
  const email = String(formData.get("email") || "")
    .toLowerCase()
    .trim();
  const password = String(formData.get("password") || "");
  const shop = await prisma.shop.findUnique({ where: { username } });
  if (!shop || shop.status === "suspended") redirect(`/s/${username}/login?error=shop`);
  const user = await prisma.user.findUnique({ where: { shopId_email: { shopId: shop.id, email } } });
  if (!user || !user.active || !(await verifyPassword(password, user.passwordHash))) {
    redirect(`/s/${username}/login?error=1`);
  }
  const days = formData.get("remember") ? 14 : 1;
  await setSession(
    {
      kind: "shop",
      userId: user.id,
      shopId: shop.id,
      shopUsername: shop.username,
      shopName: shop.name,
      email: user.email,
      name: user.name,
      role: user.role,
    },
    days,
  );
  await markAttendance(user.id, shop.id);
  redirect(`/s/${username}`);
}

export async function shopLogout(username: string) {
  await clearSession();
  redirect(`/s/${username}/login`);
}

export async function savePart(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "parts")) deny(username);
  const id = String(formData.get("id") || "");
  const data = {
    sku: String(formData.get("sku") || "").trim(),
    name: String(formData.get("name") || "").trim(),
    oemNumber: String(formData.get("oemNumber") || "") || null,
    brand: String(formData.get("brand") || "") || null,
    minQty: Math.max(0, Number(formData.get("minQty") || 0) || 0),
    salePriceFils: toFils(String(formData.get("price") || 0)),
    active: String(formData.get("active") || "true") !== "false",
  };
  if (!data.sku || !data.name) redirect(`/s/${username}/parts?error=1`);
  if (id) {
    await prisma.part.updateMany({ where: { id, shopId: s.shopId }, data });
  } else {
    await prisma.part.create({ data: { ...data, shopId: s.shopId } });
  }
  redirect(`/s/${username}/parts?ok=1`);
}

export async function raisePurchase(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "purchases")) deny(username);
  const partId = String(formData.get("partId") || "");
  const qty = Number(formData.get("qty") || 0);
  const cost = toFils(String(formData.get("unitCost") || 0));
  if (!partId || qty < 1) redirect(`/s/${username}/purchases?error=1`);
  const part = await prisma.part.findFirst({ where: { id: partId, shopId: s.shopId } });
  if (!part) redirect(`/s/${username}/purchases?error=1`);
  const supplierName = String(formData.get("supplier") || "").trim();
  await prisma.$transaction(async (tx) => {
    let supplierId: string | undefined;
    if (supplierName) {
      const sup =
        (await tx.supplier.findFirst({ where: { shopId: s.shopId, name: supplierName } })) ??
        (await tx.supplier.create({ data: { shopId: s.shopId, name: supplierName } }));
      supplierId = sup.id;
    }
    const number = await nextNumber(s.shopId, "po", "PO", tx);
    await tx.purchaseOrder.create({
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
  });
  redirect(`/s/${username}/purchases?ok=1`);
}

export async function confirmReceipt(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "godown")) deny(username);
  const orderId = String(formData.get("orderId") || "");
  const receivedQty = Number(formData.get("receivedQty") || 0);
  if (!Number.isFinite(receivedQty) || receivedQty < 0) redirect(`/s/${username}/godown?error=qty`);
  try {
    await prisma.$transaction(async (tx) => {
      const order = await tx.purchaseOrder.findFirst({
        where: { id: orderId, shopId: s.shopId, status: "awaiting_godown" },
        include: { lines: true },
      });
      if (!order || !order.lines[0]) throw new Error("missing");
      const line = order.lines[0];
      const warehouse = await tx.warehouse.findFirst({ where: { shopId: s.shopId } });
      if (!warehouse) throw new Error("No warehouse");
      const claimed = await tx.purchaseOrder.updateMany({
        where: { id: order.id, shopId: s.shopId, status: "awaiting_godown" },
        data: { status: "received", receivedById: s.userId, receivedAt: new Date() },
      });
      if (claimed.count !== 1) throw new Error("missing");
      await tx.purchaseLine.update({ where: { id: line.id }, data: { receivedQty } });
      if (receivedQty > 0) {
        await moveStock(
          {
            shopId: s.shopId,
            partId: line.partId,
            warehouseId: warehouse.id,
            qty: receivedQty,
            type: "receipt",
            userId: s.userId,
            note: `GRN ${order.number}`,
          },
          tx,
        );
      }
      await tx.vendorBill.create({
        data: { shopId: s.shopId, orderId: order.id, amountFils: receivedQty * line.unitCostFils, status: "unpaid" },
      });
    });
  } catch (e) {
    if (isRedirectError(e)) throw e;
    redirect(`/s/${username}/godown?error=1`);
  }
  redirect(`/s/${username}/godown?ok=1`);
}

export async function markVendorBill(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "bills")) deny(username);
  const id = String(formData.get("id") || "");
  const status = String(formData.get("status") || "") as "paid" | "unpaid";
  if (status !== "paid" && status !== "unpaid") deny(username);
  await prisma.vendorBill.updateMany({
    where: { id, shopId: s.shopId },
    data: { status, paidAt: status === "paid" ? new Date() : null },
  });
  redirect(`/s/${username}/bills?ok=1`);
}

export async function createPosSale(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "pos")) deny(username);
  let lines: { partId: string; qty: number; unitPriceFils: number }[] = [];
  const raw = String(formData.get("lines") || "");
  if (raw) {
    try {
      const parsed = JSON.parse(raw) as { partId: string; qty: number; unitPrice: string | number }[];
      lines = parsed.map((l) => ({
        partId: l.partId,
        qty: Number(l.qty),
        unitPriceFils: toFils(l.unitPrice),
      }));
    } catch {
      lines = [];
    }
  } else {
    lines = [
      {
        partId: String(formData.get("partId") || ""),
        qty: Number(formData.get("qty") || 0),
        unitPriceFils: toFils(String(formData.get("unitPrice") || 0)),
      },
    ];
  }
  lines = lines.filter((l) => l.partId && l.qty >= 1);
  const paymentMode = String(formData.get("paymentMode") || "cash");
  const customerName = String(formData.get("customerName") || "").trim();
  if (!lines.length) redirect(`/s/${username}/pos?error=1`);
  if (!PAY_MODES.has(paymentMode)) redirect(`/s/${username}/pos?error=1`);
  if (paymentMode === "on_account" && !customerName) redirect(`/s/${username}/pos?error=account`);
  try {
    const number = await prisma.$transaction(async (tx) => {
      const shop = await tx.shop.findUniqueOrThrow({ where: { id: s.shopId } });
      const warehouse = await tx.warehouse.findFirst({ where: { shopId: s.shopId } });
      if (!warehouse) throw new Error("No warehouse");
      for (const line of lines) {
        const part = await tx.part.findFirst({ where: { id: line.partId, shopId: s.shopId, active: true } });
        if (!part) throw new Error("Unknown part");
        await moveStock(
          {
            shopId: s.shopId,
            partId: line.partId,
            warehouseId: warehouse.id,
            qty: line.qty,
            type: "sale",
            userId: s.userId,
            note: "POS sale",
          },
          tx,
        );
      }
      let customerId: string | undefined;
      if (customerName) {
        const c =
          (await tx.customer.findFirst({ where: { shopId: s.shopId, name: customerName } })) ??
          (await tx.customer.create({ data: { shopId: s.shopId, name: customerName, type: "account" } }));
        customerId = c.id;
      }
      const subtotal = lines.reduce((a, l) => a + l.unitPriceFils * l.qty, 0);
      const vat = Math.round(subtotal * (shop.vatPercent / 100));
      const invNumber = await nextNumber(s.shopId, "inv", "INV", tx);
      await tx.invoice.create({
        data: {
          shopId: s.shopId,
          customerId,
          number: invNumber,
          status: paymentMode === "cash" || paymentMode === "card" ? "paid" : "unpaid",
          paymentMode,
          subtotalFils: subtotal,
          vatFils: vat,
          totalFils: subtotal + vat,
          salespersonId: s.userId,
          lines: {
            create: lines.map((l) => ({
              partId: l.partId,
              qty: l.qty,
              unitPriceFils: l.unitPriceFils,
              lineTotalFils: l.unitPriceFils * l.qty,
            })),
          },
        },
      });
      return invNumber;
    });
    redirect(`/s/${username}/invoices?ok=${encodeURIComponent(number)}`);
  } catch (e) {
    if (isRedirectError(e)) throw e;
    const msg = e instanceof Error ? e.message : "";
    if (msg.includes("Not enough stock")) redirect(`/s/${username}/pos?error=stock`);
    redirect(`/s/${username}/pos?error=1`);
  }
}

export async function saveCustomer(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "customers")) deny(username);
  const name = String(formData.get("name") || "").trim();
  if (!name) redirect(`/s/${username}/customers?error=1`);
  await prisma.customer.create({
    data: {
      shopId: s.shopId,
      name,
      phone: String(formData.get("phone") || "") || null,
      trn: String(formData.get("trn") || "") || null,
      type: (String(formData.get("type") || "account") as "walk_in" | "account" | "wholesale") || "account",
    },
  });
  redirect(`/s/${username}/customers?ok=1`);
}

export async function saveStaff(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "staff")) deny(username);
  const email = String(formData.get("email") || "")
    .toLowerCase()
    .trim();
  const password = String(formData.get("password") || "");
  const role = String(formData.get("role") || "cashier") as Role;
  const name = String(formData.get("name") || "").trim();
  if (!name || !email) redirect(`/s/${username}/staff?error=1`);
  if (password.length < 6) redirect(`/s/${username}/staff?error=password`);
  if (role === "owner" && s.role !== "owner") deny(username);
  try {
    await prisma.user.create({
      data: {
        shopId: s.shopId,
        name,
        email,
        passwordHash: await hashPassword(password),
        role,
        monthlySalaryFils: toFils(String(formData.get("salary") || 0)),
        incentivePercent: Number(formData.get("incentive") || 0) || 0,
      },
    });
  } catch {
    redirect(`/s/${username}/staff?error=taken`);
  }
  redirect(`/s/${username}/staff?ok=1`);
}

export async function adjustStock(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "stock") && !can(s.role, "godown")) deny(username);
  const partId = String(formData.get("partId") || "");
  const qty = Number(formData.get("qty") || 0);
  if (!Number.isFinite(qty) || qty < 1) redirect(`/s/${username}/stock?error=1`);
  const warehouse = await prisma.warehouse.findFirst({ where: { shopId: s.shopId } });
  if (!warehouse) redirect(`/s/${username}/stock?error=1`);
  try {
    await moveStock({
      shopId: s.shopId,
      partId,
      warehouseId: warehouse.id,
      qty,
      type: "adjustment",
      userId: s.userId,
      note: String(formData.get("note") || "Damage"),
    });
  } catch {
    redirect(`/s/${username}/stock?error=stock`);
  }
  redirect(`/s/${username}/stock?ok=1`);
}

export async function saveJob(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "workshop")) deny(username);
  const complaint = String(formData.get("complaint") || "").trim();
  if (!complaint) redirect(`/s/${username}/workshop?error=1`);
  await prisma.jobCard.create({
    data: {
      shopId: s.shopId,
      vehicle: String(formData.get("vehicle") || "") || null,
      complaint,
      labourFils: toFils(String(formData.get("labour") || 0)),
    },
  });
  redirect(`/s/${username}/workshop?ok=1`);
}

export async function updateJobStatus(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "workshop")) deny(username);
  const id = String(formData.get("id") || "");
  const status = String(formData.get("status") || "") as JobStatus;
  const allowed: JobStatus[] = ["open", "in_progress", "parts_pending", "completed", "closed"];
  if (!allowed.includes(status)) redirect(`/s/${username}/workshop?error=1`);
  await prisma.jobCard.updateMany({ where: { id, shopId: s.shopId }, data: { status } });
  redirect(`/s/${username}/workshop?ok=1`);
}

export async function saveSettings(username: string, formData: FormData) {
  const s = await requireShop(username);
  if (!can(s.role, "settings")) deny(username);
  const vat = Number(formData.get("vatPercent") || 5);
  await prisma.shop.update({
    where: { id: s.shopId },
    data: {
      name: String(formData.get("name") || "").trim(),
      legalName: String(formData.get("legalName") || "") || null,
      trn: String(formData.get("trn") || "") || null,
      vatPercent: Number.isFinite(vat) && vat >= 0 && vat <= 100 ? vat : 5,
    },
  });
  redirect(`/s/${username}/settings?ok=1`);
}
