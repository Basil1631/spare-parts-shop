import { shopLogout } from "@/app/actions";
import { AppShell } from "@/components/AppShell";
import { shopContext } from "@/lib/auth";
import { NAV, NAV_GROUPS, NAV_ICONS, ROLE_LABEL, shopPath } from "@/lib/roles";

export default async function ShopLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ shop: string }>;
}) {
  const { shop: username } = await params;
  const session = await shopContext(username);
  const allowed = NAV.filter((n) => n.roles.includes(session.role));
  const groups = NAV_GROUPS.map((g) => ({
    title: g.title,
    items: allowed
      .filter((n) => g.keys.includes(n.key))
      .map((n) => ({ href: shopPath(username, n.href), label: n.label, icon: NAV_ICONS[n.key] })),
  })).filter((g) => g.items.length);
  const logout = shopLogout.bind(null, username);

  return (
    <AppShell
      brand={session.shopName}
      userName={session.name}
      userRole={ROLE_LABEL[session.role]}
      groups={groups}
      logoutAction={logout}
    >
      {children}
    </AppShell>
  );
}
