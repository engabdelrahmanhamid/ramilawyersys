import {
  Alert, Avatar, Box, Card, CardContent, CardHeader, Chip, Link, List, ListItem, ListItemText,
  Skeleton, Stack, ToggleButton, ToggleButtonGroup, Typography,
} from '@mui/material';
import { useQuery } from '@tanstack/react-query';
import { useState, type ReactNode } from 'react';
import dayjs from 'dayjs';
import { get, type Page } from '../api';
import { useAuth } from '../auth';
import { OLD_APP } from '../config';
import { daysLate, formatDate, formatMoney, isTaskOverdue, toNumber, today } from '../format';
import type { Appointment, CaseListItem, Receipt, Session, Task } from '../types';

type Scope = 'mine' | 'all';

// Details still open in the old interface until those screens are rebuilt.
const oldLink = (path: string) => `${OLD_APP}${path}`;
const hhmm = (t?: string | null) => (t ? t.slice(0, 5) : '');

// The lawyer's personal workspace: everything assigned to the signed-in user, read from the
// same lists the old app uses (scoped by admin_id), merged into one daily cockpit.
export function WorkspacePage() {
  const { admin } = useAuth();
  const isSuper = admin?.super === 1;
  // A regular lawyer only ever sees their own work; a super-admin can widen to the whole office.
  const [scope, setScope] = useState<Scope>('mine');
  const adminId = scope === 'mine' ? admin?.id : undefined;
  const day = today();
  const tomorrow = dayjs().add(1, 'day').format('YYYY-MM-DD');
  const horizon = dayjs().add(30, 'day').format('YYYY-MM-DD');

  const sessionsToday = useQuery({
    queryKey: ['ws-sessions-today', day, adminId],
    queryFn: () => get<Page<Session>>('session/get', { date_from: day, date_to: day, admin_id: adminId, limit: 200 }),
  });
  const apptsToday = useQuery({
    queryKey: ['ws-appts-today', day, adminId],
    queryFn: () => get<Page<Appointment>>('appointment/get', { date_from: day, date_to: day, admin_id: adminId, limit: 200 }),
  });
  const upcoming = useQuery({
    queryKey: ['ws-upcoming', tomorrow, adminId],
    queryFn: () => get<Page<Session>>('session/get', { date_from: tomorrow, date_to: horizon, admin_id: adminId, limit: 50 }),
  });
  const tasks = useQuery({
    queryKey: ['ws-tasks', adminId],
    queryFn: () => get<Page<Task>>('task/get', { status_id: 1, admin_id: adminId, limit: 500 }),
  });
  const caseReceipts = useQuery({
    queryKey: ['ws-case-receipts', day, adminId],
    queryFn: () => get<Page<Receipt>>('case_receipt/get', { payment_status: 0, date_to: day, admin_id: adminId, limit: 500 }),
  });
  const serviceReceipts = useQuery({
    queryKey: ['ws-service-receipts', day, adminId],
    queryFn: () => get<Page<Receipt>>('service_receipt/get', { payment_status: 0, date_to: day, admin_id: adminId, limit: 500 }),
  });
  const cases = useQuery({
    queryKey: ['ws-cases', adminId],
    queryFn: () => get<Page<CaseListItem>>('case/get', { admin_id: adminId, limit: 8 }),
  });

  // "My day": today's sessions + appointments on one timeline, ordered by time.
  type DayItem = { id: string; time: string; title: string; sub: string; tag?: string; href?: string };
  const dayItems: DayItem[] = [
    ...(sessionsToday.data?.data ?? []).map((s) => ({
      id: `s${s.id}`,
      time: hhmm(s.session_time),
      title: s.case?.name ?? s.client?.name ?? `جلسة #${s.id}`,
      sub: [s.type === 2 ? 'إجراء' : 'جلسة', s.destination, s.client?.name].filter(Boolean).join(' · '),
      tag: s.status?.name ?? undefined,
      href: oldLink(`/session/${s.id}`),
    })),
    ...(apptsToday.data?.data ?? []).map((a) => ({
      id: `a${a.id}`,
      time: hhmm(a.time),
      title: a.destination || a.client?.name || `موعد #${a.id}`,
      sub: ['موعد', a.client?.name].filter(Boolean).join(' · '),
      tag: typeof a.status === 'object' && a.status ? a.status.name ?? undefined : undefined,
    })),
  ].sort((x, y) => x.time.localeCompare(y.time));

  const upcomingItems = [...(upcoming.data?.data ?? [])]
    .sort((a, b) => (a.gregorian_date ?? '').localeCompare(b.gregorian_date ?? ''));
  const overdueTasks = (tasks.data?.data ?? []).filter(isTaskOverdue).sort((a, b) => daysLate(b.end_date) - daysLate(a.end_date));
  const openTasks = tasks.data?.data?.length ?? 0;
  const dueReceipts = [
    ...(caseReceipts.data?.data ?? []).map((r) => ({ ...r, kind: 'case' as const })),
    ...(serviceReceipts.data?.data ?? []).map((r) => ({ ...r, kind: 'service' as const })),
  ].sort((a, b) => a.date.localeCompare(b.date));
  const dueTotal = dueReceipts.reduce((sum, r) => sum + toNumber(r.unpaid_amount ?? r.total_amount), 0);
  const myCases = cases.data?.data ?? [];

  const errors = [sessionsToday, apptsToday, upcoming, tasks, caseReceipts, serviceReceipts, cases]
    .map((q) => q.error).filter(Boolean) as Error[];

  const initial = (admin?.name ?? '؟').trim().charAt(0);

  return (
    <Stack spacing={3}>
      {/* identity banner */}
      <Card sx={{ overflow: 'hidden', border: 0 }}>
        <Box sx={{
          p: { xs: 2, sm: 2.5 }, display: 'flex', alignItems: 'center', gap: 2, color: 'common.white',
          background: 'linear-gradient(135deg, #1f4e79, #15385a)',
        }}>
          <Avatar sx={{ bgcolor: 'rgba(255,255,255,.18)', width: 52, height: 52, fontWeight: 700 }}>{initial}</Avatar>
          <Box flexGrow={1} minWidth={0}>
            <Typography variant="caption" sx={{ opacity: 0.85 }}>مساحتي</Typography>
            <Typography variant="h5" fontWeight={800} noWrap>{admin?.name ?? '—'}</Typography>
            <Typography variant="body2" sx={{ opacity: 0.9 }}>{isSuper ? 'مدير النظام' : 'محامٍ'}</Typography>
          </Box>
          <Box textAlign="end" sx={{ display: { xs: 'none', sm: 'block' } }}>
            <Typography variant="body2" sx={{ opacity: 0.9 }}>
              {new Intl.DateTimeFormat('ar', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date())}
            </Typography>
            {isSuper && (
              <ToggleButtonGroup
                size="small" exclusive value={scope} onChange={(_, v) => v && setScope(v)}
                sx={{ mt: 1, bgcolor: 'rgba(255,255,255,.12)', borderRadius: 2,
                  '& .MuiToggleButton-root': { color: 'common.white', border: 0, px: 1.5, py: 0.25 },
                  '& .Mui-selected': { bgcolor: 'rgba(255,255,255,.22) !important', color: 'common.white' } }}
              >
                <ToggleButton value="mine">لي</ToggleButton>
                <ToggleButton value="all">المكتب</ToggleButton>
              </ToggleButtonGroup>
            )}
          </Box>
        </Box>
      </Card>

      {errors.length > 0 && <Alert severity="error">{errors[0].message}</Alert>}

      {/* stat tiles */}
      <Box display="grid" gap={2} gridTemplateColumns={{ xs: 'repeat(2, 1fr)', md: 'repeat(4, 1fr)' }}>
        <Stat label="جلسات اليوم" value={sessionsToday.data?.data.length ?? 0} loading={sessionsToday.isLoading} accent="#6a1b9a" />
        <Stat label="مواعيد اليوم" value={apptsToday.data?.data.length ?? 0} loading={apptsToday.isLoading} accent="#1f4e79" />
        <Stat label="مهام متأخرة" value={overdueTasks.length} loading={tasks.isLoading} accent="#c62828" tone={overdueTasks.length ? 'error' : undefined} />
        <Stat label="مستحقّ عليك" value={formatMoney(dueTotal)} loading={caseReceipts.isLoading || serviceReceipts.isLoading} accent="#b26a00" />
      </Box>

      {/* two-column cockpit */}
      <Box display="grid" gap={2} gridTemplateColumns={{ xs: '1fr', md: '1.25fr 1fr' }} alignItems="start">
        <Stack spacing={2}>
          <Section title="خطّي اليوم" loading={sessionsToday.isLoading || apptsToday.isLoading} count={dayItems.length} empty="لا نشاط لك اليوم.">
            {dayItems.map((it) => (
              <ListItem key={it.id} divider component={it.href ? Link : 'li'} href={it.href} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                <ListItemText
                  primary={<>{it.time ? <b>{it.time} · </b> : null}{it.title}</>}
                  primaryTypographyProps={{ noWrap: true }}
                  secondary={it.sub}
                />
                {it.tag && <Chip size="small" label={it.tag} />}
              </ListItem>
            ))}
          </Section>

          <Section title="قادم" loading={upcoming.isLoading} count={upcomingItems.length} empty="لا شيء قادم خلال 30 يومًا.">
            {upcomingItems.map((s) => (
              <ListItem key={s.id} divider component={Link} href={oldLink(`/session/${s.id}`)} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                <ListItemText
                  primary={s.case?.name ?? s.client?.name ?? `جلسة #${s.id}`}
                  primaryTypographyProps={{ noWrap: true }}
                  secondary={[formatDate(s.gregorian_date), s.type === 2 ? 'إجراء' : 'جلسة', s.client?.name].filter(Boolean).join(' · ')}
                />
                {s.status?.name && <Chip size="small" label={s.status.name} />}
              </ListItem>
            ))}
          </Section>
        </Stack>

        <Stack spacing={2}>
          <Section title="تنبيهات" loading={tasks.isLoading || caseReceipts.isLoading} count={overdueTasks.length + dueReceipts.length} empty="لا تنبيهات.">
            {[
              ...overdueTasks.map((t) => (
                <ListItem key={`t${t.id}`} divider component={Link} href={oldLink(`/task/${t.id}`)} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                  <ListItemText
                    primary={t.description || t.task_type?.name || `مهمة #${t.id}`}
                    primaryTypographyProps={{ noWrap: true }}
                    secondary={[t.client_id?.name, `الموعد ${formatDate(t.end_date)}`].filter(Boolean).join(' · ')}
                  />
                  <Chip size="small" color="error" label={daysLate(t.end_date) ? `متأخرة ${daysLate(t.end_date)}ي` : 'اليوم'} />
                </ListItem>
              )),
              ...dueReceipts.map((r) => {
                const name = r.kind === 'case' ? r.case?.name : r.service?.name;
                const href = r.kind === 'case' && r.case ? oldLink(`/case/${r.case.id}`) : undefined;
                return (
                  <ListItem key={`${r.kind}${r.id}`} divider component={href ? Link : 'li'} href={href} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                    <ListItemText
                      primary={<>{formatMoney(toNumber(r.unpaid_amount ?? r.total_amount))} · {name ?? `سند #${r.id}`}</>}
                      primaryTypographyProps={{ noWrap: true }}
                      secondary={[r.kind === 'case' ? 'قضية' : 'خدمة', `استحق ${formatDate(r.date)}`].filter(Boolean).join(' · ')}
                    />
                    <Chip size="small" color="warning" variant="outlined" label="مستحق" />
                  </ListItem>
                );
              }),
            ]}
          </Section>

          <Section title="قضاياي النشطة" loading={cases.isLoading} count={myCases.length} empty="لا قضايا نشطة.">
            {myCases.map((c) => (
              <ListItem key={c.id} divider component={Link} href={oldLink(`/case/${c.id}`)} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                <ListItemText
                  primary={c.name}
                  primaryTypographyProps={{ noWrap: true }}
                  secondary={[c.number, c.client?.name].filter(Boolean).join(' · ')}
                />
                {c.status?.name && <Chip size="small" variant="outlined" label={c.status.name} />}
              </ListItem>
            ))}
          </Section>
        </Stack>
      </Box>
    </Stack>
  );
}

