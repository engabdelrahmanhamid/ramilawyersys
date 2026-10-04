// The unified Activity adapter.
//
// The old system models five separate concepts, each with its own endpoint,
// screen and fields: موعد (appointment), جلسة (session), إجراء (procedure, a
// session with type=2), مهمة (task) and تواصل (contact). From the user's point
// of view these are all "something that happened, or is due, on a date, for a
// client". This module normalises all five into one `Activity` shape for
// *reading* (one timeline, one filter bar, one card), while remembering where
// each row came from so edits can be written back to its original endpoint with
// its original field names. No back-end logic changes — we only read the same
// `/{entity}/get` lists the old app reads and merge them on the client.

import { get, type Page } from './api';
import type { Appointment, Contact, Session, Task } from './types';

export type ActivityKind = 'appointment' | 'session' | 'procedure' | 'task' | 'contact';

export type StatusTone = 'open' | 'done' | 'overdue' | 'neutral';

export type ActivityParty = { id: number; name: string; phone?: string | null } | null;
export type ActivityCase = { id: number; name: string; number?: string | null } | null;

export type Activity = {
  /** Stable composite id, e.g. "task:1203" — unique across the merged list. */
  key: string;
  kind: ActivityKind;
  rawId: number;
  /** Which `/{base}/get` + `/{base}/single` this came from (for detail / write-back). */
  endpointBase: string;
  title: string;
  /** Primary gregorian date (YYYY-MM-DD) used for sorting/grouping; null if undated. */
  date: string | null;
  time: string | null;
  client: ActivityParty;
  case: ActivityCase;
  /** The responsible admin/user. */
  assignee: ActivityParty;
  status: { label: string; tone: StatusTone };
  note: string | null;
  /** The untouched source row, kept for the detail view and write-back. */
  raw: unknown;
};

export const KIND_LABEL: Record<ActivityKind, string> = {
  appointment: 'موعد',
  session: 'جلسة',
  procedure: 'إجراء',
  task: 'مهمة',
  contact: 'تواصل',
};

// Contact.method is a small enum on the back-end; these are the labels the old UI shows.
const CONTACT_METHOD: Record<number, string> = {
  1: 'هاتف',
  2: 'واتساب',
  3: 'بريد إلكتروني',
  4: 'زيارة',
};

function named(value: { id: number; name: string } | null | undefined): string | null {
  return value && value.name ? value.name : null;
}

function party(value: { id: number; name: string; phone?: string | null } | null | undefined): ActivityParty {
  return value ? { id: value.id, name: value.name, phone: value.phone ?? null } : null;
}

// The status objects across entities share { id, name, type }. `type` (or a numeric
// status) hints whether the row is open / done / overdue, which we collapse into a tone
// so one colour system covers every kind.
function toneFromStatus(raw: { id?: number; name?: string; type?: number } | number | null | undefined): StatusTone {
  if (raw == null) return 'neutral';
  const t = typeof raw === 'number' ? raw : raw.type;
  switch (t) {
    case 1:
      return 'open';
    case 2:
      return 'done';
    case 3:
      return 'overdue';
    default:
      return 'neutral';
  }
}

// ---- per-kind adapters: raw list row -> Activity ---------------------------

function fromAppointment(row: Appointment): Activity {
  const date = row.gregorian_date ?? row.date ?? null;
  return {
    key: `appointment:${row.id}`,
    kind: 'appointment',
    rawId: row.id,
    endpointBase: 'appointment',
    title: row.destination || row.note || 'موعد',
    date,
    time: row.time ?? null,
    client: party(row.client),
    case: null,
    assignee: party(row.admin),
    status: {
      label: typeof row.status === 'object' ? named(row.status) || '—' : '—',
      tone: toneFromStatus(row.status),
    },
    note: row.note ?? row.destination ?? null,
    raw: row,
  };
}

function fromSession(row: Session): Activity {
  const isProcedure = row.type === 2;
  return {
    key: `session:${row.id}`,
    kind: isProcedure ? 'procedure' : 'session',
    rawId: row.id,
    endpointBase: 'session',
    title: row.case?.name || row.destination || (isProcedure ? 'إجراء' : 'جلسة'),
    date: row.gregorian_date ?? null,
    time: row.session_time ?? null,
    client: party(row.client),
    case: row.case ? { id: row.case.id, name: row.case.name, number: row.case.number ?? null } : null,
    assignee: party(row.admin),
    status: { label: named(row.status) || '—', tone: toneFromStatus(row.status) },
    note: row.session_requirements ?? null,
    raw: row,
  };
}

function fromTask(row: Task): Activity {
  const tone: StatusTone =
    row.status === 2 ? 'done' : row.is_overdue || row.status === 3 ? 'overdue' : 'open';
  return {
    key: `task:${row.id}`,
    kind: 'task',
    rawId: row.id,
    endpointBase: 'task',
    title: row.task_type?.name || row.description || 'مهمة',
    date: row.end_date ?? null,
    time: null,
    client: party(row.client_id),
    case: null,
    assignee: party(row.admin || row.created_by),
    status: {
      label: tone === 'done' ? 'منجزة' : tone === 'overdue' ? 'متأخرة' : 'مفتوحة',
      tone,
    },
    note: row.description ?? null,
    raw: row,
  };
}

