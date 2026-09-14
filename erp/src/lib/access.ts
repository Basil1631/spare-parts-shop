import { redirect } from "next/navigation";
import { shopContext, type ShopSession } from "./auth";
import { can, type NavKey } from "./roles";

export async function shopModule(username: string, key: NavKey): Promise<ShopSession> {
  const s = await shopContext(username);
  if (key !== "dashboard" && !can(s.role, key)) redirect(`/s/${username}?error=denied`);
  return s;
}

export function deny(username: string) {
  redirect(`/s/${username}?error=denied`);
}
