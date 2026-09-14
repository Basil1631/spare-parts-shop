import { shopContext } from "@/lib/auth";
import { shopRoster } from "@/lib/roster";
import { dubaiDateLabel, dubaiTime } from "@/lib/time";

export default async function AttendancePage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const roster = await shopRoster(s.shopId);
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-[22px] font-semibold text-[#101622] tracking-tight">Attendance</h1>
        <p className="text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
          {dubaiDateLabel()} · Dubai time. An employee is <strong>present</strong> after they sign in on /s/{username}. No login today = <strong>leave</strong>.
        </p>
      </div>
      <div className="grid lg:grid-cols-2 gap-4">
        <section className="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
          <header className="px-5 py-4 border-b border-slate-100 flex justify-between">
            <h2 className="font-semibold">Present</h2>
            <span className="text-sm text-emerald-700">{roster.present.length}</span>
          </header>
          <table className="w-full text-sm">
            <thead className="text-left text-slate-500 bg-slate-50">
              <tr>
                <th className="px-5 py-2 font-medium">Employee</th>
                <th className="font-medium">Role</th>
                <th className="font-medium">Login</th>
              </tr>
            </thead>
            <tbody>
              {roster.present.map((u) => (
                <tr key={u.id} className="border-t border-slate-100">
                  <td className="px-5 py-3 font-medium">{u.name}</td>
                  <td>{u.role}</td>
                  <td className="tabular-nums">{dubaiTime(u.loginAt)}</td>
                </tr>
              ))}
              {roster.present.length === 0 ? (
                <tr>
                  <td colSpan={3} className="px-5 py-8 text-slate-500">
                    No logins yet today.
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </section>
        <section className="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
          <header className="px-5 py-4 border-b border-slate-100 flex justify-between">
            <h2 className="font-semibold">Leave / not signed in</h2>
            <span className="text-sm text-amber-700">{roster.leave.length}</span>
          </header>
          <table className="w-full text-sm">
            <thead className="text-left text-slate-500 bg-slate-50">
              <tr>
                <th className="px-5 py-2 font-medium">Employee</th>
                <th className="font-medium">Role</th>
              </tr>
            </thead>
            <tbody>
              {roster.leave.map((u) => (
                <tr key={u.id} className="border-t border-slate-100">
                  <td className="px-5 py-3 font-medium">{u.name}</td>
                  <td>{u.role}</td>
                </tr>
              ))}
              {roster.leave.length === 0 ? (
                <tr>
                  <td colSpan={2} className="px-5 py-8 text-slate-500">
                    All active staff have signed in.
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </section>
      </div>
    </div>
  );
}
