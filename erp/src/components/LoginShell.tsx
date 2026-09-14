import { BrandMark } from "./BrandMark";

export function LoginShell({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen grid lg:grid-cols-2" style={{ fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif" }}>
      <div className="hidden lg:flex flex-col min-h-screen bg-[#09131F] px-12 py-12">
        <div className="flex-1" />
        <div className="flex justify-center">
          <BrandMark />
        </div>
        <p className="flex-1 flex items-end justify-center text-[13px] text-[#8B93A0] text-center">
          Copyright © 2026 Inktek Solutions. All rights reserved.
        </p>
      </div>
      <div className="flex flex-col items-center justify-center min-h-screen px-6 py-10 bg-[#F4F5F9]">
        <div className="lg:hidden w-full max-w-[400px] mb-8 rounded-[24px] bg-[#09131F] px-6 py-10 text-center">
          <BrandMark compact />
        </div>
        <div className="w-full max-w-[400px]">{children}</div>
      </div>
    </div>
  );
}
