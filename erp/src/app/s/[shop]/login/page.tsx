import { shopLogin } from "@/app/actions";
import { prisma } from "@/lib/prisma";
import { notFound } from "next/navigation";

export default async function ShopLoginPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const shop = await prisma.shop.findUnique({ where: { username } });
  if (!shop) notFound();
  if (shop.status === "suspended") {
    return <main className="p-8 text-center">This shop is suspended. Contact Inktek.</main>;
  }
  const login = shopLogin.bind(null, username);
  return (
    <main className="min-h-screen flex items-center justify-center p-6">
      <form action={login} className="w-full max-w-md rounded-3xl bg-white border p-8 space-y-4">
        <p className="text-xs uppercase tracking-wider text-slate-400">Shop login</p>
        <h1 className="text-xl font-semibold">{shop.name}</h1>
        <p className="text-sm text-slate-500">
          URL for everyone in this shop: <span className="font-mono">/s/{shop.username}</span>. Use your own email and password.
        </p>
        {q.error === "1" ? <p className="text-sm text-red-600">Wrong email or password.</p> : null}
        <input name="email" type="email" required placeholder="Email" className="w-full rounded-xl border px-3 py-2" />
        <input name="password" type="password" required placeholder="Password" className="w-full rounded-xl border px-3 py-2" />
        <button className="w-full rounded-xl bg-slate-900 text-white py-2.5">Sign in</button>
      </form>
    </main>
  );
}
