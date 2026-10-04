import {
  Alert, Box, Card, Chip, InputAdornment, Link, List, ListItem, ListItemText, Pagination,
  Skeleton, Stack, TextField, Typography,
} from '@mui/material';
import PersonSearchIcon from '@mui/icons-material/PersonSearch';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { useEffect, useState } from 'react';
import { get, type Page } from '../api';
import { OLD_APP } from '../config';
import { useDebounced } from '../hooks';
import type { ClientListItem } from '../types';

const PER_PAGE = 20;
// Profiles still open in the old interface until that screen is rebuilt.
const oldClient = (id: number) => `${OLD_APP}/client/${id}`;

export function ClientsPage() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const debounced = useDebounced(search.trim(), 400);

  // A new search term always starts from the first page.
  useEffect(() => setPage(1), [debounced]);

  const query = useQuery({
    queryKey: ['clients', debounced, page],
    queryFn: () => get<Page<ClientListItem>>('client/get', { search_text: debounced, page, limit: PER_PAGE }),
    placeholderData: keepPreviousData,
  });

  const clients = query.data?.data ?? [];
  const totalPages = query.data?.pagination.total_pages ?? 0;
  const total = query.data?.pagination.total ?? 0;

  return (
    <Stack spacing={3}>
      <Box>
        <Typography variant="h4" fontWeight={700}>العملاء</Typography>
        <Typography color="text.secondary">
          {query.isLoading ? 'جارٍ التحميل…' : `${total.toLocaleString('ar-SA-u-nu-latn')} عميل`}
        </Typography>
      </Box>

      <TextField
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        placeholder="ابحث بالاسم أو رقم الجوال…"
        fullWidth
        autoFocus
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <PersonSearchIcon color="action" />
            </InputAdornment>
          ),
        }}
      />

      {query.error && <Alert severity="error">{(query.error as Error).message}</Alert>}

      <Card sx={{ opacity: query.isFetching && !query.isLoading ? 0.6 : 1, transition: 'opacity .15s' }}>
        <List dense sx={{ py: 0 }}>
          {query.isLoading
            ? [0, 1, 2, 3, 4].map((i) => (
                <ListItem key={i} divider>
                  <Skeleton width="100%" height={44} />
                </ListItem>
              ))
            : clients.length
              ? clients.map((c) => (
                  <ListItem
                    key={c.id}
                    divider
                    component={Link}
                    href={oldClient(c.id)}
                    target="_blank"
                    rel="noopener"
                    sx={{ color: 'inherit' }}
                  >
                    <ListItemText
                      primary={c.name}
                      secondary={[c.phone, c.type?.name, c.branch?.name].filter(Boolean).join(' · ') || undefined}
                    />
                    {c.status?.name && <Chip size="small" label={c.status.name} />}
                  </ListItem>
                ))
              : (
                  <ListItem>
                    <ListItemText secondary={debounced ? 'لا توجد نتائج مطابقة' : 'لا يوجد عملاء'} />
                  </ListItem>
                )}
        </List>
      </Card>

      {totalPages > 1 && (
        <Stack alignItems="center">
          <Pagination
            count={totalPages}
            page={page}
            onChange={(_, value) => setPage(value)}
            color="primary"
            siblingCount={0}
          />
        </Stack>
      )}
    </Stack>
  );
}
