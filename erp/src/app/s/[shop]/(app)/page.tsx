import { prisma } from "@/lib/prisma";
import { aed } from "@/lib/money";
import { can, shopPath } from "@/lib/roles";
import { shopRoster } from "@/lib/roster";
import { dubaiDateLabel, dubaiDayRange, dubaiTime } from "@/lib/time";
import { shopModule } from "@/lib/access";
import { Banner } from "@/components/ui";
import Link from "next/link";

export default async function ShopDashboard({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "dashboard");
  const { start, end } = dubaiDayRange();
  const [parts, awaiting, unpaidBills, todaySales, roster] = await Promise.all([
    prisma.part.findMany({ where: { shopId: s.shopId }, include: { stocks: true } }),
    prisma.purchaseOrder.count({ where: { shopId: s.shopId, status: "awaiting_godown" } }),
    prisma.vendorBill.count({ where: { shopId: s.shopId, status: "unpaid" } }),
    prisma.invoice.aggregate({
      where: { shopId: s.shopId, createdAt: { gte: start, lt: end } },
      _sum: { totalFils: true },
      _count: true,
    }),
    shopRoster(s.shopId),
  ]);
  const low = parts.filter((p) => p.stocks.reduce((a, b) => a + b.qtyOnHand, 0) <= p.minQty).length;
  const showPeople = can(s.role, "attendance");

  return (
    <div className="space-y-6">
      {q.error === "denied" ? <Banner kind="error">You do not have access to that screen.</Banner> : null}
      <div>
        <h1 className="text-[22px] font-semibold text-[#101622] tracking-tight">Dashboard</h1>
        <p className="text-sm text-slate-500 mt-1">
          {s.shopName} · {dubaiDateLabel()} (Asia/Dubai)
        </p>
      </div>
      <div className="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {can(s.role, "invoices") || can(s.role, "pos") ? (
          <Kpi href={shopPath(username, can(s.role, "invoices") ? "/invoices" : "/pos")} label="Today invoices" value={String(todaySales._count)} />
        ) : null}
        {can(s.role, "invoices") || can(s.role, "reports") ? <Kpi href={shopPath(username, "/reports")} label="Today sales" value={aed(todaySales._sum.totalFils || 0)} /> : null}
        {can(s.role, "stock") ? <Kpi href={shopPath(username, "/stock")} label="Low stock" value={String(low)} warn={low > 0} /> : null}
        {can(s.role, "godown") ? (
          <Kpi href={shopPath(username, "/godown")} label="Awaiting godown" value={String(awaiting)} warn={awaiting > 0} />
        ) : null}
      </div>
      {showPeople ? (
        <div className="grid lg:grid-cols-2 gap-4">
          <section className="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div className="flex items-center justify-between mb-3">
              <h2 className="font-semibold text-[#101622]">Signed in today</h2>
              <span className="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">{roster.present.length}</span>
            </div>
            <p className="text-xs text-slate-500 mb-3">Present after the employee signs in on this shop URL (Dubai day).</p>
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
              <h2 className="font-semibold text-[#101622]">Not signed in today</h2>
              <span className="text-xs font-medium bg-amber-50 text-amber-800 px-2 py-0.5 rounded-full">{roster.leave.length}</span>
            </div>
            <p className="text-xs text-slate-500 mb-3">This is not approved leave — only “no login yet today” (weekends included).</p>
            <ul className="divide-y divide-slate-100 text-sm">
              {roster.leave.map((u) => (
                <li key={u.id} className="py-2.5">
                  <span className="font-medium text-[#101622]">{u.name}</span>
                  <span className="text-slate-500"> · {u.role}</span>
                </li>
              ))}
              {roster.leave.length === 0 ? <li className="py-6 text-slate-500">Everyone has signed in.</li> : null}
            </ul>
            <Link href={shopPath(username, "/attendance")} className="inline-block mt-3 text-sm text-teal-700 font-medium">
              Full attendance →
            </Link>
          </section>
        </div>
      ) : null}
      <div className="grid sm:grid-cols-2 gap-4">
        {can(s.role, "bills") ? <Kpi href={shopPath(username, "/bills")} label="Unpaid vendor bills" value={String(unpaidBills)} /> : null}
        {can(s.role, "parts") ? <Kpi href={shopPath(username, "/parts")} label="Catalog SKUs" value={String(parts.length)} /> : null}
      </div>
    </div>
  );
}

function Kpi({ label, value, warn, href }: { label: string; value: string; warn?: boolean; href?: string }) {
  const inner = (
    <div className={`rounded-2xl border bg-white p-5 shadow-sm h-full ${warn ? "border-amber-200" : "border-slate-100"}`}>
      <div className="text-sm text-slate-500">{label}</div>
      <div className={`text-[26px] font-semibold mt-1 ${warn ? "text-amber-700" : "text-[#101622]"}`}>{value}</div>
    </div>
  );
  return href ? <Link href={href}>{inner}</Link> : inner;
}
