import { providerLogout } from "@/app/actions";
import { AppShell } from "@/components/AppShell";
import { getSession } from "@/lib/auth";
import { redirect } from "next/navigation";

export default async function ProviderConsoleLayout({ children }: { children: React.ReactNode }) {
  const session = await getSession();
  if (!session || session.kind !== "provider") redirect("/provider/login");
  return (
    <AppShell
      brand="Inktek console"
      subtitle="Super admin"
      userName={session.name}
      userRole="Super admin"
      groups={[
        {
          title: "Manage",
          items: [
            { href: "/provider", label: "Shops", icon: "building" },
            { href: "/provider/shops/new", label: "Add shop", icon: "users" },
          ],
        },
      ]}
      logoutAction={providerLogout}
    >
      {children}
    </AppShell>
  );
}
