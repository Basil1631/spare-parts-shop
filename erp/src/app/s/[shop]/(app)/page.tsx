import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { can, shopPath } from "@/lib/roles";
import { shopRoster } from "@/lib/roster";
import { dubaiDateLabel, dubaiTime } from "@/lib/time";

export default async function ShopDashboard({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const [parts, awaiting, unpaidBills, todaySales, roster] = await Promise.all([
    prisma.part.findMany({ where: { shopId: s.shopId }, include: { stocks: true } }),
    prisma.purchaseOrder.count({ where: { shopId: s.shopId, status: "awaiting_godown" } }),
    prisma.vendorBill.count({ where: { shopId: s.shopId, status: "unpaid" } }),
    prisma.invoice.aggregate({
      where: { shopId: s.shopId, createdAt: { gte: new Date(new Date().toDateString()) } },
      _sum: { totalFils: true },
      _count: true,
    }),
    shopRoster(s.shopId),
  ]);
  const low = parts.filter((p) => p.stocks.reduce((a, b) => a + b.qtyOnHand, 0) <= p.minQty).length;
  const showPeople = can(s.role, "attendance");

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-[22px] font-semibold text-[#101622] tracking-tight">Dashboard</h1>
        <p className="text-sm text-slate-500 mt-1">
          {s.name} · {dubaiDateLabel()} (Asia/Dubai)
        </p>
      </div>
      <div className="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <Kpi label="Today invoices" value={String(todaySales._count)} />
        <Kpi label="Today sales" value={aed(todaySales._sum.totalFils || 0)} />
        <Kpi label="Low stock" value={String(low)} warn={low > 0} />
        <Kpi label="Awaiting godown" value={String(awaiting)} warn={awaiting > 0} />
      </div>
      {showPeople ? (
        <div className="grid lg:grid-cols-2 gap-4">
          <section className="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div className="flex items-center justify-between mb-3">
              <h2 className="font-semibold text-[#101622]">Present today</h2>
              <span className="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">{roster.present.length}</span>
            </div>
            <p className="text-xs text-slate-500 mb-3">Marked present when the employee signs in on this shop URL.</p>
            <ul className="divide-y divide-slate-100 text-sm">
              {roster.present.map((u) => (
                <li key={u.id} className="py-2.5 flex justify-between gap-3">
                  <span>
                    <span className="font-medium text-[#101622]">{u.name}</span>
                    <span className="text-slate-500"> · {u.role}</span>
                  </span>
                  <span className="text-slate-500 tabular-nums">{dubaiTime(u.loginAt)}</span>
                </li>
              ))}
              {roster.present.length === 0 ? <li className="py-6 text-slate-500">No employee has signed in yet today.</li> : null}
            </ul>
          </section>
          <section className="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div className="flex items-center justify-between mb-3">
              <h2 className="font-semibold text-[#101622]">On leave / not in</h2>
              <span className="text-xs font-medium bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full">{roster.leave.length}</span>
            </div>
            <p className="text-xs text-slate-500 mb-3">Active staff with no login today are treated as leave.</p>
            <ul className="divide-y divide-slate-100 text-sm">
              {roster.leave.map((u) => (
                <li key={u.id} className="py-2.5">
                  <span className="font-medium text-[#101622]">{u.name}</span>
                  <span className="text-slate-500"> · {u.role}</span>
                </li>
              ))}
              {roster.leave.length === 0 ? <li className="py-6 text-slate-500">Everyone is present.</li> : null}
            </ul>
            <a href={shopPath(username, "/attendance")} className="inline-block mt-3 text-sm text-teal-700 font-medium">
              Full attendance →
            </a>
          </section>
        </div>
      ) : null}
      <div className="grid sm:grid-cols-2 gap-4">
        <Kpi label="Unpaid vendor bills" value={String(unpaidBills)} />
        <Kpi label="Catalog SKUs" value={String(parts.length)} />
      </div>
    </div>
  );
}

function Kpi({ label, value, warn }: { label: string; value: string; warn?: boolean }) {
  return (
    <div className={`rounded-2xl border bg-white p-5 shadow-sm ${warn ? "border-amber-200" : "border-slate-100"}`}>
      <div className="text-sm text-slate-500">{label}</div>
      <div className={`text-[26px] font-semibold mt-1 ${warn ? "text-amber-700" : "text-[#101622]"}`}>{value}</div>
    </div>
  );
}
