import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { ROLE_LABEL } from "@/lib/roles";
import { saveStaff } from "@/app/actions";
import { aed } from "@/lib/money";
import { btnPrimary, DataTable, field, PageHeader, Panel } from "@/components/ui";

export default async function StaffPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const users = await prisma.user.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } });
  const save = saveStaff.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Staff" hint={`Everyone signs in at /s/${username} with their own email and password. First login each Dubai day marks them present.`} />
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-3 gap-2">
          <input name="name" required placeholder="Name" className={field} />
          <input name="email" type="email" required placeholder="Email (login)" className={field} />
          <input name="password" type="password" placeholder="Password (default: password)" className={field} />
          <select name="role" className={field}>
            {Object.entries(ROLE_LABEL).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
          <input name="salary" step="0.01" placeholder="Monthly salary AED" className={field} />
          <input name="incentive" step="0.1" placeholder="Incentive %" className={field} />
          <button className={`${btnPrimary} md:col-span-3`}>Add staff</button>
        </form>
      </Panel>
      <DataTable headers={["Name", "Email", "Role", "Salary"]}>
        {users.map((u) => (
          <tr key={u.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{u.name}</td>
            <td className="text-slate-500">{u.email}</td>
            <td>{ROLE_LABEL[u.role]}</td>
            <td className="tabular-nums">{aed(u.monthlySalaryFils)}</td>
          </tr>
        ))}
      </DataTable>
    </div>
  );
}
