export function BrandMark({ compact = false }: { compact?: boolean }) {
  const gid = compact ? "pzWordMobile" : "pzWordDesktop";
  return (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 140" className={compact ? "w-full h-auto" : "w-[min(420px,72%)] h-auto"} role="img" aria-label="Partszone">
      <defs>
        <linearGradient id={gid} x1="70" y1="28" x2="450" y2="88" gradientUnits="userSpaceOnUse">
          <stop stopColor="#D7F08A" />
          <stop offset="0.45" stopColor="#B4E068" />
          <stop offset="1" stopColor="#86C94A" />
        </linearGradient>
      </defs>
      <text x="260" y="72" textAnchor="middle" fill={`url(#${gid})`} fontFamily="Poppins, Inter, sans-serif" fontSize="64" fontWeight="600">
        Partszone
      </text>
      <text x="260" y="108" textAnchor="middle" fontFamily="Inter, sans-serif" fontSize="16">
        <tspan fill="#8B7CF6">Powered By </tspan>
        <tspan fill="#9AA3B0" fontWeight="500">
          Inktek Solutions
        </tspan>
      </text>
    </svg>
  );
}
