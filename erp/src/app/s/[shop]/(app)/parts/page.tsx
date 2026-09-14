import { prisma } from "@/lib/prisma";
import { aed, fromFils } from "@/lib/money";
import { savePart } from "@/app/actions";
import { SubmitButton } from "@/components/FormButtons";
import { Banner, DataTable, field, FieldLabel, PageHeader, Panel } from "@/components/ui";
import { shopModule } from "@/lib/access";

export default async function PartsPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "parts");
  const parts = await prisma.part.findMany({
    where: { shopId: s.shopId },
    include: { stocks: true },
    orderBy: { name: "asc" },
  });
  const save = savePart.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Catalog" hint="SKU, OEM and selling price. On-hand quantity comes from incoming stock, not this form." />
      {q.ok ? <Banner kind="ok">Catalog saved.</Banner> : null}
      {q.error ? <Banner kind="error">SKU and name are required.</Banner> : null}
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-3 gap-3">
          <div>
            <FieldLabel htmlFor="sku">SKU</FieldLabel>
            <input id="sku" name="sku" required className={field} />
          </div>
          <div className="md:col-span-2">
            <FieldLabel htmlFor="name">Name</FieldLabel>
            <input id="name" name="name" required className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="brand">Brand</FieldLabel>
            <input id="brand" name="brand" className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="oemNumber">OEM</FieldLabel>
            <input id="oemNumber" name="oemNumber" className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="price">Sale AED</FieldLabel>
            <input id="price" name="price" step="0.01" className={field} />
          </div>
          <div>
            <FieldLabel htmlFor="minQty">Min qty</FieldLabel>
            <input id="minQty" name="minQty" type="number" defaultValue={0} className={field} />
          </div>
          <div className="md:col-span-3">
            <SubmitButton>Add part</SubmitButton>
          </div>
        </form>
      </Panel>
      <DataTable headers={["SKU", "Name", "Brand", "On hand", "Price", "Active"]}>
        {parts.map((p) => {
          const qty = p.stocks.reduce((a, b) => a + b.qtyOnHand, 0);
          const low = qty <= p.minQty;
          return (
            <tr key={p.id} className="hover:bg-slate-50/80">
              <td className="px-4 py-3 font-medium tabular-nums">{p.sku}</td>
              <td className="px-4 py-3">{p.name}</td>
              <td className="px-4 py-3 text-slate-500">{p.brand || "—"}</td>
              <td className={`px-4 py-3 ${low ? "text-amber-700 font-medium" : ""}`}>{qty}</td>
              <td className="px-4 py-3 tabular-nums">{aed(p.salePriceFils)}</td>
              <td className="px-4 py-3">
                <form action={save} className="flex gap-2 items-center">
                  <input type="hidden" name="id" value={p.id} />
                  <input type="hidden" name="sku" value={p.sku} />
                  <input type="hidden" name="name" value={p.name} />
                  <input type="hidden" name="brand" value={p.brand || ""} />
                  <input type="hidden" name="oemNumber" value={p.oemNumber || ""} />
                  <input type="hidden" name="price" value={fromFils(p.salePriceFils)} />
                  <input type="hidden" name="minQty" value={p.minQty} />
                  <input type="hidden" name="active" value={p.active ? "false" : "true"} />
                  <SubmitButton className="text-sm text-teal-700 font-medium h-auto px-0 bg-transparent hover:bg-transparent">
                    {p.active ? "Deactivate" : "Activate"}
                  </SubmitButton>
                </form>
              </td>
            </tr>
          );
        })}
        {parts.length === 0 ? (
          <tr>
            <td colSpan={6} className="px-4 py-8 text-slate-500">
              No parts yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
