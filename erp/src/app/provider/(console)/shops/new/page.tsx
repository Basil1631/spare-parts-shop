import { redirect } from "next/navigation";
import { getSession } from "@/lib/auth";
import { createShop } from "@/app/actions";
import { SubmitButton } from "@/components/FormButtons";

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
        <label className="block text-xs font-medium text-slate-600">
          Shop / garage name
          <input name="name" required className={`${field} mt-1`} />
        </label>
        <label className="block text-xs font-medium text-slate-600">
          Username (login URL)
          <input name="username" required className={`${field} mt-1`} />
          <span className="block text-xs font-normal text-slate-500 mt-1">Staff sign in at /s/this-username</span>
        </label>
        <label className="block text-xs font-medium text-slate-600">
          Legal name (optional)
          <input name="legalName" className={`${field} mt-1`} />
        </label>
        <label className="block text-xs font-medium text-slate-600">
          TRN (optional)
          <input name="trn" className={`${field} mt-1`} />
        </label>
        <div className="pt-2 text-sm font-medium text-[#101622]">First shop owner</div>
        <label className="block text-xs font-medium text-slate-600">
          Owner name
          <input name="ownerName" required className={`${field} mt-1`} />
        </label>
        <label className="block text-xs font-medium text-slate-600">
          Owner email
          <input name="ownerEmail" type="email" required className={`${field} mt-1`} />
        </label>
        <label className="block text-xs font-medium text-slate-600">
          Owner password
          <input name="ownerPassword" type="password" required minLength={6} className={`${field} mt-1`} />
        </label>
        <SubmitButton className="w-full h-11 bg-[#101622] text-white rounded-full text-sm font-medium">Create shop</SubmitButton>
      </form>
    </div>
  );
}
