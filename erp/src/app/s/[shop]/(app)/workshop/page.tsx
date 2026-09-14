import { prisma } from "@/lib/prisma";
import { shopContext } from "@/lib/auth";
import { saveJob, updateJobStatus } from "@/app/actions";
import { Badge, btnPrimary, btnQuiet, field, PageHeader, Panel, statusTone } from "@/components/ui";

export default async function WorkshopPage({ params }: { params: Promise<{ shop: string }> }) {
  const { shop: username } = await params;
  const s = await shopContext(username);
  const jobs = await prisma.jobCard.findMany({ where: { shopId: s.shopId }, orderBy: { createdAt: "desc" } });
  const save = saveJob.bind(null, username);
  const update = updateJobStatus.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Workshop job cards" hint="Open a job, move status as work progresses, then invoice from POS when parts are sold." />
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-3 gap-2">
          <input name="vehicle" placeholder="Vehicle" className={field} />
          <input name="complaint" required placeholder="Complaint / job" className={field} />
          <input name="labour" step="0.01" placeholder="Labour AED" className={field} />
          <button className={`${btnPrimary} md:col-span-3`}>Open job</button>
        </form>
      </Panel>
      <div className="space-y-2">
        {jobs.map((j) => (
          <form key={j.id} action={update}>
            <Panel className="p-4 flex flex-wrap justify-between gap-3 items-center">
              <input type="hidden" name="id" value={j.id} />
              <div>
                <div className="font-medium text-[#101622]">{j.complaint}</div>
                <div className="text-sm text-slate-500 mt-0.5 flex items-center gap-2">
                  {j.vehicle || "No vehicle"}
                  <Badge tone={statusTone(j.status)}>{j.status.replaceAll("_", " ")}</Badge>
                </div>
              </div>
              <div className="flex gap-2 items-center">
                <select name="status" defaultValue={j.status} className={`${field} w-44`}>
                  <option value="open">open</option>
                  <option value="in_progress">in progress</option>
                  <option value="parts_pending">parts pending</option>
                  <option value="completed">completed</option>
                  <option value="invoiced">invoiced</option>
                  <option value="closed">closed</option>
                </select>
                <button className={btnQuiet}>Update</button>
              </div>
            </Panel>
          </form>
        ))}
        {jobs.length === 0 ? <p className="text-sm text-slate-500">No job cards yet.</p> : null}
      </div>
    </div>
  );
}
