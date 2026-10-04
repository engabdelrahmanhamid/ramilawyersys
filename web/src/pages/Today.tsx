import {
  Alert, Box, Card, CardContent, CardHeader, Chip, Link, List, ListItem, ListItemText, Skeleton, Stack,
  ToggleButton, ToggleButtonGroup, Typography,
} from '@mui/material';
import { useQuery } from '@tanstack/react-query';
import { useState, type ReactNode } from 'react';
import { get, type Page } from '../api';
import { useAuth } from '../auth';
import { OLD_APP } from '../config';
import { daysLate, formatDate, formatMoney, isTaskOverdue, toNumber, today } from '../format';
import type { Receipt, Session, Task } from '../types';

type Scope = 'mine' | 'all';

// Details still open in the old interface until those screens are rebuilt.
const oldLink = (path: string) => `${OLD_APP}${path}`;

export function TodayPage() {
  const { admin } = useAuth();
  const isSuper = admin?.super === 1;
  const [scope, setScope] = useState<Scope>(isSuper ? 'all' : 'mine');
  const adminId = scope === 'mine' ? admin?.id : undefined;
  const day = today();

  const sessions = useQuery({
    queryKey: ['today-sessions', day, adminId],
    queryFn: () => get<Page<Session>>('session/get', { date_from: day, date_to: day, admin_id: adminId, limit: 200 }),
  });
  const tasks = useQuery({
    queryKey: ['open-tasks', adminId],
    queryFn: () => get<Page<Task>>('task/get', { status_id: 1, admin_id: adminId, limit: 500 }),
  });
  const caseReceipts = useQuery({
    queryKey: ['due-case-receipts', day, adminId],
    queryFn: () => get<Page<Receipt>>('case_receipt/get', { payment_status: 0, date_to: day, admin_id: adminId, limit: 500 }),
  });
  const serviceReceipts = useQuery({
    queryKey: ['due-service-receipts', day, adminId],
    queryFn: () => get<Page<Receipt>>('service_receipt/get', { payment_status: 0, date_to: day, admin_id: adminId, limit: 500 }),
  });

  const todaySessions = [...(sessions.data?.data ?? [])].sort((a, b) => (a.session_time ?? '').localeCompare(b.session_time ?? ''));
  const overdueTasks = (tasks.data?.data ?? []).filter(isTaskOverdue).sort((a, b) => daysLate(b.end_date) - daysLate(a.end_date));
  const dueReceipts = [
    ...(caseReceipts.data?.data ?? []).map((r) => ({ ...r, kind: 'case' as const })),
    ...(serviceReceipts.data?.data ?? []).map((r) => ({ ...r, kind: 'service' as const })),
  ].sort((a, b) => a.date.localeCompare(b.date));
  const dueTotal = dueReceipts.reduce((sum, r) => sum + toNumber(r.unpaid_amount ?? r.total_amount), 0);

  const errors = [sessions, tasks, caseReceipts, serviceReceipts].map((q) => q.error).filter(Boolean) as Error[];

  return (
    <Stack spacing={3}>
      <Stack direction={{ xs: 'column', sm: 'row' }} justifyContent="space-between" alignItems={{ sm: 'center' }} gap={1}>
        <Box>
          <Typography variant="h4" fontWeight={700}>يومي</Typography>
          <Typography color="text.secondary">{formatDate(day)}</Typography>
        </Box>
        <ToggleButtonGroup size="small" exclusive value={scope} onChange={(_, v) => v && setScope(v)}>
          <ToggleButton value="mine">لي فقط</ToggleButton>
          <ToggleButton value="all">الكل</ToggleButton>
        </ToggleButtonGroup>
      </Stack>

      {errors.length > 0 && <Alert severity="error">{errors[0].message}</Alert>}

      <Box display="grid" gap={2} gridTemplateColumns={{ xs: 'repeat(2, 1fr)', md: 'repeat(4, 1fr)' }}>
        <Stat label="جلسات اليوم" value={todaySessions.length} loading={sessions.isLoading} />
        <Stat label="مهام متأخرة" value={overdueTasks.length} loading={tasks.isLoading} tone={overdueTasks.length ? 'error' : undefined} />
        <Stat label="سندات مستحقة" value={dueReceipts.length} loading={caseReceipts.isLoading || serviceReceipts.isLoading} />
        <Stat label="المبلغ المستحق" value={formatMoney(dueTotal)} loading={caseReceipts.isLoading || serviceReceipts.isLoading} />
      </Box>

      <Box display="grid" gap={2} gridTemplateColumns={{ xs: '1fr', md: 'repeat(3, 1fr)' }} alignItems="start">
        <Section title="جلسات اليوم" loading={sessions.isLoading} empty="لا توجد جلسات اليوم">
          {todaySessions.map((s) => (
            <ListItem key={s.id} divider component={Link} href={oldLink(`/session/${s.id}`)} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
              <ListItemText
                primary={<>{s.session_time ? <b>{s.session_time.slice(0, 5)} · </b> : null}{s.case?.name ?? s.client?.name ?? `جلسة #${s.id}`}</>}
                secondary={[s.type === 2 ? 'مراجعة' : 'جلسة', s.destination, s.admin?.name].filter(Boolean).join(' · ')}
              />
              {s.status?.name && <Chip size="small" label={s.status.name} />}
            </ListItem>
          ))}
        </Section>

        <Section title="مهام متأخرة" loading={tasks.isLoading} empty="لا توجد مهام متأخرة 👏">
          {overdueTasks.map((t) => (
            <ListItem key={t.id} divider component={Link} href={oldLink(`/task/${t.id}`)} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
              <ListItemText
                primary={t.description || t.task_type?.name || `مهمة #${t.id}`}
                primaryTypographyProps={{ noWrap: true }}
                secondary={[t.client_id?.name, t.admin?.name, `الموعد ${formatDate(t.end_date)}`].filter(Boolean).join(' · ')}
              />
              <Chip size="small" color="error" label={daysLate(t.end_date) ? `متأخرة ${daysLate(t.end_date)} يوم` : 'اليوم'} />
            </ListItem>
          ))}
        </Section>

        <Section title="سندات مستحقة" loading={caseReceipts.isLoading || serviceReceipts.isLoading} empty="لا توجد سندات مستحقة">
          {dueReceipts.map((r) => {
            const name = r.kind === 'case' ? r.case?.name : r.service?.name;
            const client = r.kind === 'case' ? r.case?.client?.name : r.service?.client?.name;
            const href = r.kind === 'case' && r.case ? oldLink(`/case/${r.case.id}`) : undefined;
            return (
              <ListItem key={`${r.kind}-${r.id}`} divider component={href ? Link : 'li'} href={href} target="_blank" rel="noopener" sx={{ color: 'inherit' }}>
                <ListItemText
                  primary={<>{formatMoney(toNumber(r.unpaid_amount ?? r.total_amount))} · {client ?? name ?? `سند #${r.id}`}</>}
                  secondary={[r.kind === 'case' ? 'قضية' : 'خدمة', name, `استحق ${formatDate(r.date)}`].filter(Boolean).join(' · ')}
                />
              </ListItem>
            );
          })}
        </Section>
      </Box>
    </Stack>
  );
}

function Stat({ label, value, loading, tone }: { label: string; value: ReactNode; loading?: boolean; tone?: 'error' }) {
  return (
    <Card>
      <CardContent>
        <Typography variant="body2" color="text.secondary">{label}</Typography>
        <Typography variant="h5" fontWeight={700} color={tone === 'error' ? 'error.main' : undefined}>
          {loading ? <Skeleton width={60} /> : value}
        </Typography>
      </CardContent>
    </Card>
  );
}

function Section({ title, loading, empty, children }: { title: string; loading: boolean; empty: string; children: ReactNode[] }) {
  return (
    <Card>
      <CardHeader title={title} titleTypographyProps={{ variant: 'h6', fontWeight: 700 }} subheader={loading ? undefined : `${children.length}`} />
      <List dense sx={{ maxHeight: 520, overflow: 'auto', pt: 0 }}>
        {loading
          ? [0, 1, 2].map((i) => <ListItem key={i}><Skeleton width="100%" height={40} /></ListItem>)
          : children.length
            ? children
            : <ListItem><ListItemText secondary={empty} /></ListItem>}
      </List>
    </Card>
  );
}
