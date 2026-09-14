import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveCustomer } from "@/app/actions";
import { btnPrimary, DataTable, field, PageHeader, Panel } from "@/components/ui";

export default async function CustomersPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const customers = await prisma.customer.findMany({
    where: { shopId: s.shopId },
    include: { invoices: true },
    orderBy: { name: "asc" },
  });
  const save = saveCustomer.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Customers / garages" hint="Account customers can take parts on credit. Walk-in is optional at the POS." />
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-4 gap-2">
          <input name="name" required placeholder="Name" className={field} />
          <input name="phone" placeholder="Phone" className={field} />
          <select name="type" className={field}>
            <option value="account">Account / garage</option>
            <option value="walk_in">Walk-in</option>
            <option value="wholesale">Wholesale</option>
          </select>
          <button className={btnPrimary}>Add customer</button>
        </form>
      </Panel>
      <DataTable headers={["Name", "Type", "Phone", "Invoices"]}>
        {customers.map((c) => (
          <tr key={c.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{c.name}</td>
            <td className="capitalize">{c.type.replaceAll("_", " ")}</td>
            <td className="text-slate-500">{c.phone || "—"}</td>
            <td>{c.invoices.length}</td>
          </tr>
        ))}
        {customers.length === 0 ? (
          <tr>
            <td colSpan={4} className="px-4 py-8 text-slate-500">
              No customers yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