function Stat({ label, value, loading, accent, tone }: { label: string; value: ReactNode; loading?: boolean; accent?: string; tone?: 'error' }) {
  return (
    <Card sx={{ borderInlineStart: accent ? `3px solid ${accent}` : undefined }}>
      <CardContent sx={{ py: 1.5 }}>
        <Typography variant="body2" color="text.secondary">{label}</Typography>
        <Typography variant="h5" fontWeight={800} color={tone === 'error' ? 'error.main' : undefined} sx={{ fontVariantNumeric: 'tabular-nums' }}>
          {loading ? <Skeleton width={60} /> : value}
        </Typography>
      </CardContent>
    </Card>
  );
}

function Section({ title, loading, count, empty, children }: { title: string; loading: boolean; count: number; empty: string; children: ReactNode[] }) {
  return (
    <Card>
      <CardHeader title={title} titleTypographyProps={{ variant: 'h6', fontWeight: 700 }} subheader={loading ? undefined : `${count}`} sx={{ pb: 0 }} />
      <List dense sx={{ maxHeight: 460, overflow: 'auto', pt: 0.5 }}>
        {loading
          ? [0, 1, 2].map((i) => <ListItem key={i}><Skeleton width="100%" height={36} /></ListItem>)
          : children.length
            ? children
            : <ListItem><ListItemText secondary={empty} /></ListItem>}
      </List>
    </Card>
  );
}
