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
  const field = "w-full h-11 border border-slate-200 rounded-xl px-3 text-sm text-[#101622] focus:outline-none focus:border-[#101622]";
  return (
    <div className="max-w-lg">
      <h1 className="text-[22px] font-semibold text-[#101622]">Add garage / shop</h1>
      <p className="text-sm text-slate-500 mt-1 mb-5">Username becomes the shop login URL. Owner and later staff all use that URL with different emails.</p>
      {q.error === "taken" ? <p className="text-red-600 text-sm mb-3">That username is already used.</p> : null}
      {q.error === "1" ? <p className="text-red-600 text-sm mb-3">Fill shop name, username, owner email, and a password of 6+ characters.</p> : null}
      <form action={createShop} className="bg-white rounded-2xl border border-slate-100 p-5 space-y-3 shadow-sm">
        <input name="name" required placeholder="Shop / garage name" className={field} />
        <div>
          <input name="username" required placeholder="Username (login URL)" className={field} />
          <p className="text-xs text-slate-500 mt-1">Staff sign in at /s/this-username</p>
        </div>
        <input name="legalName" placeholder="Legal name (optional)" className={field} />
        <input name="trn" placeholder="TRN (optional)" className={field} />
        <div className="pt-2 text-sm font-medium text-[#101622]">First shop owner</div>
        <input name="ownerName" required placeholder="Owner name" className={field} />
        <input name="ownerEmail" type="email" required placeholder="Owner email" className={field} />
        <input name="ownerPassword" type="password" required placeholder="Owner password" className={field} />
        <button className="w-full h-11 bg-[#101622] text-white rounded-full text-sm font-medium">Create shop</button>
      </form>
    </div>
  );
}
