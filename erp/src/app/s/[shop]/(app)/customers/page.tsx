import { prisma } from "@/lib/prisma";
import { saveCustomer } from "@/app/actions";
import { Banner, DataTable, field, FieldLabel, PageHeader, Panel } from "@/components/ui";
import { SubmitButton } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function CustomersPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "customers");
  const customers = await prisma.customer.findMany({
    where: { shopId: s.shopId },
    include: { invoices: true },
    orderBy: { name: "asc" },
  });
  const save = saveCustomer.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Customers / garages" hint="Account customers can take parts on credit. Walk-in is optional at the POS." />
      {q.ok ? <Banner kind="ok">Customer saved.</Banner> : null}
      {q.error ? <Banner kind="error">Customer name is required.</Banner> : null}
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-4 gap-3">
          <div>
            <FieldLabel>Name</FieldLabel>
            <input name="name" required className={field} />
          </div>
          <div>
            <FieldLabel>Phone</FieldLabel>
            <input name="phone" className={field} />
          </div>
          <div>
            <FieldLabel>Type</FieldLabel>
            <select name="type" className={field}>
              <option value="account">Account / garage</option>
              <option value="walk_in">Walk-in</option>
              <option value="wholesale">Wholesale</option>
            </select>
          </div>
          <div className="flex items-end">
            <SubmitButton>Add customer</SubmitButton>
          </div>
        </form>
      </Panel>
      <DataTable headers={["Name", "Type", "Phone", "Invoices"]}>
        {customers.map((c) => (
          <tr key={c.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{c.name}</td>
            <td className="px-4 py-3 capitalize">{c.type.replaceAll("_", " ")}</td>
            <td className="px-4 py-3 text-slate-500">{c.phone || "—"}</td>
            <td className="px-4 py-3">{c.invoices.length}</td>
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
