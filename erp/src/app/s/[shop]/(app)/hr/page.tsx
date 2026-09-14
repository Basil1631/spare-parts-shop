import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { shopRoster } from "@/lib/roster";
import { dubaiTime } from "@/lib/time";
import { prisma } from "@/lib/prisma";
import { DataTable, PageHeader, Panel } from "@/components/ui";

export default async function HrPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const [users, roster] = await Promise.all([
    prisma.user.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } }),
    shopRoster(s.shopId),
  ]);
  return (
    <div className="space-y-6">
      <PageHeader title="HR / payroll" hint="Attendance follows employee login. Salary and incentive sit on the staff card." />
      <div className="grid sm:grid-cols-2 gap-4">
        <Panel className="p-5">
          <div className="text-sm text-slate-500">Present</div>
          <div className="text-2xl font-semibold text-emerald-700">{roster.present.length}</div>
        </Panel>
        <Panel className="p-5">
          <div className="text-sm text-slate-500">Leave / not in</div>
          <div className="text-2xl font-semibold text-amber-700">{roster.leave.length}</div>
        </Panel>
      </div>
      <Panel className="p-5">
        <h2 className="font-semibold mb-3">Signed in today</h2>
        <ul className="text-sm divide-y divide-slate-100">
          {roster.present.map((a) => (
            <li key={a.id} className="py-2 flex justify-between">
              <span>{a.name}</span>
              <span className="text-slate-500 tabular-nums">{dubaiTime(a.loginAt)}</span>
            </li>
          ))}
          {roster.present.length === 0 ? <li className="py-4 text-slate-500">No logins yet.</li> : null}
        </ul>
      </Panel>
      <DataTable headers={["Staff", "Salary", "Incentive %"]}>
        {users.map((u) => (
          <tr key={u.id}>
            <td className="px-4 py-3 font-medium">{u.name}</td>
            <td className="tabular-nums">{aed(u.monthlySalaryFils)}</td>
            <td>{u.incentivePercent}%</td>
          </tr>
        ))}
      </DataTable>
    </div>
  );
}
