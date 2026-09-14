import { shopLogin } from "@/app/actions";
import { LoginShell } from "@/components/LoginShell";
import { PasswordField } from "@/components/PasswordField";
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
    return (
      <LoginShell>
        <div className="bg-white rounded-[24px] px-8 py-10 text-center">
          <h2 className="text-[20px] font-semibold">Shop suspended</h2>
          <p className="mt-2 text-[13px] text-[#ABACB0]">Contact Inktek Solutions.</p>
        </div>
      </LoginShell>
    );
  }
  const login = shopLogin.bind(null, username);
  return (
    <LoginShell>
      <div className="bg-white rounded-[24px] px-8 pt-8 pb-8 shadow-[0_18px_50px_rgba(16,22,34,0.08)]">
        <h2 className="text-[20px] font-semibold text-[#101622] tracking-tight">Sign in</h2>
        <p className="mt-1 text-[13px] text-[#ABACB0] leading-snug">
          {shop.name}. Same URL for every role. Use your own email and password.
        </p>
        {q.error === "1" ? <p className="mt-3 text-sm text-red-600">Wrong email or password.</p> : null}
        {q.error === "shop" ? <p className="mt-3 text-sm text-red-600">This shop is suspended. Contact Inktek Solutions.</p> : null}
        <form action={login} className="mt-6 space-y-4">
          <div>
            <label className="block text-[13px] text-[#6B7280] mb-1.5">Email</label>
            <input
              name="email"
              type="email"
              required
              autoComplete="username"
              className="w-full h-11 bg-white border border-[#E5E7EB] rounded-full px-4 text-[14px] text-[#101622] focus:outline-none focus:border-[#101622]"
            />
          </div>
          <PasswordField />
          <label className="flex items-center gap-2 text-[13px] text-[#6B7280]">
            <input type="checkbox" name="remember" className="rounded border-[#D1D5DB] text-[#101622] focus:ring-[#101622]" /> Remember me
          </label>
          <button className="w-full h-11 bg-[#101622] text-white rounded-full text-[14px] font-medium">Continue</button>
        </form>
      </div>
    </LoginShell>
  );
}
