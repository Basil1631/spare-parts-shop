import { shopLogout } from "@/app/actions";
import { shopContext } from "@/lib/auth";
import { NAV, ROLE_LABEL, shopPath } from "@/lib/roles";

export default async function ShopLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ shop: string }>;
}) {
  const { shop: username } = await params;
  const session = await shopContext(username);
  const logout = shopLogout.bind(null, username);
  return (
    <div className="min-h-screen flex">
      <aside className="w-60 bg-slate-950 text-slate-100 p-4 flex flex-col">
        <div className="mb-6">
          <div className="text-[11px] uppercase tracking-wider text-slate-500">Shop</div>
          <div className="font-semibold leading-tight">{session.shopName}</div>
          <div className="text-xs text-slate-400 mt-1">/s/{username}</div>
        </div>
        <nav className="space-y-0.5 flex-1 text-sm">
          {NAV.filter((n) => n.roles.includes(session.role)).map((n) => (
            <a key={n.key} href={shopPath(username, n.href)} className="block rounded-lg px-3 py-2 hover:bg-white/10">
              {n.label}
            </a>
          ))}
        </nav>
        <div className="text-xs text-slate-400">
          <div>{session.name}</div>
          <div>{ROLE_LABEL[session.role]}</div>
          <form action={logout} className="mt-2">
            <button className="text-slate-300">Sign out</button>
          </form>
        </div>
      </aside>
      <div className="flex-1 p-6">{children}</div>
    </div>
  );
}
