import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveJob, updateJobStatus } from "@/app/actions";

export default async function WorkshopPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const jobs = await prisma.jobCard.findMany({ where: { shopId: s.shopId }, orderBy: { createdAt: "desc" } });
  const save = saveJob.bind(null, username);
  const update = updateJobStatus.bind(null, username);
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Workshop job cards</h1>
      <form action={save} className="bg-white rounded-2xl border p-4 grid md:grid-cols-3 gap-2">
        <input name="vehicle" placeholder="Vehicle" className="border rounded-xl px-3 py-2" />
        <input name="complaint" required placeholder="Complaint / job" className="border rounded-xl px-3 py-2" />
        <input name="labour" step="0.01" placeholder="Labour AED" className="border rounded-xl px-3 py-2" />
        <button className="bg-slate-900 text-white rounded-xl">Open job</button>
      </form>
      <div className="space-y-2">
        {jobs.map((j) => (
          <form key={j.id} action={update} className="bg-white rounded-2xl border p-4 flex flex-wrap justify-between gap-3">
            <input type="hidden" name="id" value={j.id} />
            <div>
              <div className="font-medium">{j.complaint}</div>
              <div className="text-sm text-slate-500">{j.vehicle || "—"} · {j.status}</div>
            </div>
            <select name="status" defaultValue={j.status} className="border rounded-xl px-3 py-2">
              <option value="open">open</option>
              <option value="in_progress">in progress</option>
              <option value="parts_pending">parts pending</option>
              <option value="completed">completed</option>
              <option value="invoiced">invoiced</option>
              <option value="closed">closed</option>
            </select>
            <button className="text-sm">Update</button>
          </form>
        ))}
      </div>
    </div>
  );
}
