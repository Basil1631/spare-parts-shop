import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { raisePurchase } from "@/app/actions";
import { aed } from "@/lib/money";

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
      <h1 className="text-2xl font-semibold">Purchases</h1>
      <p className="text-sm text-slate-500">Raising an order does not increase stock. Warehouse confirms actual qty first.</p>
      <form action={raise} className="bg-white rounded-2xl border p-4 grid md:grid-cols-5 gap-2">
        <input name="supplier" placeholder="Supplier" className="border rounded-xl px-3 py-2" />
        <select name="partId" required className="border rounded-xl px-3 py-2">
          <option value="">Part</option>
          {parts.map((p) => (
            <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>
          ))}
        </select>
        <input name="qty" type="number" min={1} defaultValue={1} className="border rounded-xl px-3 py-2" />
        <input name="unitCost" step="0.01" required placeholder="Unit cost AED" className="border rounded-xl px-3 py-2" />
        <button className="bg-slate-900 text-white rounded-xl">Raise order</button>
      </form>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">PO</th><th>Supplier</th><th>Status</th><th>Cost</th></tr></thead>
        <tbody>
          {orders.map((o) => (
            <tr key={o.id} className="border-t">
              <td className="px-3 py-2">{o.number}</td>
              <td>{o.supplier?.name || "—"}</td>
              <td>{o.status.replaceAll("_", " ")}</td>
              <td>{aed(o.lines.reduce((a, l) => a + l.unitCostFils * (l.receivedQty || l.orderedQty), 0))}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
