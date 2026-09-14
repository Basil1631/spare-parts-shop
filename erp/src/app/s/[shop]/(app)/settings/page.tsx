import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveSettings } from "@/app/actions";
import { btnPrimary, field, PageHeader, Panel } from "@/components/ui";

export default async function SettingsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const shop = await prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } });
  const save = saveSettings.bind(null, username);
  return (
    <div>
      <PageHeader title="Shop settings" hint="Legal name and TRN print on invoices. The login URL is set by Inktek and does not change here." />
      <Panel className="p-5 max-w-lg space-y-3">
        <form action={save} className="space-y-3">
          <label className="block text-sm text-slate-600 space-y-1">
            Shop name
            <input name="name" defaultValue={shop.name} className={field} />
          </label>
          <label className="block text-sm text-slate-600 space-y-1">
            Legal name
            <input name="legalName" defaultValue={shop.legalName || ""} placeholder="Legal name" className={field} />
          </label>
          <label className="block text-sm text-slate-600 space-y-1">
            TRN
            <input name="trn" defaultValue={shop.trn || ""} placeholder="TRN" className={field} />
          </label>
          <label className="block text-sm text-slate-600 space-y-1">
            VAT %
            <input name="vatPercent" type="number" step="0.1" defaultValue={shop.vatPercent} className={field} />
          </label>
          <p className="text-sm text-slate-500">Shop login URL stays /s/{shop.username}.</p>
          <button className={btnPrimary}>Save</button>
        </form>
      </Panel>
    </div>
  );
}
