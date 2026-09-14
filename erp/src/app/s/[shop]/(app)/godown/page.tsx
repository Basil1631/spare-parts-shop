import { prisma } from "@/lib/prisma";
import { confirmReceipt } from "@/app/actions";
import { Banner, field, PageHeader, Panel } from "@/components/ui";
import { ConfirmSubmit } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function GodownPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  await shopModule(username, "godown");
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
      <PageHeader title="Incoming stock" hint="Enter actual qty received. Cost stays with accounts. Confirming creates a vendor bill." />
      {q.ok ? <Banner kind="ok">Receipt recorded.</Banner> : null}
      {q.error ? <Banner kind="error">Could not confirm that receipt. It may already be done.</Banner> : null}
      {orders.length === 0 ? <Panel className="p-8 text-slate-500 text-sm">Nothing waiting at the godown.</Panel> : null}
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
                <div className="flex gap-2 items-end">
                  <label className="text-xs text-slate-600">
                    Qty received
                    <input name="receivedQty" type="number" min={0} defaultValue={line?.orderedQty} className={`${field} w-28 mt-1`} />
                  </label>
                  <ConfirmSubmit message="Confirm this receipt? Stock will increase by the qty received.">Confirm received</ConfirmSubmit>
                </div>
              </Panel>
            </form>
          );
        })}
      </div>
    </div>
  );
}
