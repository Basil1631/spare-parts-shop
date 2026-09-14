import { MovementType } from "@prisma/client";
import { prisma } from "./prisma";

export async function moveStock(opts: {
  partId: string;
  warehouseId: string;
  qty: number;
  type: MovementType;
  userId?: string;
  note?: string;
}) {
  if (opts.qty === 0) return;
  const signed =
    opts.type === "sale" || opts.type === "workshop_issue" || opts.type === "adjustment"
      ? -Math.abs(opts.qty)
      : Math.abs(opts.qty);

  await prisma.$transaction(async (tx) => {
    const item = await tx.stockItem.upsert({
      where: { partId_warehouseId: { partId: opts.partId, warehouseId: opts.warehouseId } },
      update: {},
      create: { partId: opts.partId, warehouseId: opts.warehouseId, qtyOnHand: 0 },
    });
    const next = item.qtyOnHand + signed;
    if (next < 0) throw new Error("Not enough stock for this part.");
    await tx.stockItem.update({ where: { id: item.id }, data: { qtyOnHand: next } });
    await tx.stockMovement.create({
      data: {
        partId: opts.partId,
        type: opts.type,
        qty: Math.abs(opts.qty),
        userId: opts.userId,
        note: opts.note,
      },
    });
  });
}
