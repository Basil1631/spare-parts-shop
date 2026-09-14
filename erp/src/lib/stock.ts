import { MovementType, Prisma } from "@prisma/client";
import { prisma } from "./prisma";

type Db = Prisma.TransactionClient | typeof prisma;

export async function moveStock(
  opts: {
    shopId: string;
    partId: string;
    warehouseId: string;
    qty: number;
    type: MovementType;
    userId?: string;
    note?: string;
  },
  db: Db = prisma,
) {
  if (!Number.isFinite(opts.qty) || opts.qty === 0) return;
  const signed =
    opts.type === "sale" || opts.type === "workshop_issue" || opts.type === "adjustment"
      ? -Math.abs(opts.qty)
      : Math.abs(opts.qty);

  const part = await db.part.findFirst({ where: { id: opts.partId, shopId: opts.shopId } });
  if (!part) throw new Error("Unknown part");
  const warehouse = await db.warehouse.findFirst({ where: { id: opts.warehouseId, shopId: opts.shopId } });
  if (!warehouse) throw new Error("No warehouse");

  await db.stockItem.upsert({
    where: { partId_warehouseId: { partId: opts.partId, warehouseId: opts.warehouseId } },
    update: {},
    create: { partId: opts.partId, warehouseId: opts.warehouseId, qtyOnHand: 0 },
  });

  const updated = await db.stockItem.updateMany({
    where: {
      partId: opts.partId,
      warehouseId: opts.warehouseId,
      qtyOnHand: signed < 0 ? { gte: Math.abs(signed) } : { gte: 0 },
    },
    data: { qtyOnHand: { increment: signed } },
  });
  if (updated.count !== 1) throw new Error("Not enough stock for this part.");

  await db.stockMovement.create({
    data: {
      partId: opts.partId,
      warehouseId: opts.warehouseId,
      type: opts.type,
      qty: Math.abs(opts.qty),
      userId: opts.userId,
      note: opts.note,
    },
  });
}
