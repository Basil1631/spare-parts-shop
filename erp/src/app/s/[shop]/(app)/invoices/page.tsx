import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";

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
      <h1 className="text-2xl font-semibold mb-4">Invoices</h1>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">Number</th><th>Customer</th><th>Total</th><th>VAT</th><th>Pay</th><th>Status</th></tr></thead>
        <tbody>
          {invoices.map((inv) => (
            <tr key={inv.id} className="border-t">
              <td className="px-3 py-2">{inv.number}</td>
              <td>{inv.customer?.name || "Walk-in"}</td>
              <td>{aed(inv.totalFils)}</td>
              <td>{aed(inv.vatFils)}</td>
              <td>{inv.paymentMode}</td>
              <td>{inv.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
