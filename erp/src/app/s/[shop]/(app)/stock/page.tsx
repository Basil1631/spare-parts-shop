import { prisma } from "@/lib/prisma";
import { adjustStock } from "@/app/actions";
import { can } from "@/lib/roles";
import { Badge, Banner, field, PageHeader, Panel } from "@/components/ui";
import { ConfirmSubmit } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function StockPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "stock");
  const parts = await prisma.part.findMany({
    where: { shopId: s.shopId },
    include: { stocks: true },
    orderBy: { name: "asc" },
  });
  const adjust = adjustStock.bind(null, username);
  const canWriteOff = can(s.role, "godown") || s.role === "owner" || s.role === "branch_manager";
  return (
    <div>
      <PageHeader title="Stock" hint="Inbound stock is only via Incoming stock. Adjust out is for damage or write-off." />
      {q.ok ? <Banner kind="ok">Stock adjusted.</Banner> : null}
      {q.error ? <Banner kind="error">Could not write off that quantity.</Banner> : null}
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
                    {!p.active ? <Badge>Inactive</Badge> : null}
                  </div>
                  <div className="text-sm text-slate-500 mt-0.5">
                    SKU {p.sku} · on hand <span className="tabular-nums font-medium text-[#101622]">{qty}</span> · min {p.minQty}
                  </div>
                </div>
                {canWriteOff ? (
                  <form action={adjust} className="flex gap-2 items-end flex-wrap">
                    <input type="hidden" name="partId" value={p.id} />
                    <label className="text-xs text-slate-600">
                      Qty out
                      <input name="qty" type="number" min={1} className={`${field} w-24 mt-1`} required />
                    </label>
                    <label className="text-xs text-slate-600">
                      Reason
                      <input name="note" required className={`${field} w-48 mt-1`} />
                    </label>
                    <ConfirmSubmit message="Write this quantity off as damage? This cannot be undone from here.">Adjust out</ConfirmSubmit>
                  </form>
                ) : (
                  <p className="text-xs text-slate-500">Warehouse confirms inbound. Ask godown to write off damage.</p>
                )}
              </div>
            </Panel>
          );
        })}
        {parts.length === 0 ? <p className="text-slate-500 text-sm">No catalog items.</p> : null}
      </div>
    </div>
  );
}
