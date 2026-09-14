import { prisma } from "@/lib/prisma";
import { aed } from "@/lib/money";
import { markVendorBill } from "@/app/actions";
import { Badge, Banner, DataTable, PageHeader, statusTone } from "@/components/ui";
import { ConfirmSubmit } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function BillsPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "bills");
  const bills = await prisma.vendorBill.findMany({
    where: { shopId: s.shopId },
    include: { order: { include: { supplier: true } } },
    orderBy: { order: { receivedAt: "desc" } },
  });
  const mark = markVendorBill.bind(null, username);
  return (
    <div>
      <PageHeader title="Vendor bills" hint="Supplier bills appear after godown confirms a purchase receipt." />
      {q.ok ? <Banner kind="ok">Bill status updated.</Banner> : null}
      <DataTable headers={["PO", "Supplier", "Amount", "Status", ""]}>
        {bills.map((b) => (
          <tr key={b.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{b.order.number}</td>
            <td className="px-4 py-3">{b.order.supplier?.name || "—"}</td>
            <td className="px-4 py-3 tabular-nums">{aed(b.amountFils)}</td>
            <td className="px-4 py-3">
              <Badge tone={statusTone(b.status)}>{b.status}</Badge>
            </td>
            <td className="px-4 py-3 text-right">
              <form action={mark} className="inline">
                <input type="hidden" name="id" value={b.id} />
                <input type="hidden" name="status" value={b.status === "paid" ? "unpaid" : "paid"} />
                <ConfirmSubmit
                  message={b.status === "paid" ? "Mark this supplier bill unpaid?" : "Mark this supplier bill paid?"}
                  className="text-sm font-medium text-teal-700 h-auto px-0 bg-transparent hover:bg-transparent"
                >
                  {b.status === "paid" ? "Mark unpaid" : "Mark paid"}
                </ConfirmSubmit>
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
