import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";

export default async function HrPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const today = new Date();
  today.setUTCHours(0, 0, 0, 0);
  const [users, present] = await Promise.all([
    prisma.user.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" } }),
    prisma.attendance.findMany({ where: { shopId: s.shopId, workedOn: today }, include: { user: true } }),
  ]);
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">HR / payroll</h1>
      <p className="text-sm text-slate-500">Attendance is first login per day. Incentive % of extra sales is stored on the staff card (WPS file comes later).</p>
      <section className="bg-white rounded-2xl border p-5">
        <h2 className="font-medium mb-2">Present today</h2>
        <ul className="text-sm space-y-1">
          {present.map((a) => (
            <li key={a.id}>{a.user.name} · {a.loginAt.toISOString().slice(11, 16)} UTC</li>
          ))}
          {present.length === 0 ? <li className="text-slate-500">No logins yet.</li> : null}
        </ul>
      </section>
      <table className="w-full text-sm bg-white rounded-2xl">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">Staff</th><th>Salary</th><th>Incentive %</th></tr></thead>
        <tbody>
          {users.map((u) => (
            <tr key={u.id} className="border-t">
              <td className="px-3 py-2">{u.name}</td>
              <td>{aed(u.monthlySalaryFils)}</td>
              <td>{u.incentivePercent}%</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
