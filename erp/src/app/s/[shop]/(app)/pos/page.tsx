import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { createPosSale } from "@/app/actions";
import { PosCheckout } from "@/components/PosCheckout";
import { PageHeader } from "@/components/ui";

export default async function PosPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopContext(username);
  const [parts, shop] = await Promise.all([
    prisma.part.findMany({
      where: { shopId: s.shopId, active: true },
      include: { stocks: true },
      orderBy: { name: "asc" },
    }),
    prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } }),
  ]);
  const sale = createPosSale.bind(null, username);
  return (
    <div>
      <PageHeader title="POS / New bill" hint="Search a part, take payment, issue a VAT invoice. Stock deducts immediately." />
      <PosCheckout
        action={sale}
        vatPercent={shop.vatPercent}
        error={q.error === "1"}
        parts={parts.map((p) => ({
          id: p.id,
          name: p.name,
          sku: p.sku,
          oem: p.oemNumber,
          qty: p.stocks.reduce((a, b) => a + b.qtyOnHand, 0),
          priceFils: p.salePriceFils,
        }))}
      />
    </div>
  );
}
