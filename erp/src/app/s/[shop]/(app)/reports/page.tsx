import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";

export default async function ReportsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const [sales, unpaid, low] = await Promise.all([
    prisma.invoice.aggregate({ where: { shopId: s.shopId }, _sum: { totalFils: true, vatFils: true }, _count: true }),
    prisma.invoice.aggregate({ where: { shopId: s.shopId, status: "unpaid" }, _sum: { totalFils: true } }),
    prisma.part.findMany({
      where: { shopId: s.shopId },
      include: { stocks: true },
    }),
  ]);
  const lowRows = low.filter((p) => p.stocks.reduce((a, b) => a + b.qtyOnHand, 0) <= p.minQty);
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Reports</h1>
      <div className="grid sm:grid-cols-3 gap-4">
        <div className="bg-white rounded-2xl p-5 border"><div className="text-sm text-slate-500">Invoices</div><div className="text-xl font-semibold">{sales._count}</div></div>
        <div className="bg-white rounded-2xl p-5 border"><div className="text-sm text-slate-500">Sales total</div><div className="text-xl font-semibold">{aed(sales._sum.totalFils || 0)}</div></div>
        <div className="bg-white rounded-2xl p-5 border"><div className="text-sm text-slate-500">VAT collected</div><div className="text-xl font-semibold">{aed(sales._sum.vatFils || 0)}</div></div>
        <div className="bg-white rounded-2xl p-5 border"><div className="text-sm text-slate-500">AR unpaid</div><div className="text-xl font-semibold">{aed(unpaid._sum.totalFils || 0)}</div></div>
      </div>
      <section>
        <h2 className="font-medium mb-2">Low stock</h2>
        <ul className="text-sm bg-white rounded-2xl border divide-y">
          {lowRows.map((p) => (
            <li key={p.id} className="px-4 py-2">{p.name} ({p.sku}) · {p.stocks.reduce((a, b) => a + b.qtyOnHand, 0)}</li>
          ))}
          {lowRows.length === 0 ? <li className="px-4 py-2 text-slate-500">None</li> : null}
        </ul>
      </section>
    </div>
  );
}
