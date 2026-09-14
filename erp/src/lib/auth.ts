import { SignJWT, jwtVerify, type JWTPayload } from "jose";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import bcrypt from "bcryptjs";
import { randomUUID } from "node:crypto";
import type { Prisma, Role } from "@prisma/client";
import { prisma } from "./prisma";
import { dubaiDayUtc } from "./time";

const COOKIE = "erp_session";

function secret() {
  const s = process.env.AUTH_SECRET;
  if (!s) {
    if (process.env.NODE_ENV === "production") throw new Error("AUTH_SECRET is required");
    return new TextEncoder().encode("dev-only-change-me");
  }
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
export type DbClient = Prisma.TransactionClient | typeof prisma;

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
  const [shop, user] = await Promise.all([
    prisma.shop.findUnique({ where: { username } }),
    prisma.user.findUnique({ where: { id: s.userId } }),
  ]);
  if (!shop || shop.status === "suspended") throw new Error("SHOP_SUSPENDED");
  if (!user || !user.active || user.shopId !== shop.id) throw new Error("UNAUTHORIZED_SHOP");
  return {
    kind: "shop",
    userId: user.id,
    shopId: shop.id,
    shopUsername: shop.username,
    shopName: shop.name,
    email: user.email,
    name: user.name,
    role: user.role,
  };
}

export async function shopContext(username: string): Promise<ShopSession> {
  try {
    return await requireShop(username);
  } catch (e) {
    const code = e instanceof Error ? e.message : "";
    if (code === "SHOP_SUSPENDED") redirect(`/s/${username}/login?error=shop`);
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

export async function nextNumber(shopId: string, name: string, prefix: string, db: DbClient = prisma) {
  await db.$executeRaw`
    INSERT INTO "Sequence" (id, "shopId", name, next)
    VALUES (${randomUUID()}, ${shopId}, ${name}, 1)
    ON CONFLICT ("shopId", name) DO NOTHING
  `;
  const rows = await db.$queryRaw<{ next: number }[]>`
    UPDATE "Sequence" SET next = next + 1
    WHERE "shopId" = ${shopId} AND name = ${name}
    RETURNING next - 1 AS next
  `;
  return `${prefix}-${String(rows[0].next).padStart(5, "0")}`;
}
