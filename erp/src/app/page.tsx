import { redirect } from "next/navigation";

export default function Home() {
  return (
    <main className="min-h-screen flex items-center justify-center p-6">
      <div className="w-full max-w-lg rounded-3xl bg-white border border-slate-100 shadow-sm p-8 space-y-6">
        <div>
          <p className="text-xs uppercase tracking-wider text-slate-400">Spare Parts ERP</p>
          <h1 className="text-2xl font-semibold mt-1">Choose your login</h1>
          <p className="text-sm text-slate-500 mt-2">
            Super admin has a dedicated URL. Each shop (garage) has its own URL from the username set when the shop was added.
          </p>
        </div>
        <a href="/provider/login" className="block rounded-2xl bg-slate-900 text-white text-center py-3 font-medium">
          Super admin dashboard
        </a>
        <form
          className="space-y-2"
          action={async (fd) => {
            "use server";
            const shop = String(fd.get("shop") || "")
              .toLowerCase()
              .trim();
            if (shop) redirect(`/s/${shop}/login`);
          }}
        >
          <label className="text-sm text-slate-600">Shop username (login URL)</label>
          <input name="shop" placeholder="e.g. alain" className="w-full rounded-xl border border-slate-200 px-3 py-2" />
          <button className="w-full rounded-xl border border-slate-200 py-2.5 text-sm">Open shop login</button>
        </form>
      </div>
    </main>
  );
}
