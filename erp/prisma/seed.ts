import { PrismaClient, Role } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  const passwordHash = await bcrypt.hash("password", 10);

  await prisma.providerUser.upsert({
    where: { email: "superadmin@inktek.local" },
    update: { name: "Inktek Super Admin" },
    create: {
      name: "Inktek Super Admin",
      email: "superadmin@inktek.local",
      passwordHash,
    },
  });

  const shop = await prisma.shop.upsert({
    where: { username: "alain" },
    update: { name: "Al Ain Spare Parts" },
    create: {
      name: "Al Ain Spare Parts",
      username: "alain",
      legalName: "Al Ain Spare Parts LLC",
      trn: "100000000000003",
      status: "active",
      vatPercent: 5,
    },
  });

  let branch = await prisma.branch.findFirst({ where: { shopId: shop.id } });
  if (!branch) {
    branch = await prisma.branch.create({
      data: { shopId: shop.id, name: "Main branch", isDefault: true },
    });
  }

  let warehouse = await prisma.warehouse.findFirst({ where: { shopId: shop.id } });
  if (!warehouse) {
    warehouse = await prisma.warehouse.create({
      data: { shopId: shop.id, branchId: branch.id, name: "Main godown" },
    });
  }

  const staff: { email: string; name: string; role: Role }[] = [
    { email: "owner@alain.local", name: "Shop Owner", role: "owner" },
    { email: "manager@alain.local", name: "Branch Manager", role: "branch_manager" },
    { email: "purchase@alain.local", name: "Purchase Officer", role: "purchase" },
    { email: "cashier@alain.local", name: "Counter Cashier", role: "cashier" },
    { email: "godown@alain.local", name: "Warehouse Supervisor", role: "warehouse" },
    { email: "accounts@alain.local", name: "Accountant", role: "accountant" },
    { email: "hr@alain.local", name: "HR Officer", role: "hr" },
    { email: "tech@alain.local", name: "Technician", role: "technician" },
  ];

  for (const u of staff) {
    await prisma.user.upsert({
      where: { shopId_email: { shopId: shop.id, email: u.email } },
      update: { name: u.name, role: u.role },
      create: {
        shopId: shop.id,
        email: u.email,
        name: u.name,
        role: u.role,
        passwordHash,
        monthlySalaryFils: 400000,
        incentivePercent: u.role === "cashier" ? 5 : 0,
      },
    });
  }

  const filter = await prisma.part.upsert({
    where: { shopId_sku: { shopId: shop.id, sku: "OF-100" } },
    update: {},
    create: {
      shopId: shop.id,
      sku: "OF-100",
      oemNumber: "15400-PLM-A01",
      brand: "Bosch",
      name: "Oil Filter",
      minQty: 5,
      salePriceFils: 2500,
    },
  });
  const pad = await prisma.part.upsert({
    where: { shopId_sku: { shopId: shop.id, sku: "BP-900" } },
    update: {},
    create: {
      shopId: shop.id,
      sku: "BP-900",
      oemNumber: "45022-S6D-E50",
      brand: "Brembo",
      name: "Brake Pad Set",
      minQty: 4,
      salePriceFils: 12000,
    },
  });

  for (const part of [filter, pad]) {
    await prisma.stockItem.upsert({
      where: { partId_warehouseId: { partId: part.id, warehouseId: warehouse.id } },
      update: {},
      create: { partId: part.id, warehouseId: warehouse.id, qtyOnHand: 12 },
    });
  }

  if (!(await prisma.supplier.findFirst({ where: { shopId: shop.id } }))) {
    await prisma.supplier.create({ data: { shopId: shop.id, name: "Gulf Auto Supplies", phone: "0500000000" } });
  }
  if (!(await prisma.customer.findFirst({ where: { shopId: shop.id } }))) {
    await prisma.customer.create({ data: { shopId: shop.id, name: "Al Ain Garage", type: "account", phone: "037800000" } });
  }

  console.log("Seed ready.");
}

main()
  .then(() => prisma.$disconnect())
  .catch((e) => {
    console.error(e);
    prisma.$disconnect();
    process.exit(1);
  });
