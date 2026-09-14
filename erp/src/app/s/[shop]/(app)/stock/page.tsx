import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { adjustStock } from "@/app/actions";
import { can } from "@/lib/roles";
import { Badge, btnPrimary, field, PageHeader, Panel } from "@/components/ui";

export default async function StockPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const parts = await prisma.part.findMany({
    where: { shopId: s.shopId },
    include: { stocks: true },
    orderBy: { name: "asc" },
  });
  const adjust = adjustStock.bind(null, username);
  const warehouseCan = can(s.role, "godown");
  return (
    <div>
      <PageHeader title="Stock" hint="Inbound stock is only via Incoming stock. Adjust out is for damage or write-off." />
      <div className="space-y-3">
        {parts.map((p) => {
          const qty = p.stocks.reduce((a, b) => a + b.qtyOnHand, 0);
          const low = qty <= p.minQty;
          return (
            <Panel key={p.id} className={`p-4 ${low ? "ring-1 ring-amber-300" : ""}`}>
              <div className="flex justify-between gap-4 flex-wrap items-center">
                <div>
                  <div className="font-medium text-[#101622] flex items-center gap-2">
                    {p.name}
                    {low ? <Badge tone="warn">Low</Badge> : null}
                  </div>
                  <div className="text-sm text-slate-500 mt-0.5">
                    SKU {p.sku} · on hand <span className="tabular-nums font-medium text-[#101622]">{qty}</span> · min {p.minQty}
                  </div>
                </div>
                {warehouseCan ? (
                  <form action={adjust} className="flex gap-2 items-center flex-wrap">
                    <input type="hidden" name="partId" value={p.id} />
                    <input name="qty" type="number" min={1} placeholder="Qty out" className={`${field} w-24`} required />
                    <input name="note" placeholder="Damage reason" required className={`${field} w-48`} />
                    <button className={btnPrimary}>Adjust out</button>
                  </form>
                ) : null}
              </div>
            </Panel>
          );
        })}
        {parts.length === 0 ? <p className="text-slate-500 text-sm">No catalog items.</p> : null}
      </div>
    </div>
  );
}
