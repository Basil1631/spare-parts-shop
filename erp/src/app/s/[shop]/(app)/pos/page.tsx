import { prisma } from "@/lib/prisma";
import { createPosSale } from "@/app/actions";
import { PosCheckout } from "@/components/PosCheckout";
import { PageHeader } from "@/components/ui";
import { shopModule } from "@/lib/access";

export default async function PosPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "pos");
  const [parts, shop, customers] = await Promise.all([
    prisma.part.findMany({
      where: { shopId: s.shopId, active: true },
      include: { stocks: true },
      orderBy: { name: "asc" },
    }),
    prisma.shop.findUniqueOrThrow({ where: { id: s.shopId } }),
    prisma.customer.findMany({ where: { shopId: s.shopId }, orderBy: { name: "asc" }, select: { name: true } }),
  ]);
  const sale = createPosSale.bind(null, username);
  return (
    <div>
      <PageHeader title="POS / New bill" hint="Add one or more parts, take payment, issue a VAT invoice. Stock deducts immediately." />
      <PosCheckout
        action={sale}
        vatPercent={shop.vatPercent}
        error={q.error}
        customers={customers.map((c) => c.name)}
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
