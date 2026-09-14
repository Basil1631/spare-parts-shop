import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { confirmReceipt } from "@/app/actions";

export default async function GodownPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  await shopContext(username);
  const orders = await prisma.purchaseOrder.findMany({
    where: { status: "awaiting_godown", shop: { username } },
    include: { lines: true },
    orderBy: { createdAt: "desc" },
  });
  const parts = await prisma.part.findMany({ where: { shop: { username } } });
  const byId = Object.fromEntries(parts.map((p) => [p.id, p]));
  const confirm = confirmReceipt.bind(null, username);
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-2">Incoming stock</h1>
      <p className="text-sm text-slate-500 mb-4">Name, SKU and ordered qty only. Enter actual qty received. Cost stays with accounts.</p>
      {orders.length === 0 ? <p className="text-slate-500">Nothing waiting at the godown.</p> : null}
      <div className="space-y-4">
        {orders.map((o) => {
          const line = o.lines[0];
          const part = line ? byId[line.partId] : null;
          return (
            <form key={o.id} action={confirm} className="bg-white rounded-2xl border p-4 flex flex-wrap gap-4 items-end">
              <input type="hidden" name="orderId" value={o.id} />
              <div>
                <div className="font-medium">{part?.name}</div>
                <div className="text-sm text-slate-500">SKU {part?.sku} · ordered {line?.orderedQty} · {o.number}</div>
              </div>
              <input name="receivedQty" type="number" min={0} defaultValue={line?.orderedQty} className="border rounded-xl px-3 py-2 w-28" />
              <button className="bg-slate-900 text-white rounded-xl px-4 py-2 text-sm">Confirm received</button>
            </form>
          );
        })}
      </div>
    </div>
  );
}
