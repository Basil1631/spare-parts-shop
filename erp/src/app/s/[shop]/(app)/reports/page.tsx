import { prisma } from "@/lib/prisma";
import { aed } from "@/lib/money";
import { PageHeader, Panel } from "@/components/ui";
import { shopModule } from "@/lib/access";

export default async function ReportsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopModule(username, "reports");
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
      <PageHeader title="Reports" hint="Live totals from this shop only. Use this for a quick close, not a tax filing." />
      <div className="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <Kpi label="Invoices" value={String(sales._count)} />
        <Kpi label="Sales total" value={aed(sales._sum.totalFils || 0)} />
        <Kpi label="VAT collected" value={aed(sales._sum.vatFils || 0)} />
        <Kpi label="AR unpaid" value={aed(unpaid._sum.totalFils || 0)} />
      </div>
      <Panel className="overflow-hidden">
        <div className="px-5 py-4 border-b border-slate-100 font-semibold text-[#101622]">Low stock</div>
        <ul className="text-sm divide-y divide-slate-100">
          {lowRows.map((p) => (
            <li key={p.id} className="px-5 py-3 flex justify-between">
              <span>
                {p.name} <span className="text-slate-500">({p.sku})</span>
              </span>
              <span className="tabular-nums font-medium">{p.stocks.reduce((a, b) => a + b.qtyOnHand, 0)}</span>
            </li>
          ))}
          {lowRows.length === 0 ? <li className="px-5 py-8 text-slate-500">None below minimum.</li> : null}
        </ul>
      </Panel>
    </div>
  );
}

function Kpi({ label, value }: { label: string; value: string }) {
  return (
    <Panel className="p-5">
      <div className="text-sm text-slate-500">{label}</div>
      <div className="text-[26px] font-semibold mt-1 text-[#101622]">{value}</div>
    </Panel>
  );
}
