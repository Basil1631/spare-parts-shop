import { prisma } from "@/lib/prisma";
import { aed } from "@/lib/money";
import { Badge, Banner, DataTable, PageHeader, statusTone } from "@/components/ui";
import { shopModule } from "@/lib/access";

export default async function InvoicesPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "invoices");
  const invoices = await prisma.invoice.findMany({
    where: { shopId: s.shopId },
    include: { customer: true, lines: true },
    orderBy: { createdAt: "desc" },
    take: 50,
  });
  return (
    <div>
      <PageHeader title="Invoices" hint="Latest 50 sales. Open POS to raise a new bill." />
      {q.ok ? <Banner kind="ok">Invoice {q.ok} issued.</Banner> : null}
      <DataTable headers={["Number", "Customer", "Total", "VAT", "Pay", "Status"]}>
        {invoices.map((inv) => (
          <tr key={inv.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{inv.number}</td>
            <td className="px-4 py-3">{inv.customer?.name || "Walk-in"}</td>
            <td className="px-4 py-3 tabular-nums">{aed(inv.totalFils)}</td>
            <td className="px-4 py-3 tabular-nums text-slate-500">{aed(inv.vatFils)}</td>
            <td className="px-4 py-3 capitalize">{inv.paymentMode.replaceAll("_", " ")}</td>
            <td className="px-4 py-3">
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
