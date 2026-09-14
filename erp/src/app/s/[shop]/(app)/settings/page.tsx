import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveSettings } from "@/app/actions";

export default async function SettingsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const shop = await prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } });
  const save = saveSettings.bind(null, username);
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-4">Shop settings</h1>
      <form action={save} className="bg-white rounded-2xl border p-5 max-w-lg space-y-3">
        <input name="name" defaultValue={shop.name} className="w-full border rounded-xl px-3 py-2" />
        <input name="legalName" defaultValue={shop.legalName || ""} placeholder="Legal name" className="w-full border rounded-xl px-3 py-2" />
        <input name="trn" defaultValue={shop.trn || ""} placeholder="TRN" className="w-full border rounded-xl px-3 py-2" />
        <label className="text-sm">VAT %</label>
        <input name="vatPercent" type="number" step="0.1" defaultValue={shop.vatPercent} className="w-full border rounded-xl px-3 py-2" />
        <p className="text-sm text-slate-500">Shop login URL stays /s/{shop.username} (set by super admin).</p>
        <button className="bg-slate-900 text-white rounded-xl px-4 py-2">Save</button>
      </form>
    </div>
  );
}
