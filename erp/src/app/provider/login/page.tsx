import { providerLogin } from "@/app/actions";

export default async function ProviderLoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  const q = await searchParams;
  return (
    <main className="min-h-screen flex items-center justify-center p-6">
      <form action={providerLogin} className="w-full max-w-md rounded-3xl bg-white border border-slate-100 p-8 space-y-4">
        <h1 className="text-xl font-semibold">Super admin</h1>
        <p className="text-sm text-slate-500">This URL is only for Inktek. Shop staff do not use it.</p>
        {q.error ? <p className="text-sm text-red-600">Wrong email or password.</p> : null}
        <input name="email" type="email" required placeholder="Email" className="w-full rounded-xl border px-3 py-2" />
        <input name="password" type="password" required placeholder="Password" className="w-full rounded-xl border px-3 py-2" />
        <button className="w-full rounded-xl bg-slate-900 text-white py-2.5">Sign in</button>
      </form>
    </main>
  );
}
