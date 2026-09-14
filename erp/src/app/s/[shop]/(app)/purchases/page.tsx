import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { raisePurchase } from "@/app/actions";
import { aed } from "@/lib/money";
import { Badge, btnPrimary, DataTable, field, PageHeader, Panel, statusTone } from "@/components/ui";

export default async function PurchasesPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
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
      <PageHeader title="Purchases" hint="Raising an order does not increase stock. Warehouse confirms actual qty first." />
      <Panel className="p-4">
        <form action={raise} className="grid md:grid-cols-5 gap-2">
          <input name="supplier" placeholder="Supplier" className={field} />
          <select name="partId" required className={field}>
            <option value="">Part</option>
            {parts.map((p) => (
              <option key={p.id} value={p.id}>
                {p.name} ({p.sku})
              </option>
            ))}
          </select>
          <input name="qty" type="number" min={1} defaultValue={1} className={field} />
          <input name="unitCost" step="0.01" required placeholder="Unit cost AED" className={field} />
          <button className={btnPrimary}>Raise order</button>
        </form>
      </Panel>
      <DataTable headers={["PO", "Supplier", "Status", "Cost"]}>
        {orders.map((o) => (
          <tr key={o.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{o.number}</td>
            <td>{o.supplier?.name || "—"}</td>
            <td>
              <Badge tone={statusTone(o.status)}>{o.status.replaceAll("_", " ")}</Badge>
            </td>
            <td className="tabular-nums">{aed(o.lines.reduce((a, l) => a + l.unitCostFils * (l.receivedQty || l.orderedQty), 0))}</td>
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
