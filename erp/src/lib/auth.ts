import { SignJWT, jwtVerify, type JWTPayload } from "jose";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import bcrypt from "bcryptjs";
import type { Role } from "@prisma/client";
import { prisma } from "./prisma";
import { dubaiDayUtc } from "./time";

const COOKIE = "erp_session";

function secret() {
  const s = process.env.AUTH_SECRET || "dev-only-change-me";
  return new TextEncoder().encode(s);
}

export type ProviderSession = { kind: "provider"; userId: string; email: string; name: string };
export type ShopSession = {
  kind: "shop";
  userId: string;
  shopId: string;
  shopUsername: string;
  shopName: string;
  email: string;
  name: string;
  role: Role;
};
export type Session = ProviderSession | ShopSession;

export async function hashPassword(plain: string) {
  return bcrypt.hash(plain, 10);
}

export async function verifyPassword(plain: string, hash: string) {
  return bcrypt.compare(plain, hash);
}

export async function setSession(session: Session, days = 14) {
  const token = await new SignJWT(session as unknown as JWTPayload)
    .setProtectedHeader({ alg: "HS256" })
    .setExpirationTime(`${days}d`)
    .sign(secret());
  const jar = await cookies();
  jar.set(COOKIE, token, {
    httpOnly: true,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    path: "/",
    maxAge: 60 * 60 * 24 * days,
  });
}

export async function clearSession() {
  (await cookies()).delete(COOKIE);
}

export async function getSession(): Promise<Session | null> {
  const token = (await cookies()).get(COOKIE)?.value;
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, secret());
    if (payload.kind === "provider" || payload.kind === "shop") return payload as unknown as Session;
    return null;
  } catch {
    return null;
  }
}

export async function requireProvider(): Promise<ProviderSession> {
  const s = await getSession();
  if (!s || s.kind !== "provider") throw new Error("UNAUTHORIZED_PROVIDER");
  return s;
}

export async function requireShop(username: string): Promise<ShopSession> {
  const s = await getSession();
  if (!s || s.kind !== "shop" || s.shopUsername !== username) throw new Error("UNAUTHORIZED_SHOP");
  const shop = await prisma.shop.findUnique({ where: { username } });
  if (!shop || shop.status === "suspended") throw new Error("SHOP_SUSPENDED");
  return s;
}

export async function shopContext(username: string): Promise<ShopSession> {
  try {
    return await requireShop(username);
  } catch {
    redirect(`/s/${username}/login`);
  }
}

export async function markAttendance(userId: string, shopId: string) {
  const day = dubaiDayUtc();
  await prisma.attendance.upsert({
    where: { userId_workedOn: { userId, workedOn: day } },
    update: {},
    create: { shopId, userId, workedOn: day },
  });
  await prisma.user.update({ where: { id: userId }, data: { lastLoginAt: new Date() } });
}

export async function nextNumber(shopId: string, name: string, prefix: string) {
  const n = await prisma.$transaction(async (tx) => {
    const row = await tx.sequence.upsert({
      where: { shopId_name: { shopId, name } },
      update: {},
      create: { shopId, name, next: 1 },
    });
    await tx.sequence.update({
      where: { shopId_name: { shopId, name } },
      data: { next: row.next + 1 },
    });
    return row.next;
  });
  return `${prefix}-${String(n).padStart(5, "0")}`;
}
