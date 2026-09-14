import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { confirmReceipt } from "@/app/actions";
import { btnPrimary, field, PageHeader, Panel } from "@/components/ui";

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
      <PageHeader title="Incoming stock" hint="Name, SKU and ordered qty only. Enter actual qty received. Cost stays with accounts." />
      {orders.length === 0 ? (
        <Panel className="p-8 text-slate-500 text-sm">Nothing waiting at the godown.</Panel>
      ) : null}
      <div className="space-y-3">
        {orders.map((o) => {
          const line = o.lines[0];
          const part = line ? byId[line.partId] : null;
          return (
            <form key={o.id} action={confirm}>
              <Panel className="p-4 flex flex-wrap gap-4 items-end justify-between">
                <input type="hidden" name="orderId" value={o.id} />
                <div>
                  <div className="font-medium text-[#101622]">{part?.name}</div>
                  <div className="text-sm text-slate-500">
                    SKU {part?.sku} · ordered {line?.orderedQty} · {o.number}
                  </div>
                </div>
                <div className="flex gap-2 items-center">
                  <input name="receivedQty" type="number" min={0} defaultValue={line?.orderedQty} className={`${field} w-28`} />
                  <button className={btnPrimary}>Confirm received</button>
                </div>
              </Panel>
            </form>
          );
        })}
      </div>
    </div>
  );
}
