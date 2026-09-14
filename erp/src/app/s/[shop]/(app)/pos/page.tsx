import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { aed } from "@/lib/money";
import { createPosSale } from "@/app/actions";

export default async function PosPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const parts = await prisma.part.findMany({
    where: { shopId: s.shopId, active: true },
    include: { stocks: true },
    orderBy: { name: "asc" },
  });
  const sale = createPosSale.bind(null, username);
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-4">POS</h1>
      <form action={sale} className="bg-white rounded-2xl border p-5 grid md:grid-cols-2 gap-3 max-w-3xl">
        <select name="partId" required className="border rounded-xl px-3 py-2">
          <option value="">Part</option>
          {parts.map((p) => (
            <option key={p.id} value={p.id}>
              {p.name} · {p.sku} · on hand {p.stocks.reduce((a, b) => a + b.qtyOnHand, 0)} · {aed(p.salePriceFils)}
            </option>
          ))}
        </select>
        <input name="qty" type="number" min={1} defaultValue={1} className="border rounded-xl px-3 py-2" />
        <input name="unitPrice" step="0.01" placeholder="Unit price AED" required className="border rounded-xl px-3 py-2" />
        <select name="paymentMode" className="border rounded-xl px-3 py-2">
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="on_account">On account</option>
        </select>
        <input name="customerName" placeholder="Customer / garage (optional)" className="border rounded-xl px-3 py-2 md:col-span-2" />
        <button className="md:col-span-2 bg-slate-900 text-white rounded-xl py-2">Issue invoice</button>
      </form>
    </div>
  );
}
