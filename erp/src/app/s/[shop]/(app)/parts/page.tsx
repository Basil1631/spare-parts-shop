import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { savePart } from "@/app/actions";
import { btnPrimary, DataTable, field, PageHeader, Panel } from "@/components/ui";

export default async function PartsPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const parts = await prisma.part.findMany({
    where: { shopId: s.shopId },
    include: { stocks: true },
    orderBy: { name: "asc" },
  });
  const save = savePart.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Catalog" hint="SKU, OEM and selling price. Stock quantity is never typed here — it comes from incoming stock." />
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-6 gap-2">
          <input name="sku" required placeholder="SKU" className={field} />
          <input name="name" required placeholder="Name" className={`${field} md:col-span-2`} />
          <input name="brand" placeholder="Brand" className={field} />
          <input name="oemNumber" placeholder="OEM" className={field} />
          <input name="price" step="0.01" placeholder="Sale AED" className={field} />
          <input name="minQty" type="number" defaultValue={0} placeholder="Min qty" className={field} />
          <button className={`${btnPrimary} md:col-span-5`}>Add part</button>
        </form>
      </Panel>
      <DataTable headers={["SKU", "Name", "OEM", "On hand", "Price"]}>
        {parts.map((p) => {
          const qty = p.stocks.reduce((a, b) => a + b.qtyOnHand, 0);
          const low = qty <= p.minQty;
          return (
            <tr key={p.id} className="hover:bg-slate-50/80">
              <td className="px-4 py-3 font-medium tabular-nums">{p.sku}</td>
              <td>{p.name}</td>
              <td className="text-slate-500">{p.oemNumber || "—"}</td>
              <td className={low ? "text-amber-700 font-medium" : ""}>{qty}</td>
              <td className="tabular-nums">{aed(p.salePriceFils)}</td>
            </tr>
          );
        })}
        {parts.length === 0 ? (
          <tr>
            <td colSpan={5} className="px-4 py-8 text-slate-500">
              No parts yet.
            </td>
          </tr>
        ) : null}
      </DataTable>
    </div>
  );
}
