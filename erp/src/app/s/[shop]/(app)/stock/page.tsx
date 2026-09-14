import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { adjustStock } from "@/app/actions";
import { can } from "@/lib/roles";

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
      <h1 className="text-2xl font-semibold mb-2">Stock</h1>
      <p className="text-sm text-slate-500 mb-4">Inbound stock is only via Incoming stock (godown confirm). Adjust out is for damage.</p>
      <div className="space-y-3">
        {parts.map((p) => {
          const qty = p.stocks.reduce((a, b) => a + b.qtyOnHand, 0);
          const low = qty <= p.minQty;
          return (
            <div key={p.id} className={`bg-white rounded-2xl border p-4 ${low ? "ring-2 ring-red-400" : ""}`}>
              <div className="flex justify-between gap-3 flex-wrap">
                <div>
                  <div className="font-medium">{p.name} {low ? <span className="text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">LOW</span> : null}</div>
                  <div className="text-sm text-slate-500">SKU {p.sku} · on hand {qty} · min {p.minQty}</div>
                </div>
                {warehouseCan ? (
                  <form action={adjust} className="flex gap-2 items-end">
                    <input type="hidden" name="partId" value={p.id} />
                    <input name="qty" type="number" min={1} placeholder="Qty out" className="border rounded-lg w-24 px-2 py-1.5" required />
                    <input name="note" placeholder="Damage reason" required className="border rounded-lg px-2 py-1.5" />
                    <button className="bg-slate-700 text-white rounded-lg px-3 py-1.5 text-sm">Adjust</button>
                  </form>
                ) : null}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
