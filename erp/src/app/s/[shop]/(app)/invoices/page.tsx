import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { Badge, DataTable, PageHeader, statusTone } from "@/components/ui";

export default async function InvoicesPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const invoices = await prisma.invoice.findMany({
    where: { shopId: s.shopId },
    include: { customer: true, lines: true },
    orderBy: { createdAt: "desc" },
    take: 50,
  });
  return (
    <div>
      <PageHeader title="Invoices" hint="Latest 50 sales. Open POS to raise a new bill." />
      <DataTable headers={["Number", "Customer", "Total", "VAT", "Pay", "Status"]}>
        {invoices.map((inv) => (
          <tr key={inv.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{inv.number}</td>
            <td>{inv.customer?.name || "Walk-in"}</td>
            <td className="tabular-nums">{aed(inv.totalFils)}</td>
            <td className="tabular-nums text-slate-500">{aed(inv.vatFils)}</td>
            <td className="capitalize">{inv.paymentMode.replaceAll("_", " ")}</td>
            <td>
              <Badge tone={statusTone(inv.status)}>{inv.status}</Badge>
            </td>
          </tr>
        ))}
        {invoices.length === 0 ? (
          <tr>
            <td colSpan={6} className="px-4 py-8 text-slate-500">
              No invoices yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
