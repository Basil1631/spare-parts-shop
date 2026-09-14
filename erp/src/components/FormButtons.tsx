"use client";

import { useFormStatus } from "react-dom";
import { btnPrimary } from "./ui";

export function SubmitButton({
  children,
  className = btnPrimary,
  disabled,
}: {
  children: React.ReactNode;
  className?: string;
  disabled?: boolean;
}) {
  const { pending } = useFormStatus();
  return (
    <button className={className} disabled={pending || disabled}>
      {pending ? "Please wait…" : children}
    </button>
  );
}

export function ConfirmSubmit({
  message,
  children,
  className = btnPrimary,
}: {
  message: string;
  children: React.ReactNode;
  className?: string;
}) {
  const { pending } = useFormStatus();
  return (
    <button
      className={className}
      disabled={pending}
      onClick={(e) => {
        if (!confirm(message)) e.preventDefault();
      }}
    >
      {pending ? "Please wait…" : children}
    </button>
  );
}
