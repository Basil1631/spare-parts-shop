import { redirect } from "next/navigation";
import { getSession } from "@/lib/auth";
import { createShop } from "@/app/actions";

export default async function NewShopPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  const session = await getSession();
  if (!session || session.kind !== "provider") redirect("/provider/login");
  const q = await searchParams;
  return (
    <main className="max-w-lg mx-auto p-6">
      <a href="/provider" className="text-sm text-slate-500">← Back</a>
      <h1 className="text-xl font-semibold mt-3 mb-4">Add garage / shop</h1>
      {q.error === "taken" ? <p className="text-red-600 text-sm mb-3">That username is already used.</p> : null}
      {q.error === "1" ? <p className="text-red-600 text-sm mb-3">Fill shop name, username, owner email, and a password of 6+ characters.</p> : null}
      <form action={createShop} className="bg-white rounded-2xl border p-5 space-y-3">
        <input name="name" required placeholder="Shop / garage name" className="w-full border rounded-xl px-3 py-2" />
        <div>
          <input name="username" required placeholder="Username (becomes login URL)" className="w-full border rounded-xl px-3 py-2" />
          <p className="text-xs text-slate-500 mt-1">Staff will sign in at /s/this-username — one URL for the whole shop.</p>
        </div>
        <input name="legalName" placeholder="Legal name (optional)" className="w-full border rounded-xl px-3 py-2" />
        <input name="trn" placeholder="TRN (optional)" className="w-full border rounded-xl px-3 py-2" />
        <hr />
        <p className="text-sm font-medium">First shop owner login</p>
        <input name="ownerName" required placeholder="Owner name" className="w-full border rounded-xl px-3 py-2" />
        <input name="ownerEmail" type="email" required placeholder="Owner email" className="w-full border rounded-xl px-3 py-2" />
        <input name="ownerPassword" type="password" required placeholder="Owner password" className="w-full border rounded-xl px-3 py-2" />
        <button className="w-full bg-slate-900 text-white rounded-xl py-2.5">Create shop</button>
      </form>
    </main>
  );
}
