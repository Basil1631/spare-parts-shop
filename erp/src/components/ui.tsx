export const field =
  "h-11 w-full border border-slate-200 rounded-xl px-3.5 text-sm text-[#101622] bg-white placeholder:text-slate-400 focus:outline-none focus:border-[#101622] focus:ring-1 focus:ring-[#101622]";

export const btnPrimary =
  "h-11 px-5 inline-flex items-center justify-center rounded-full bg-[#101622] text-white text-sm font-medium hover:bg-black transition";

export const btnQuiet =
  "h-11 px-4 inline-flex items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:border-slate-300";

export function PageHeader({
  title,
  hint,
  action,
}: {
  title: string;
  hint?: string;
  action?: React.ReactNode;
}) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-3 mb-6">
      <div>
        <h1 className="text-[22px] font-semibold text-[#101622] tracking-tight">{title}</h1>
        {hint ? <p className="text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">{hint}</p> : null}
      </div>
      {action}
    </div>
  );
}

export function Panel({ children, className = "" }: { children: React.ReactNode; className?: string }) {
  return <div className={`bg-white rounded-2xl border border-slate-100 shadow-sm ${className}`}>{children}</div>;
}

export function Badge({
  tone = "muted",
  children,
}: {
  tone?: "ok" | "warn" | "muted" | "danger";
  children: React.ReactNode;
}) {
  const map = {
    ok: "bg-emerald-50 text-emerald-700",
    warn: "bg-amber-50 text-amber-800",
    muted: "bg-slate-100 text-slate-600",
    danger: "bg-red-50 text-red-700",
  };
  return <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium capitalize ${map[tone]}`}>{children}</span>;
}

export function DataTable({ headers, children }: { headers: string[]; children: React.ReactNode }) {
  return (
    <Panel className="overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 text-left text-slate-500">
            <tr>
              {headers.map((h) => (
                <th key={h} className="px-4 py-3 font-medium whitespace-nowrap">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">{children}</tbody>
        </table>
      </div>
    </Panel>
  );
}

export function FieldLabel({ htmlFor, children }: { htmlFor?: string; children: React.ReactNode }) {
  return (
    <label htmlFor={htmlFor} className="block text-xs font-medium text-slate-600 mb-1">
      {children}
    </label>
  );
}

export function Banner({ kind, children }: { kind: "ok" | "error"; children: React.ReactNode }) {
  const cls = kind === "ok" ? "bg-emerald-50 text-emerald-800" : "bg-red-50 text-red-700";
  return <div className={`rounded-2xl px-4 py-3 text-sm mb-4 ${cls}`}>{children}</div>;
}

export function statusTone(status: string): "ok" | "warn" | "muted" | "danger" {
  if (["paid", "active", "completed", "closed", "received"].includes(status)) return "ok";
  if (["unpaid", "awaiting_godown", "parts_pending", "low", "suspended", "leave"].includes(status)) return "warn";
  if (["cancelled"].includes(status)) return "danger";
  return "muted";
}
