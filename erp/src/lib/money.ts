export function toFils(aed: string | number | null | undefined) {
  if (aed === null || aed === undefined || aed === "") return 0;
  const n = Number(String(aed).replace(/,/g, ""));
  if (!Number.isFinite(n)) return 0;
  return Math.round(n * 100);
}

export function fromFils(fils: number) {
  return (fils / 100).toFixed(2);
}

export function aed(fils: number) {
  return `AED ${fromFils(fils)}`;
}
