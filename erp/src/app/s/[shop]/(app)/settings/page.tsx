import { prisma } from "@/lib/prisma";
import { saveSettings } from "@/app/actions";
import { Banner, field, FieldLabel, PageHeader, Panel } from "@/components/ui";
import { SubmitButton } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function SettingsPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "settings");
  const shop = await prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } });
  const save = saveSettings.bind(null, username);
  return (
    <div>
      <PageHeader title="Shop settings" hint="Legal name and TRN print on invoices. The login URL is set by Inktek and does not change here." />
      {q.ok ? <Banner kind="ok">Settings saved.</Banner> : null}
      <Panel className="p-5 max-w-lg">
        <form action={save} className="space-y-3">
          <div>
            <FieldLabel htmlFor="name">Shop name</FieldLabel>
            <input id="name" name="name" defaultValue={shop.name} className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="legalName">Legal name</FieldLabel>
            <input id="legalName" name="legalName" defaultValue={shop.legalName || ""} className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="trn">TRN</FieldLabel>
            <input id="trn" name="trn" defaultValue={shop.trn || ""} className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="vatPercent">VAT %</FieldLabel>
            <input id="vatPercent" name="vatPercent" type="number" step="0.1" defaultValue={shop.vatPercent} className={field} />
          </div>
          <p className="text-sm text-slate-500">Shop login URL stays /s/{shop.username}.</p>
          <SubmitButton>Save</SubmitButton>
        </form>
      </Panel>
    </div>
  );
}
