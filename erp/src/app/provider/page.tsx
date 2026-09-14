import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { getSession } from "@/lib/auth";
import { providerLogout, toggleShop } from "@/app/actions";

export default async function ProviderHome({
  searchParams,
}: {
  searchParams: Promise<{ created?: string }>;
}) {
  const session = await getSession();
  if (!session || session.kind !== "provider") redirect("/provider/login");
  const shops = await prisma.shop.findMany({ orderBy: { createdAt: "desc" }, include: { _count: { select: { users: true } } } });
  const q = await searchParams;

  return (
    <div className="min-h-screen">
      <header className="border-b bg-white px-6 py-4 flex justify-between items-center">
        <div>
          <div className="text-xs uppercase tracking-wider text-slate-400">Provider console</div>
          <h1 className="font-semibold">Inktek · Super admin</h1>
        </div>
        <form action={providerLogout}>
          <button className="text-sm text-slate-600">Sign out</button>
        </form>
      </header>
      <main className="max-w-5xl mx-auto p-6 space-y-6">
        {q.created ? (
          <div className="rounded-xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
            Shop created. Login URL: <strong>/s/{q.created}</strong>
          </div>
        ) : null}
        <div className="flex justify-between items-end">
          <p className="text-slate-600 text-sm">Add a garage/shop with a username. That username is the shop login URL. Staff in the shop all use that URL with different emails and passwords.</p>
          <a href="/provider/shops/new" className="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm">Add shop</a>
        </div>
        <div className="bg-white rounded-2xl border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-slate-50 text-left">
              <tr>
                <th className="px-4 py-3">Shop</th>
                <th>Username / URL</th>
                <th>Users</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {shops.map((shop) => (
                <tr key={shop.id} className="border-t">
                  <td className="px-4 py-3 font-medium">{shop.name}</td>
                  <td>
                    <a className="text-teal-700" href={`/s/${shop.username}/login`}>
                      /s/{shop.username}
                    </a>
                  </td>
                  <td>{shop._count.users}</td>
                  <td>{shop.status}</td>
                  <td>
                    <form action={toggleShop}>
                      <input type="hidden" name="id" value={shop.id} />
                      <button className="text-sm text-slate-600">{shop.status === "suspended" ? "Activate" : "Suspend"}</button>
                    </form>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </main>
    </div>
  );
}
