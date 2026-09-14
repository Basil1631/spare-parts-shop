import { prisma } from "@/lib/prisma";
import { getSession } from "@/lib/auth";
import { toggleShop } from "@/app/actions";
import { redirect } from "next/navigation";
import { ConfirmSubmit } from "@/components/FormButtons";

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
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-[22px] font-semibold text-[#101622]">Shops</h1>
          <p className="text-sm text-slate-500 mt-1">Each shop gets a username. That is the login URL for every employee in that shop.</p>
        </div>
        <a href="/provider/shops/new" className="h-10 px-4 inline-flex items-center rounded-full bg-[#101622] text-white text-sm font-medium">
          Add shop
        </a>
      </div>
      {q.created ? (
        <div className="rounded-2xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
          Shop created. Staff login: <strong>/s/{q.created}</strong>
        </div>
      ) : null}
      <div className="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 text-left text-slate-500">
            <tr>
              <th className="px-4 py-3 font-medium">Shop</th>
              <th className="px-4 py-3 font-medium">Login URL</th>
              <th className="px-4 py-3 font-medium">Users</th>
              <th className="px-4 py-3 font-medium">Status</th>
              <th className="px-4 py-3" />
            </tr>
          </thead>
          <tbody>
            {shops.map((shop) => (
              <tr key={shop.id} className="border-t border-slate-100">
                <td className="px-4 py-3.5 font-medium text-[#101622]">{shop.name}</td>
                <td className="px-4 py-3">
                  <a className="text-teal-700 font-medium" href={`/s/${shop.username}/login`}>
                    /s/{shop.username}
                  </a>
                </td>
                <td className="px-4 py-3">{shop._count.users}</td>
                <td className="px-4 py-3">
                  <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${shop.status === "active" ? "bg-emerald-50 text-emerald-700" : "bg-amber-50 text-amber-700"}`}>
                    {shop.status}
                  </span>
                </td>
                <td className="px-4 py-3 text-right">
                  <form action={toggleShop}>
                    <input type="hidden" name="id" value={shop.id} />
                    <ConfirmSubmit
                      message={
                        shop.status === "suspended"
                          ? "Activate this shop? Staff will be able to sign in again."
                          : "Suspend this shop? Staff will be locked out until you activate it."
                      }
                      className="text-sm text-slate-600 hover:text-[#101622] h-auto px-0 bg-transparent"
                    >
                      {shop.status === "suspended" ? "Activate" : "Suspend"}
                    </ConfirmSubmit>
                  </form>
                </td>
              </tr>
            ))}
            {shops.length === 0 ? (
              <tr>
                <td colSpan={5} className="px-4 py-10 text-slate-500 text-center">
                  No shops yet. Add a garage to issue the first login URL.
                </td>
              </tr>
            ) : null}
          </tbody>
        </table>
      </div>
    </div>
  );
}
