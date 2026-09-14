import { prisma } from "@/lib/prisma";
import { saveJob, updateJobStatus } from "@/app/actions";
import { aed } from "@/lib/money";
import { Badge, Banner, field, FieldLabel, PageHeader, Panel, statusTone } from "@/components/ui";
import { SubmitButton } from "@/components/FormButtons";
import { shopModule } from "@/lib/access";

export default async function WorkshopPage({
  params,
  searchParams,
}: {
  params: Promise<{ shop: string }>;
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  const { shop: username } = await params;
  const q = await searchParams;
  const s = await shopModule(username, "workshop");
  const jobs = await prisma.jobCard.findMany({ where: { shopId: s.shopId }, orderBy: { createdAt: "desc" } });
  const save = saveJob.bind(null, username);
  const update = updateJobStatus.bind(null, username);
  return (
    <div className="space-y-6">
      <PageHeader title="Workshop job cards" hint="Open a job, move status as work progresses, then invoice parts from POS. Labour is recorded here for the technician, not auto-billed." />
      {q.ok ? <Banner kind="ok">Job card saved.</Banner> : null}
      {q.error ? <Banner kind="error">Could not update that job.</Banner> : null}
      <Panel className="p-4">
        <form action={save} className="grid md:grid-cols-3 gap-3">
          <div>
            <FieldLabel>Vehicle</FieldLabel>
            <input name="vehicle" className={field} />
          </div>
          <div>
            <FieldLabel>Complaint / job</FieldLabel>
            <input name="complaint" required className={field} />
          </div>
          <div>
            <FieldLabel>Labour AED</FieldLabel>
            <input name="labour" step="0.01" className={field} />
          </div>
          <div className="md:col-span-3">
            <SubmitButton>Open job</SubmitButton>
          </div>
        </form>
      </Panel>
      <div className="space-y-2">
        {jobs.map((j) => (
          <form key={j.id} action={update}>
            <Panel className="p-4 flex flex-wrap justify-between gap-3 items-center">
              <input type="hidden" name="id" value={j.id} />
              <div>
                <div className="font-medium text-[#101622]">{j.complaint}</div>
                <div className="text-sm text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap">
                  {j.vehicle || "No vehicle"}
                  <span className="tabular-nums">Labour {aed(j.labourFils)}</span>
                  <Badge tone={statusTone(j.status)}>{j.status.replaceAll("_", " ")}</Badge>
                </div>
              </div>
              <div className="flex gap-2 items-end">
                <label className="text-xs text-slate-600">
                  Status
                  <select name="status" defaultValue={j.status === "invoiced" ? "completed" : j.status} className={`${field} w-44 mt-1`}>
                    <option value="open">open</option>
                    <option value="in_progress">in progress</option>
                    <option value="parts_pending">parts pending</option>
                    <option value="completed">completed</option>
                    <option value="closed">closed</option>
                  </select>
                </label>
                <SubmitButton className="h-11 px-4 inline-flex items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:border-slate-300">
                  Update
                </SubmitButton>
              </div>
            </Panel>
          </form>
        ))}
        {jobs.length === 0 ? <p className="text-sm text-slate-500">No job cards yet.</p> : null}
      </div>
    </div>
  );
}
