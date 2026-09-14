import { providerLogin } from "@/app/actions";
import { LoginShell } from "@/components/LoginShell";
import { PasswordField } from "@/components/PasswordField";

export default async function ProviderLoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  const q = await searchParams;
  return (
    <LoginShell>
      <div className="bg-white rounded-[24px] px-8 pt-8 pb-8 shadow-[0_18px_50px_rgba(16,22,34,0.08)]">
        <h2 className="text-[20px] font-semibold text-[#101622] tracking-tight">Sign in</h2>
        <p className="mt-1 text-[13px] text-[#ABACB0] leading-snug">Super admin console. Shop staff use their shop URL.</p>
        {q.error ? <p className="mt-3 text-sm text-red-600">Wrong email or password.</p> : null}
        <form action={providerLogin} className="mt-6 space-y-4">
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
