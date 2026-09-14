"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { Icon } from "./Icon";

export type NavItem = { href: string; label: string; icon: string };
export type NavGroup = { title: string; items: NavItem[] };

export function AppShell({
  brand,
  subtitle,
  userName,
  userRole,
  groups,
  logoutAction,
  children,
}: {
  brand: string;
  subtitle?: string;
  userName: string;
  userRole: string;
  groups: NavGroup[];
  logoutAction: (formData: FormData) => void | Promise<void>;
  children: React.ReactNode;
}) {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") setOpen(false);
    }
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);
  const flat = groups.flatMap((g) => g.items);
  const current = flat.find((item) => {
    const root = item.href === "/provider" || /^\/s\/[^/]+$/.test(item.href);
    return root ? pathname === item.href : pathname === item.href || pathname.startsWith(`${item.href}/`);
  });

  function Nav({ logout }: { logout?: boolean }) {
    return (
      <>
        <div className="px-5 pt-6 pb-4 border-b border-white/10">
          <div className="text-[15px] font-semibold tracking-tight text-[#D7F08A]" style={{ fontFamily: "var(--font-poppins), Poppins, Inter, sans-serif" }}>
            Partszone
          </div>
          <div className="text-[13px] font-medium text-white mt-2 leading-tight">{brand}</div>
          {subtitle ? <div className="text-[11px] text-slate-400 mt-1">{subtitle}</div> : null}
        </div>
        <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-5">
          {groups.map((group) =>
            group.items.length ? (
              <div key={group.title}>
                <div className="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">{group.title}</div>
                <div className="space-y-0.5">
                  {group.items.map((item) => {
                    const root = item.href === "/provider" || /^\/s\/[^/]+$/.test(item.href);
                    const on = root ? pathname === item.href : pathname === item.href || pathname.startsWith(`${item.href}/`);
                    return (
                      <Link
                        key={item.href}
                        href={item.href}
                        onClick={() => setOpen(false)}
                        className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition ${
                          on ? "bg-white/10 text-white ring-1 ring-white/10" : "text-slate-300 hover:bg-white/5 hover:text-white"
                        }`}
                      >
                        <Icon name={item.icon} />
                        {item.label}
                      </Link>
                    );
                  })}
                </div>
              </div>
            ) : null,
          )}
        </nav>
        <div className="p-4 border-t border-white/10">
          <div className="text-sm font-medium truncate">{userName}</div>
          <div className="text-xs text-slate-400 mb-3 truncate">{userRole}</div>
          {logout ? (
            <form action={logoutAction}>
              <button className="w-full h-10 rounded-xl bg-white/10 hover:bg-white/15 text-sm font-medium">Log out</button>
            </form>
          ) : null}
        </div>
      </>
    );
  }

  return (
    <div className="min-h-screen flex bg-[#f3f5f8] text-slate-800">
      <aside className="hidden lg:flex w-[248px] shrink-0 bg-[#09131F] text-slate-100 flex-col min-h-screen sticky top-0 h-screen">
        <Nav logout />
      </aside>
      {open ? (
        <div className="lg:hidden fixed inset-0 z-40">
          <button className="absolute inset-0 bg-black/40" aria-label="Close menu" onClick={() => setOpen(false)} />
          <aside className="relative w-[248px] h-full bg-[#09131F] text-slate-100 flex flex-col">
            <Nav />
          </aside>
        </div>
      ) : null}
      <div className="flex-1 min-w-0 flex flex-col">
        <header className="h-14 px-4 lg:px-8 flex items-center justify-between gap-3 border-b border-slate-200/80 bg-white/80 backdrop-blur sticky top-0 z-20">
          <div className="flex items-center gap-3 min-w-0">
            <button type="button" className="lg:hidden p-2 rounded-xl hover:bg-slate-100" onClick={() => setOpen(true)} aria-label="Open menu">
              <Icon name="menu" className="w-5 h-5" />
            </button>
            <div className="min-w-0">
              <div className="text-sm font-semibold text-[#101622] truncate">{current?.label || "Partszone"}</div>
              <div className="text-[11px] text-slate-500 truncate lg:hidden">{brand}</div>
            </div>
          </div>
          <form action={logoutAction} className="lg:hidden">
            <button className="h-9 px-3 rounded-full border border-slate-200 text-sm font-medium">Log out</button>
          </form>
        </header>
        <main className="flex-1 p-4 sm:p-6 lg:p-8">{children}</main>
      </div>
    </div>
  );
}