function fromContact(row: Contact): Activity {
  return {
    key: `contact:${row.id}`,
    kind: 'contact',
    rawId: row.id,
    endpointBase: 'contact',
    title: row.contact_reason?.name || row.type?.name || 'تواصل',
    date: row.date ?? null,
    time: null,
    client: party(row.client),
    case: null,
    assignee: party(row.admin),
    status: {
      label: row.method != null ? CONTACT_METHOD[row.method] ?? 'تواصل' : 'تواصل',
      tone: 'neutral',
    },
    note: row.description ?? null,
    raw: row,
  };
}

// ---- reading: fetch the selected kinds and merge into one timeline ---------

export type ActivityFilters = {
  kinds?: ActivityKind[];
  client_id?: number;
  admin_id?: number;
  branch_id?: number;
  date_from?: string;
  date_to?: string;
  search_text?: string;
  limit?: number;
};

const ALL_KINDS: ActivityKind[] = ['appointment', 'session', 'procedure', 'task', 'contact'];

// session + procedure are the same endpoint, so we fetch `session` once and split by type.
type Source = { base: string; map: (row: any) => Activity };
const SOURCES: Record<Exclude<ActivityKind, 'procedure'>, Source> = {
  appointment: { base: 'appointment', map: fromAppointment },
  session: { base: 'session', map: fromSession },
  task: { base: 'task', map: fromTask },
  contact: { base: 'contact', map: fromContact },
};

/**
 * Fetch the chosen kinds in parallel, normalise each, and return one list sorted
 * newest-first. A failing source doesn't sink the whole timeline — it just
 * contributes nothing and is reported in `errors`.
 */
export async function fetchActivities(
  filters: ActivityFilters = {},
): Promise<{ items: Activity[]; errors: { kind: ActivityKind; message: string }[] }> {
  const kinds = filters.kinds?.length ? filters.kinds : ALL_KINDS;
  const wantSession = kinds.includes('session') || kinds.includes('procedure');
  const bases = new Set<Exclude<ActivityKind, 'procedure'>>();
  if (kinds.includes('appointment')) bases.add('appointment');
  if (wantSession) bases.add('session');
  if (kinds.includes('task')) bases.add('task');
  if (kinds.includes('contact')) bases.add('contact');

  const shared = {
    page: 1,
    limit: filters.limit ?? 50,
    search_text: filters.search_text ?? '',
    client_id: filters.client_id,
    admin_id: filters.admin_id,
    branch_id: filters.branch_id,
    date_from: filters.date_from,
    date_to: filters.date_to,
  };

  const errors: { kind: ActivityKind; message: string }[] = [];
  const results = await Promise.all(
    [...bases].map(async (base) => {
      const source = SOURCES[base];
      try {
        const page = await get<Page<any>>(`${source.base}/get`, shared);
        return (page.data ?? []).map(source.map);
      } catch (e) {
        errors.push({ kind: base, message: e instanceof Error ? e.message : 'تعذر التحميل' });
        return [] as Activity[];
      }
    }),
  );

  let items = results.flat();

  // session rows carry both جلسة and إجراء; drop whichever the caller didn't ask for.
  if (wantSession && !(kinds.includes('session') && kinds.includes('procedure'))) {
    const keep: ActivityKind = kinds.includes('procedure') ? 'procedure' : 'session';
    items = items.filter((a) => a.kind !== 'session' && a.kind !== 'procedure' ? true : a.kind === keep);
  }

  items.sort((a, b) => (b.date ?? '').localeCompare(a.date ?? ''));
  return { items, errors };
}

// ---- write-back: map a normalised edit to each endpoint's own fields --------
//
// The adapter never invents a shared write model. Each kind keeps its original
// create/edit fields (confirmed from the old app's forms), so a change made in
// the unified UI is translated back to exactly the payload that kind's endpoint
// expects. The concrete POST path (create vs update) is wired in the mutation
// layer once confirmed against the back-end; here we only own the field mapping.

export type ActivityDraft = {
  client_id?: number;
  admin_id?: number;
  branch_id?: number;
  date?: string;
  note?: string;
  status_id?: number;
  type_id?: number;
};

export const WRITE_FIELDS: Record<ActivityKind, (draft: ActivityDraft) => Record<string, unknown>> = {
  appointment: (d) => ({
    client_id: d.client_id,
    admin_id: d.admin_id,
    branch_id: d.branch_id,
    date: d.date,
    status: d.status_id,
    destination: d.note,
  }),
  session: (d) => ({
    client_id: d.client_id,
    type: 1,
    status_id: d.status_id,
    date: d.date,
    session_requirements: d.note,
  }),
  procedure: (d) => ({
    client_id: d.client_id,
    type: 2,
    status_id: d.status_id,
    date: d.date,
    session_requirements: d.note,
  }),
  task: (d) => ({
    client_id: d.client_id,
    task_type_id: d.type_id,
    end_date: d.date,
    description: d.note,
  }),
  contact: (d) => ({
    client_id: d.client_id,
    contact_reason_id: d.type_id,
    date: d.date,
    description: d.note,
  }),
};
