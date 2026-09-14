import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { savePart } from "@/app/actions";

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
      <h1 className="text-2xl font-semibold">Catalog</h1>
      <form action={save} className="bg-white rounded-2xl border p-4 grid md:grid-cols-6 gap-2">
        <input name="sku" required placeholder="SKU" className="border rounded-xl px-3 py-2" />
        <input name="name" required placeholder="Name" className="border rounded-xl px-3 py-2 md:col-span-2" />
        <input name="brand" placeholder="Brand" className="border rounded-xl px-3 py-2" />
        <input name="oemNumber" placeholder="OEM" className="border rounded-xl px-3 py-2" />
        <input name="price" step="0.01" placeholder="Sale AED" className="border rounded-xl px-3 py-2" />
        <input name="minQty" type="number" defaultValue={0} className="border rounded-xl px-3 py-2" />
        <button className="bg-slate-900 text-white rounded-xl">Add part</button>
      </form>
      <table className="w-full text-sm bg-white rounded-2xl overflow-hidden">
        <thead className="bg-slate-50 text-left"><tr><th className="px-3 py-2">SKU</th><th>Name</th><th>OEM</th><th>On hand</th><th>Price</th></tr></thead>
        <tbody>
          {parts.map((p) => (
            <tr key={p.id} className="border-t">
              <td className="px-3 py-2">{p.sku}</td>
              <td>{p.name}</td>
              <td>{p.oemNumber}</td>
              <td>{p.stocks.reduce((a, b) => a + b.qtyOnHand, 0)}</td>
              <td>{aed(p.salePriceFils)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
