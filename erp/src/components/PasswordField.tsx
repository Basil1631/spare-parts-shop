"use client";

import { useState } from "react";

export function PasswordField({ name = "password" }: { name?: string }) {
  const [show, setShow] = useState(false);
  return (
    <div>
      <label htmlFor={name} className="block text-[13px] text-[#6B7280] mb-1.5">
        Password
      </label>
      <div className="relative">
        <input
          id={name}
          name={name}
          type={show ? "text" : "password"}
          required
          autoComplete="current-password"
          className="w-full h-11 bg-white border border-[#E5E7EB] rounded-full px-4 pr-11 text-[14px] text-[#101622] focus:outline-none focus:border-[#101622]"
        />
        <button
          type="button"
          onClick={() => setShow((v) => !v)}
          className="absolute inset-y-0 right-3 flex items-center text-[#9CA3AF] hover:text-[#101622]"
          aria-label={show ? "Hide password" : "Show password"}
        >
          {show ? (
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.7">
              <path strokeLinecap="round" strokeLinejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.93 12S5.68 18.75 12 18.75c1.69 0 3.26-.35 4.64-.97M9.88 9.88A3 3 0 0 1 14.12 14.12M6.23 6.23 17.77 17.77M9.88 9.88 6.23 6.23m7.89 7.89 3.65 3.65" />
            </svg>
          ) : (
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.7">
              <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
            </svg>
          )}
        </button>
      </div>
    </div>
  );
}
