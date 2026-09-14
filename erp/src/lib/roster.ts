import { prisma } from "./prisma";
import { dubaiDayUtc } from "./time";
import { ROLE_LABEL } from "./roles";
import type { Role } from "@prisma/client";

export async function shopRoster(shopId: string) {
  const day = dubaiDayUtc();
  const users = await prisma.user.findMany({
    where: { shopId, active: true },
    orderBy: { name: "asc" },
  });
  const logs = await prisma.attendance.findMany({
    where: { shopId, workedOn: day },
  });
  const byUser = new Map(logs.map((l) => [l.userId, l]));
  const present = users
    .filter((u) => byUser.has(u.id))
    .map((u) => ({
      id: u.id,
      name: u.name,
      role: ROLE_LABEL[u.role as Role],
      loginAt: byUser.get(u.id)!.loginAt,
    }));
  const leave = users
    .filter((u) => !byUser.has(u.id))
    .map((u) => ({ id: u.id, name: u.name, role: ROLE_LABEL[u.role as Role] }));
  return { present, leave, day };
}
