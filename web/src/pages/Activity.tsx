import {
  Alert,
  Avatar,
  Box,
  Card,
  Chip,
  CircularProgress,
  Divider,
  InputAdornment,
  Stack,
  TextField,
  ToggleButton,
  ToggleButtonGroup,
  Tooltip,
  Typography,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import EventIcon from '@mui/icons-material/Event';
import GavelIcon from '@mui/icons-material/Gavel';
import PlaylistAddCheckIcon from '@mui/icons-material/PlaylistAddCheck';
import TaskAltIcon from '@mui/icons-material/TaskAlt';
import ForumIcon from '@mui/icons-material/Forum';
import { useQuery } from '@tanstack/react-query';
import { useMemo, useState } from 'react';
import dayjs from 'dayjs';
import {
  fetchActivities,
  KIND_LABEL,
  type Activity,
  type ActivityKind,
  type StatusTone,
} from '../activity';
import { useDebounced } from '../hooks';
import { formatDate } from '../format';

const KIND_ICON: Record<ActivityKind, typeof EventIcon> = {
  appointment: EventIcon,
  session: GavelIcon,
  procedure: PlaylistAddCheckIcon,
  task: TaskAltIcon,
  contact: ForumIcon,
};

// One colour system for every kind, driven by the normalised tone.
const TONE_COLOR: Record<StatusTone, 'default' | 'info' | 'success' | 'error'> = {
  open: 'info',
  done: 'success',
  overdue: 'error',
  neutral: 'default',
};

const KIND_ACCENT: Record<ActivityKind, string> = {
  appointment: '#1f4e79',
  session: '#6a1b9a',
  procedure: '#00695c',
  task: '#ef6c00',
  contact: '#2e7d32',
};

const FILTERS: ActivityKind[] = ['appointment', 'session', 'procedure', 'task', 'contact'];

function groupLabel(date: string | null): string {
  if (!date) return 'بدون تاريخ';
  const d = dayjs(date);
  const today = dayjs().startOf('day');
  if (d.isSame(today, 'day')) return 'اليوم';
  if (d.isSame(today.add(1, 'day'), 'day')) return 'غدًا';
  if (d.isSame(today.subtract(1, 'day'), 'day')) return 'أمس';
  return formatDate(date);
}

function ActivityRow({ item }: { item: Activity }) {
  const Icon = KIND_ICON[item.kind];
  return (
    <Stack direction="row" spacing={1.5} alignItems="flex-start" sx={{ py: 1.25 }}>
      <Avatar sx={{ bgcolor: KIND_ACCENT[item.kind], width: 34, height: 34 }}>
        <Icon sx={{ fontSize: 18 }} />
      </Avatar>
      <Box flexGrow={1} minWidth={0}>
        <Stack direction="row" spacing={1} alignItems="center" flexWrap="wrap">
          <Chip label={KIND_LABEL[item.kind]} size="small" sx={{ bgcolor: KIND_ACCENT[item.kind], color: '#fff' }} />
          <Typography fontWeight={600} noWrap>
            {item.title}
          </Typography>
          {item.time && (
            <Typography variant="caption" color="text.secondary">
              {item.time}
            </Typography>
          )}
        </Stack>
        <Typography variant="body2" color="text.secondary" sx={{ mt: 0.25 }} noWrap>
          {item.client?.name ?? 'بدون عميل'}
          {item.case?.name ? ` · ${item.case.name}` : ''}
          {item.note ? ` — ${item.note}` : ''}
        </Typography>
      </Box>
      <Stack alignItems="flex-end" spacing={0.5} flexShrink={0}>
        <Chip label={item.status.label} size="small" color={TONE_COLOR[item.status.tone]} variant="outlined" />
        {item.assignee?.name && (
          <Tooltip title="المسؤول">
            <Typography variant="caption" color="text.secondary" noWrap>
              {item.assignee.name}
            </Typography>
          </Tooltip>
        )}
      </Stack>
    </Stack>
  );
}

export function ActivityPage() {
  const [kinds, setKinds] = useState<ActivityKind[]>([]);
  const [rawSearch, setRawSearch] = useState('');
  const search = useDebounced(rawSearch, 400);

  const query = useQuery({
    queryKey: ['activities', kinds, search],
    queryFn: () => fetchActivities({ kinds, search_text: search, limit: 50 }),
  });

  const grouped = useMemo(() => {
    const items = query.data?.items ?? [];
    const map = new Map<string, Activity[]>();
    for (const item of items) {
      const label = groupLabel(item.date);
      (map.get(label) ?? map.set(label, []).get(label)!).push(item);
    }
    return [...map.entries()];
  }, [query.data]);

  return (
    <Stack spacing={2}>
      <Box>
        <Typography variant="h5" fontWeight={700}>
          مركز النشاط
        </Typography>
        <Typography variant="body2" color="text.secondary">
          المواعيد والجلسات والإجراءات والمهام والتواصل في خطٍّ زمني واحد.
        </Typography>
      </Box>

      <Stack direction={{ xs: 'column', md: 'row' }} spacing={1.5} alignItems={{ md: 'center' }}>
        <ToggleButtonGroup
          size="small"
          value={kinds}
          onChange={(_, next: ActivityKind[]) => setKinds(next)}
          sx={{ flexWrap: 'wrap' }}
        >
          {FILTERS.map((k) => (
            <ToggleButton key={k} value={k} sx={{ px: 1.5 }}>
              {KIND_LABEL[k]}
            </ToggleButton>
          ))}
        </ToggleButtonGroup>
        <Box flexGrow={1} />
        <TextField
          size="small"
          placeholder="بحث في النشاط…"
          value={rawSearch}
          onChange={(e) => setRawSearch(e.target.value)}
          sx={{ minWidth: { xs: '100%', md: 260 } }}
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <SearchIcon fontSize="small" />
              </InputAdornment>
            ),
          }}
        />
      </Stack>

      {query.data?.errors?.length ? (
        <Alert severity="warning">
          تعذّر تحميل بعض المصادر: {query.data.errors.map((e) => KIND_LABEL[e.kind]).join('، ')}
        </Alert>
      ) : null}

      {query.isLoading ? (
        <Box display="grid" sx={{ placeItems: 'center', py: 6 }}>
          <CircularProgress />
        </Box>
      ) : query.isError ? (
        <Alert severity="error">تعذّر تحميل النشاط. حاول مرة أخرى.</Alert>
      ) : grouped.length === 0 ? (
        <Card sx={{ p: 4, textAlign: 'center', color: 'text.secondary' }}>لا يوجد نشاط مطابق.</Card>
      ) : (
        grouped.map(([label, items]) => (
          <Card key={label} sx={{ p: { xs: 1.5, sm: 2 } }}>
            <Typography variant="subtitle2" color="text.secondary" sx={{ mb: 0.5 }}>
              {label} · {items.length}
            </Typography>
            <Divider />
            <Stack divider={<Divider flexItem />}>
              {items.map((item) => (
                <ActivityRow key={item.key} item={item} />
              ))}
            </Stack>
          </Card>
        ))
      )}
    </Stack>
  );
}
