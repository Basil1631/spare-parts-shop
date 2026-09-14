import { prisma } from "@/lib/prisma";
import { ROLE_LABEL } from "@/lib/roles";
import { saveStaff } from "@/app/actions";
import { aed } from "@/lib/money";
import { Banner, DataTable, field, FieldLabel, PageHeader, Panel } from "@/components/ui";
import { SubmitButton } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";
import type { Role } from "@prisma/client";

export default async function StaffPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "staff");
  const users = await prisma.user.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } });
  const save = saveStaff.bind(null, username);
  const roles = (Object.entries(ROLE_LABEL) as [Role, string][]).filter(([value]) => value !== "owner" || s.role === "owner");
  return (
    <div className="space-y-6">
      <PageHeader title="Staff" hint={`Everyone signs in at /s/${username} with their own email and password. First login each Dubai day marks them present. Password must be at least 6 characters.`} />
      {q.ok ? <Banner kind="ok">Staff member added.</Banner> : null}
      {q.error === "password" ? <Banner kind="error">Password must be at least 6 characters.</Banner> : null}
      {q.error === "taken" ? <Banner kind="error">That email is already used in this shop.</Banner> : null}
      {q.error === "1" ? <Banner kind="error">Name and email are required.</Banner> : null}
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-3 gap-3">
          <div>
            <FieldLabel>Name</FieldLabel>
            <input name="name" required className={field} />
          </div>
          <div>
            <FieldLabel>Email (login)</FieldLabel>
            <input name="email" type="email" required className={field} />
          </div>
          <div>
            <FieldLabel>Password</FieldLabel>
            <input name="password" type="password" required minLength={6} className={field} />
          </div>
          <div>
            <FieldLabel>Role</FieldLabel>
            <select name="role" className={field}>
              {roles.map(([value, label]) => (
                <option key={value} value={value}>
                  {label}
                </option>
              ))}
            </select>
          </div>
          <div>
            <FieldLabel>Monthly salary AED</FieldLabel>
            <input name="salary" step="0.01" className={field} />
          </div>
          <div>
            <FieldLabel>Incentive %</FieldLabel>
            <input name="incentive" step="0.1" className={field} />
          </div>
          <div className="md:col-span-3">
            <SubmitButton>Add staff</SubmitButton>
          </div>
        </form>
      </Panel>
      <DataTable headers={["Name", "Email", "Role", "Salary"]}>
        {users.map((u) => (
          <tr key={u.id} className="hover:bg-slate-50/80">
            <td className="px-4 py-3 font-medium">{u.name}</td>
            <td className="px-4 py-3 text-slate-500">{u.email}</td>
            <td className="px-4 py-3">{ROLE_LABEL[u.role]}</td>
            <td className="px-4 py-3 tabular-nums">{aed(u.monthlySalaryFils)}</td>
          </tr>
        ))}
      </DataTable>
    </div>
  );
}
