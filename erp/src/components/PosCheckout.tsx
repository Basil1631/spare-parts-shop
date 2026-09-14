"use client";

import { useMemo, useState } from "react";
import { aed, fromFils } from "@/lib/money";
import { btnPrimary, field, Panel } from "./ui";

export type PosPart = {
  id: string;
  name: string;
  sku: string;
  oem: string | null;
  qty: number;
  priceFils: number;
};

export function PosCheckout({
  parts,
  vatPercent,
  action,
  error,
}: {
  parts: PosPart[];
  vatPercent: number;
  action: (formData: FormData) => void | Promise<void>;
  error?: boolean;
}) {
  const [q, setQ] = useState("");
  const [partId, setPartId] = useState(parts[0]?.id || "");
  const [qty, setQty] = useState(1);
  const [unit, setUnit] = useState(fromFils(parts[0]?.priceFils || 0));

  const filtered = useMemo(() => {
    const s = q.trim().toLowerCase();
    if (!s) return parts;
    return parts.filter(
      (p) =>
        p.name.toLowerCase().includes(s) ||
        p.sku.toLowerCase().includes(s) ||
        (p.oem || "").toLowerCase().includes(s),
    );
  }, [parts, q]);

  const selected = parts.find((p) => p.id === partId);
  const unitFils = Math.round(Number(unit || 0) * 100);
  const subtotal = unitFils * Math.max(qty, 0);
  const vat = Math.round(subtotal * (vatPercent / 100));
  const total = subtotal + vat;

  function pick(p: PosPart) {
    setPartId(p.id);
    setUnit(fromFils(p.priceFils));
    if (qty < 1) setQty(1);
  }

  return (
    <div className="grid xl:grid-cols-[1.4fr_0.9fr] gap-5">
      <Panel className="overflow-hidden">
        <div className="p-4 border-b border-slate-100">
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Search name, SKU or OEM"
            className={field}
            autoFocus
          />
        </div>
        <div className="max-h-[70vh] overflow-y-auto">
          {filtered.map((p) => {
            const on = p.id === partId;
            const low = p.qty <= 0;
            return (
              <button
                type="button"
                key={p.id}
                onClick={() => pick(p)}
                className={`w-full text-left px-4 py-3.5 border-b border-slate-50 flex items-center justify-between gap-3 ${
                  on ? "bg-[#09131F] text-white" : "hover:bg-slate-50"
                }`}
              >
                <div className="min-w-0">
                  <div className="font-medium truncate">{p.name}</div>
                  <div className={`text-xs mt-0.5 ${on ? "text-slate-300" : "text-slate-500"}`}>
                    {p.sku}
                    {p.oem ? ` · OEM ${p.oem}` : ""}
                  </div>
                </div>
                <div className="text-right shrink-0">
                  <div className="tabular-nums font-medium">{aed(p.priceFils)}</div>
                  <div className={`text-xs ${low ? "text-amber-500" : on ? "text-slate-300" : "text-slate-500"}`}>
                    {p.qty} on hand
                  </div>
                </div>
              </button>
            );
          })}
          {filtered.length === 0 ? <div className="px-4 py-10 text-sm text-slate-500 text-center">No matching part.</div> : null}
        </div>
      </Panel>
      <form action={action} className="space-y-4">
        <Panel className="p-5 space-y-4">
          <div>
            <div className="text-xs uppercase tracking-wider text-slate-400">Selected part</div>
            <div className="mt-1 font-semibold text-[#101622]">{selected?.name || "Choose a part"}</div>
            <div className="text-sm text-slate-500">{selected ? `${selected.sku} · ${selected.qty} available` : "Search on the left"}</div>
          </div>
          <input type="hidden" name="partId" value={partId} />
          <div className="grid grid-cols-2 gap-3">
            <label className="text-sm text-slate-600 space-y-1">
              Qty
              <input
                name="qty"
                type="number"
                min={1}
                value={qty}
                onChange={(e) => setQty(Number(e.target.value))}
                className={field}
                required
              />
            </label>
            <label className="text-sm text-slate-600 space-y-1">
              Unit AED
              <input
                name="unitPrice"
                step="0.01"
                value={unit}
                onChange={(e) => setUnit(e.target.value)}
                className={field}
                required
              />
            </label>
          </div>
          <label className="block text-sm text-slate-600 space-y-1">
            Payment
            <select name="paymentMode" className={field} defaultValue="cash">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="on_account">On account</option>
            </select>
          </label>
          <label className="block text-sm text-slate-600 space-y-1">
            Customer / garage
            <input name="customerName" placeholder="Optional for cash walk-in" className={field} />
          </label>
          <dl className="text-sm space-y-1.5 pt-2 border-t border-slate-100">
            <div className="flex justify-between">
              <dt className="text-slate-500">Subtotal</dt>
              <dd className="tabular-nums">{aed(subtotal)}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-slate-500">VAT {vatPercent}%</dt>
              <dd className="tabular-nums">{aed(vat)}</dd>
            </div>
            <div className="flex justify-between text-base font-semibold text-[#101622]">
              <dt>Total</dt>
              <dd className="tabular-nums">{aed(total)}</dd>
            </div>
          </dl>
          {error ? <p className="text-sm text-red-600">Choose a part and a quantity of 1 or more.</p> : null}
          <button className={`${btnPrimary} w-full`} disabled={!partId}>
            Issue invoice
          </button>
        </Panel>
      </form>
    </div>
  );
}
