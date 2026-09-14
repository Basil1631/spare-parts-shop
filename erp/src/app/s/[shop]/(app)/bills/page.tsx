import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { markVendorBill } from "@/app/actions";

export default async function BillsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const bills = await prisma.vendorBill.findMany({
    where: { shopId: s.shopId },
    include: { order: { include: { supplier: true } } },
    orderBy: { order: { receivedAt: "desc" } },
  });
  const mark = markVendorBill.bind(null, username);
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-2">Vendor bills</h1>
      <p className="text-sm text-slate-500 mb-4">Invoices appear after godown confirms receipt.</p>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">PO</th><th>Supplier</th><th>Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
          {bills.map((b) => (
            <tr key={b.id} className="border-t">
              <td className="px-3 py-2">{b.order.number}</td>
              <td>{b.order.supplier?.name || "—"}</td>
              <td>{aed(b.amountFils)}</td>
              <td>{b.status}</td>
              <td>
                <form action={mark} className="inline">
                  <input type="hidden" name="id" value={b.id} />
                  <input type="hidden" name="status" value={b.status === "paid" ? "unpaid" : "paid"} />
                  <button className="text-teal-700">{b.status === "paid" ? "Mark unpaid" : "Mark paid"}</button>
                </form>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
