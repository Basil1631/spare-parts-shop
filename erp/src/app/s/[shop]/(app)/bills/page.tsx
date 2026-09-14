import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { markVendorBill } from "@/app/actions";
import { Badge, DataTable, PageHeader, statusTone } from "@/components/ui";

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
      <PageHeader title="Vendor bills" hint="Invoices appear after godown confirms receipt." />
      <DataTable headers={["PO", "Supplier", "Amount", "Status", ""]}>
        {bills.map((b) => (
          <tr key={b.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{b.order.number}</td>
            <td>{b.order.supplier?.name || "—"}</td>
            <td className="tabular-nums">{aed(b.amountFils)}</td>
            <td>
              <Badge tone={statusTone(b.status)}>{b.status}</Badge>
            </td>
            <td className="pr-4 text-right">
              <form action={mark} className="inline">
                <input type="hidden" name="id" value={b.id} />
                <input type="hidden" name="status" value={b.status === "paid" ? "unpaid" : "paid"} />
                <button className="text-sm font-medium text-teal-700 hover:text-teal-900">
                  {b.status === "paid" ? "Mark unpaid" : "Mark paid"}
                </button>
              </form>
            </td>
          </tr>
        ))}
        {bills.length === 0 ? (
          <tr>
            <td colSpan={5} className="px-4 py-8 text-slate-500">
              No vendor bills yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
