-- Unique document numbers per shop
CREATE UNIQUE INDEX IF NOT EXISTS "Invoice_shopId_number_key" ON "Invoice"("shopId", "number");
CREATE UNIQUE INDEX IF NOT EXISTS "PurchaseOrder_shopId_number_key" ON "PurchaseOrder"("shopId", "number");
CREATE INDEX IF NOT EXISTS "PurchaseOrder_shopId_status_idx" ON "PurchaseOrder"("shopId", "status");
CREATE INDEX IF NOT EXISTS "Invoice_shopId_createdAt_idx" ON "Invoice"("shopId", "createdAt");

-- Stock movements record warehouse
ALTER TABLE "StockMovement" ADD COLUMN IF NOT EXISTS "warehouseId" TEXT;

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'StockMovement_warehouseId_fkey') THEN
    ALTER TABLE "StockMovement" ADD CONSTRAINT "StockMovement_warehouseId_fkey" FOREIGN KEY ("warehouseId") REFERENCES "Warehouse"("id") ON DELETE SET NULL ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'StockMovement_userId_fkey') THEN
    ALTER TABLE "StockMovement" ADD CONSTRAINT "StockMovement_userId_fkey" FOREIGN KEY ("userId") REFERENCES "User"("id") ON DELETE SET NULL ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'PurchaseOrder_createdById_fkey') THEN
    ALTER TABLE "PurchaseOrder" ADD CONSTRAINT "PurchaseOrder_createdById_fkey" FOREIGN KEY ("createdById") REFERENCES "User"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'PurchaseOrder_receivedById_fkey') THEN
    ALTER TABLE "PurchaseOrder" ADD CONSTRAINT "PurchaseOrder_receivedById_fkey" FOREIGN KEY ("receivedById") REFERENCES "User"("id") ON DELETE SET NULL ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'PurchaseLine_partId_fkey') THEN
    ALTER TABLE "PurchaseLine" ADD CONSTRAINT "PurchaseLine_partId_fkey" FOREIGN KEY ("partId") REFERENCES "Part"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'Invoice_salespersonId_fkey') THEN
    ALTER TABLE "Invoice" ADD CONSTRAINT "Invoice_salespersonId_fkey" FOREIGN KEY ("salespersonId") REFERENCES "User"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'InvoiceLine_partId_fkey') THEN
    ALTER TABLE "InvoiceLine" ADD CONSTRAINT "InvoiceLine_partId_fkey" FOREIGN KEY ("partId") REFERENCES "Part"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'JobPart_partId_fkey') THEN
    ALTER TABLE "JobPart" ADD CONSTRAINT "JobPart_partId_fkey" FOREIGN KEY ("partId") REFERENCES "Part"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
  END IF;
END $$;
