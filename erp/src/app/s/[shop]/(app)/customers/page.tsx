import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveCustomer } from "@/app/actions";

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
      <h1 className="text-2xl font-semibold">Customers / garages</h1>
      <form action={save} className="bg-white rounded-2xl border p-4 grid md:grid-cols-4 gap-2">
        <input name="name" required placeholder="Name" className="border rounded-xl px-3 py-2" />
        <input name="phone" placeholder="Phone" className="border rounded-xl px-3 py-2" />
        <select name="type" className="border rounded-xl px-3 py-2">
          <option value="account">Account / garage</option>
          <option value="walk_in">Walk-in</option>
          <option value="wholesale">Wholesale</option>
        </select>
        <button className="bg-slate-900 text-white rounded-xl">Add</button>
      </form>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">Name</th><th>Type</th><th>Phone</th><th>Invoices</th></tr></thead>
        <tbody>
          {customers.map((c) => (
            <tr key={c.id} className="border-t">
              <td className="px-3 py-2">{c.name}</td>
              <td>{c.type}</td>
              <td>{c.phone}</td>
              <td>{c.invoices.length}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
