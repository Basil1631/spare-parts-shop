import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { card } from "@/lib/ui";

export default async function ShopDashboard({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const [parts, low, awaiting, unpaidBills, todaySales, staffPresent] = await Promise.all([
    prisma.part.count({ where: { shopId: s.shopId } }),
    prisma.part.count({ where: { shopId: s.shopId, stocks: { some: { qtyOnHand: { lte: 5 } } } } }),
    prisma.purchaseOrder.count({ where: { shopId: s.shopId, status: "awaiting_godown" } }),
    prisma.vendorBill.count({ where: { shopId: s.shopId, status: "unpaid" } }),
    prisma.invoice.aggregate({
      where: { shopId: s.shopId, createdAt: { gte: new Date(new Date().toDateString()) } },
      _sum: { totalFils: true },
      _count: true,
    }),
    prisma.attendance.count({
      where: { shopId: s.shopId, workedOn: new Date(new Date().toISOString().slice(0, 10)) },
    }),
  ]);

  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">Dashboard</h1>
      <p className="text-sm text-slate-500 mb-6">
        Logged in as {s.name}. Stock rises only after warehouse confirms incoming goods.
      </p>
      <div className="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <div className={card}><div className="text-sm text-slate-500">Today invoices</div><div className="text-2xl font-semibold">{todaySales._count}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Today sales</div><div className="text-2xl font-semibold">{aed(todaySales._sum.totalFils || 0)}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Catalog SKUs</div><div className="text-2xl font-semibold">{parts}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Low stock</div><div className="text-2xl font-semibold">{low}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Awaiting godown</div><div className="text-2xl font-semibold">{awaiting}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Unpaid vendor bills</div><div className="text-2xl font-semibold">{unpaidBills}</div></div>
        <div className={card}><div className="text-sm text-slate-500">Present today</div><div className="text-2xl font-semibold">{staffPresent}</div></div>
      </div>
    </div>
  );
}
