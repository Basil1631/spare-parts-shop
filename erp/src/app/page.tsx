import { redirect } from "next/navigation";
import { LoginShell } from "@/components/LoginShell";

export default function Home() {
  return (
    <LoginShell>
      <div className="bg-white rounded-[24px] px-8 pt-8 pb-8 shadow-[0_18px_50px_rgba(16,22,34,0.08)]">
        <h2 className="text-[20px] font-semibold text-[#101622] tracking-tight">Sign in</h2>
        <p className="mt-1 text-[13px] text-[#ABACB0] leading-snug">Enter your shop username to open that shop’s login.</p>
        <form
          className="mt-6 space-y-4"
          action={async (fd) => {
            "use server";
            const shop = String(fd.get("shop") || "")
              .toLowerCase()
              .trim();
            if (shop) redirect(`/s/${shop}/login`);
          }}
        >
          <div>
            <label className="block text-[13px] text-[#6B7280] mb-1.5">Shop username</label>
            <input
              name="shop"
              required
              placeholder="e.g. alain"
              className="w-full h-11 bg-white border border-[#E5E7EB] rounded-full px-4 text-[14px] text-[#101622] focus:outline-none focus:border-[#101622]"
            />
          </div>
          <button className="w-full h-11 bg-[#101622] text-white rounded-full text-[14px] font-medium">Continue</button>
        </form>
        <p className="mt-5 text-center text-[13px] text-[#6B7280]">
          Inktek operator?{" "}
          <a href="/provider/login" className="text-[#101622] font-medium">
            Super admin login
          </a>
        </p>
      </div>
    </LoginShell>
  );
}
