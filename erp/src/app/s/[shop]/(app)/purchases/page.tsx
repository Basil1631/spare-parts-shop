import { prisma } from "@/lib/prisma";
import { raisePurchase } from "@/app/actions";
import { aed } from "@/lib/money";
import { Badge, Banner, DataTable, field, FieldLabel, PageHeader, Panel, statusTone } from "@/components/ui";
import { SubmitButton } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function PurchasesPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "purchases");
  const [orders, parts] = await Promise.all([
    prisma.purchaseOrder.findMany({
      where: { shopId: s.shopId },
      include: { supplier: true, lines: true, vendorBill: true },
      orderBy: { createdAt: "desc" },
    }),
    prisma.part.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } }),
  ]);
  const raise = raisePurchase.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Purchases" hint="Raising an order does not increase stock. Warehouse confirms actual qty first. Supplier name is optional." />
      {q.ok ? <Banner kind="ok">Purchase order raised.</Banner> : null}
      {q.error ? <Banner kind="error">Choose a part and a quantity of 1 or more.</Banner> : null}
      <Panel className="p-4">
        <form action={raise} className="grid md:grid-cols-5 gap-3">
          <div>
            <FieldLabel>Supplier</FieldLabel>
            <input name="supplier" className={field} />
          </div>
          <div>
            <FieldLabel>Part</FieldLabel>
            <select name="partId" required className={field}>
              <option value="">Select</option>
              {parts.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.name} ({p.sku})
                </option>
              ))}
            </select>
          </div>
          <div>
            <FieldLabel>Qty</FieldLabel>
            <input name="qty" type="number" min={1} defaultValue={1} className={field} />
          </div>
          <div>
            <FieldLabel>Unit cost AED</FieldLabel>
            <input name="unitCost" step="0.01" required className={field} />
          </div>
          <div className="flex items-end">
            <SubmitButton>Raise order</SubmitButton>
          </div>
        </form>
      </Panel>
      <DataTable headers={["PO", "Supplier", "Status", "Cost"]}>
        {orders.map((o) => (
          <tr key={o.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{o.number}</td>
            <td className="px-4 py-3">{o.supplier?.name || "—"}</td>
            <td className="px-4 py-3">
              <Badge tone={statusTone(o.status)}>{o.status.replaceAll("_", " ")}</Badge>
            </td>
            <td className="px-4 py-3 tabular-nums">{aed(o.lines.reduce((a, l) => a + l.unitCostFils * (l.receivedQty || l.orderedQty), 0))}</td>
          </tr>
        ))}
        {orders.length === 0 ? (
          <tr>
            <td colSpan={4} className="px-4 py-8 text-slate-500">
              No purchase orders yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
