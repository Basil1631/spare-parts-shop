import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { ROLE_LABEL } from "@/lib/roles";
import { saveStaff } from "@/app/actions";
import { aed } from "@/lib/money";

export default async function StaffPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const users = await prisma.user.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } });
  const save = saveStaff.bind(null, username);
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Staff</h1>
      <p className="text-sm text-slate-500">Everyone signs in at /s/{username} with their own email and password.</p>
      <form action={save} className="bg-white rounded-2xl border p-4 grid md:grid-cols-3 gap-2">
        <input name="name" required placeholder="Name" className="border rounded-xl px-3 py-2" />
        <input name="email" type="email" required placeholder="Email (login)" className="border rounded-xl px-3 py-2" />
        <input name="password" type="password" placeholder="Password (default password)" className="border rounded-xl px-3 py-2" />
        <select name="role" className="border rounded-xl px-3 py-2">
          {Object.entries(ROLE_LABEL).map(([value, label]) => (
            <option key={value} value={value}>{label}</option>
          ))}
        </select>
        <input name="salary" step="0.01" placeholder="Monthly salary AED" className="border rounded-xl px-3 py-2" />
        <input name="incentive" step="0.1" placeholder="Incentive %" className="border rounded-xl px-3 py-2" />
        <button className="bg-slate-900 text-white rounded-xl">Add staff</button>
      </form>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">Name</th><th>Email</th><th>Role</th><th>Salary</th></tr></thead>
        <tbody>
          {users.map((u) => (
            <tr key={u.id} className="border-t">
              <td className="px-3 py-2">{u.name}</td>
              <td>{u.email}</td>
              <td>{ROLE_LABEL[u.role]}</td>
              <td>{aed(u.monthlySalaryFils)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
