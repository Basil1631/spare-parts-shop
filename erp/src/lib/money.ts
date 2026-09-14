export function toFils(aed: string | number | null | undefined) {
  if (aed === null || aed === undefined || aed === "") return 0;
  return Math.round(Number(String(aed).replace(/,/g, "")) * 100);
}

export function fromFils(fils: number) {
  return (fils / 100).toFixed(2);
}

export function aed(fils: number) {
  return `AED ${fromFils(fils)}`;
}
