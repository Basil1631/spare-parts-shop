import type { Role } from "@prisma/client";

export const ROLE_LABEL: Record<Role, string> = {
  owner: "Owner / GM",
  tenant_admin: "Tenant admin",
  branch_manager: "Branch manager",
  purchase: "Purchase officer",
  cashier: "Sales / cashier",
  warehouse: "Warehouse / godown",
  accountant: "Accountant",
  hr: "HR / payroll",
  technician: "Technician",
};

export type NavKey =
  | "dashboard"
  | "pos"
  | "parts"
  | "stock"
  | "godown"
  | "purchases"
  | "bills"
  | "customers"
  | "invoices"
  | "staff"
  | "hr"
  | "workshop"
  | "reports"
  | "settings";

const ALL: Role[] = [
  "owner",
  "tenant_admin",
  "branch_manager",
  "purchase",
  "cashier",
  "warehouse",
  "accountant",
  "hr",
  "technician",
];

export const NAV: { href: string; label: string; key: NavKey; roles: Role[] }[] = [
  { href: "", label: "Dashboard", key: "dashboard", roles: ALL },
  { href: "/pos", label: "POS / New bill", key: "pos", roles: ["owner", "branch_manager", "cashier"] },
  { href: "/parts", label: "Catalog", key: "parts", roles: ["owner", "tenant_admin", "branch_manager", "purchase", "warehouse"] },
  { href: "/stock", label: "Stock", key: "stock", roles: ["owner", "branch_manager", "purchase", "warehouse"] },
  { href: "/godown", label: "Incoming stock", key: "godown", roles: ["owner", "branch_manager", "warehouse"] },
  { href: "/purchases", label: "Purchases", key: "purchases", roles: ["owner", "branch_manager", "purchase"] },
  { href: "/bills", label: "Vendor bills", key: "bills", roles: ["owner", "branch_manager", "accountant"] },
  { href: "/customers", label: "Customers", key: "customers", roles: ["owner", "branch_manager", "cashier", "accountant"] },
  { href: "/invoices", label: "Invoices", key: "invoices", roles: ["owner", "branch_manager", "cashier", "accountant"] },
  { href: "/staff", label: "Staff", key: "staff", roles: ["owner", "tenant_admin", "branch_manager"] },
  { href: "/hr", label: "HR / payroll", key: "hr", roles: ["owner", "branch_manager", "hr"] },
  { href: "/workshop", label: "Workshop", key: "workshop", roles: ["owner", "branch_manager", "technician"] },
  { href: "/reports", label: "Reports", key: "reports", roles: ["owner", "branch_manager", "accountant", "purchase", "hr"] },
  { href: "/settings", label: "Shop settings", key: "settings", roles: ["owner", "tenant_admin"] },
];

export function can(role: Role, key: NavKey) {
  return NAV.find((n) => n.key === key)?.roles.includes(role) ?? false;
}

export function shopPath(username: string, href = "") {
  return `/s/${username}${href}`;
}
