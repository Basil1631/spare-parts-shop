"use client";

import { useMemo, useState } from "react";
import { aed, fromFils } from "@/lib/money";
import { SubmitButton } from "./FormButtons";
import { btnPrimary, field, Panel } from "./ui";

export type PosPart = {
  id: string;
  name: string;
  sku: string;
  oem: string | null;
  qty: number;
  priceFils: number;
};

type Line = { partId: string; qty: number; unitPrice: string };

export function PosCheckout({
  parts,
  vatPercent,
  customers,
  action,
  error,
}: {
  parts: PosPart[];
  vatPercent: number;
  customers: string[];
  action: (formData: FormData) => void | Promise<void>;
  error?: string;
}) {
  const [q, setQ] = useState("");
  const [lines, setLines] = useState<Line[]>([]);
  const [qty, setQty] = useState(1);
  const [unit, setUnit] = useState("");
  const [picked, setPicked] = useState<string>(parts[0]?.id || "");
  const [pay, setPay] = useState("cash");

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

  const selected = parts.find((p) => p.id === picked);
  const cart = lines
    .map((l) => {
      const p = parts.find((x) => x.id === l.partId);
      const unitFils = Math.round(Number(l.unitPrice || 0) * 100);
      return p ? { ...l, part: p, unitFils, lineFils: unitFils * l.qty } : null;
    })
    .filter(Boolean) as { partId: string; qty: number; unitPrice: string; part: PosPart; unitFils: number; lineFils: number }[];

  const needed = new Map<string, number>();
  for (const l of cart) needed.set(l.partId, (needed.get(l.partId) || 0) + l.qty);
  const oversell = [...needed.entries()].some(([id, n]) => n > (parts.find((p) => p.id === id)?.qty || 0));

  const subtotal = cart.reduce((a, l) => a + l.lineFils, 0);
  const vat = Math.round(subtotal * (vatPercent / 100));
  const total = subtotal + vat;

  function addLine() {
    if (!selected || selected.qty < 1 || qty < 1) return;
    const price = unit || fromFils(selected.priceFils);
    setLines((prev) => [...prev, { partId: selected.id, qty, unitPrice: price }].slice(0, 8));
  }

  const errorText =
    error === "stock"
      ? "Not enough stock for one of these parts."
      : error === "account"
        ? "On-account sales need a customer / garage name."
        : error === "1"
          ? "Add at least one part and a quantity of 1 or more."
          : null;

  return (
    <div className="grid xl:grid-cols-[1.4fr_0.9fr] gap-5">
      <Panel className="overflow-hidden">
        <div className="p-4 border-b border-slate-100">
          <label className="block text-xs font-medium text-slate-600 mb-1" htmlFor="pos-search">
            Search catalog
          </label>
          <input id="pos-search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Name, SKU or OEM" className={field} autoFocus />
        </div>
        <div className="max-h-[70vh] overflow-y-auto">
          {filtered.map((p) => {
            const on = p.id === picked;
            const low = p.qty <= 0;
            return (
              <button
                type="button"
                key={p.id}
                onClick={() => {
                  setPicked(p.id);
                  setUnit(fromFils(p.priceFils));
                }}
                disabled={low}
                className={`w-full text-left px-4 py-3.5 border-b border-slate-50 flex items-center justify-between gap-3 disabled:opacity-50 ${
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
                  <div className={`text-xs ${low ? "text-amber-500" : on ? "text-slate-300" : "text-slate-500"}`}>{p.qty} on hand</div>
                </div>
              </button>
            );
          })}
          {filtered.length === 0 ? (
            <div className="px-4 py-10 text-sm text-slate-500 text-center">
              {parts.length === 0 ? "No parts in the catalog yet. Ask the owner to add SKUs." : "No matching part."}
            </div>
          ) : null}
        </div>
      </Panel>
      <form action={action} className="space-y-4 xl:sticky xl:top-20 h-fit">
        <input type="hidden" name="lines" value={JSON.stringify(cart.map((l) => ({ partId: l.partId, qty: l.qty, unitPrice: l.unitPrice })))} />
        <Panel className="p-5 space-y-4">
          <div>
            <div className="text-xs uppercase tracking-wider text-slate-400">Add to bill</div>
            <div className="mt-1 font-semibold text-[#101622]">{selected?.name || "Choose a part"}</div>
            <div className="text-sm text-slate-500">{selected ? `${selected.sku} · ${selected.qty} available` : "Search on the left"}</div>
          </div>
          <div className="grid grid-cols-2 gap-3">
            <label className="text-sm text-slate-600 space-y-1">
              Qty
              <input type="number" min={1} max={selected?.qty || 1} value={qty} onChange={(e) => setQty(Number(e.target.value))} className={field} />
            </label>
            <label className="text-sm text-slate-600 space-y-1">
              Unit AED
              <input step="0.01" value={unit} onChange={(e) => setUnit(e.target.value)} className={field} />
            </label>
          </div>
          <button type="button" className="w-full h-11 rounded-full border border-slate-200 text-sm font-medium" onClick={addLine} disabled={!selected || selected.qty < 1}>
            Add line
          </button>
          <ul className="text-sm divide-y divide-slate-100">
            {cart.map((l, i) => (
              <li key={`${l.partId}-${i}`} className="py-2 flex justify-between gap-2">
                <span>
                  {l.part.name} × {l.qty}
                </span>
                <span className="flex items-center gap-2">
                  <span className="tabular-nums">{aed(l.lineFils)}</span>
                  <button type="button" className="text-xs text-slate-500" onClick={() => setLines((prev) => prev.filter((_, j) => j !== i))}>
                    Remove
                  </button>
                </span>
              </li>
            ))}
            {cart.length === 0 ? <li className="py-3 text-slate-500">No lines yet.</li> : null}
          </ul>
          <label className="block text-sm text-slate-600 space-y-1">
            Payment
            <select name="paymentMode" className={field} value={pay} onChange={(e) => setPay(e.target.value)}>
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="on_account">On account</option>
            </select>
          </label>
          <label className="block text-sm text-slate-600 space-y-1">
            Customer / garage {pay === "on_account" ? "(required)" : "(optional)"}
            <input name="customerName" list="pos-customers" required={pay === "on_account"} placeholder="Walk-in or garage name" className={field} />
            <datalist id="pos-customers">
              {customers.map((c) => (
                <option key={c} value={c} />
              ))}
            </datalist>
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
          {errorText ? <p className="text-sm text-red-600">{errorText}</p> : null}
          {oversell ? <p className="text-sm text-red-600">This bill asks for more than is on hand.</p> : null}
          <SubmitButton className={`${btnPrimary} w-full`} disabled={cart.length === 0 || oversell}>
            Issue invoice
          </SubmitButton>
        </Panel>
      </form>
    </div>
  );
}
