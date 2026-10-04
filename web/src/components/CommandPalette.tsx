import {
  Box, Chip, CircularProgress, Dialog, InputAdornment, List, ListItemButton, ListItemText,
  ListSubheader, TextField, Typography,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import { useQuery } from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import { get, type Page } from '../api';
import { OLD_APP } from '../config';
import { useDebounced } from '../hooks';
import type { CaseListItem, ClientListItem } from '../types';

type Hit = { key: string; label: string; secondary?: string; href: string; kind: 'client' | 'case' };

export function CommandPalette({ open, onClose }: { open: boolean; onClose: () => void }) {
  const [search, setSearch] = useState('');
  const debounced = useDebounced(search.trim(), 300);
  const enabled = open && debounced.length >= 2;

  // Reset the box each time it opens so an old query never flashes.
  useEffect(() => {
    if (open) setSearch('');
  }, [open]);

  const clients = useQuery({
    queryKey: ['search-clients', debounced],
    queryFn: () => get<Page<ClientListItem>>('client/get', { search_text: debounced, limit: 6 }),
    enabled,
  });
  const cases = useQuery({
    queryKey: ['search-cases', debounced],
    queryFn: () => get<Page<CaseListItem>>('case/get', { search_text: debounced, limit: 6 }),
    enabled,
  });

  const loading = enabled && (clients.isFetching || cases.isFetching);

  const hits = useMemo<Hit[]>(() => {
    const clientHits: Hit[] = (clients.data?.data ?? []).map((c) => ({
      key: `client-${c.id}`,
      label: c.name,
      secondary: c.phone ?? undefined,
      href: `${OLD_APP}/client/${c.id}`,
      kind: 'client',
    }));
    const caseHits: Hit[] = (cases.data?.data ?? []).map((c) => ({
      key: `case-${c.id}`,
      label: c.name,
      secondary: [c.number, c.client?.name].filter(Boolean).join(' · ') || undefined,
      href: `${OLD_APP}/case/${c.id}`,
      kind: 'case',
    }));
    return [...clientHits, ...caseHits];
  }, [clients.data, cases.data]);

  function openHit(hit: Hit) {
    window.open(hit.href, '_blank', 'noopener');
    onClose();
  }

  return (
    <Dialog
      open={open}
      onClose={onClose}
      fullWidth
      maxWidth="sm"
      PaperProps={{ sx: { position: 'absolute', top: 64, m: 0 } }}
    >
      <Box sx={{ p: 1.5, pb: 0 }}>
        <TextField
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="ابحث عن عميل أو قضية…"
          fullWidth
          autoFocus
          onKeyDown={(e) => {
            if (e.key === 'Enter' && hits[0]) openHit(hits[0]);
          }}
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                {loading ? <CircularProgress size={18} /> : <SearchIcon color="action" />}
              </InputAdornment>
            ),
          }}
        />
      </Box>

      <List sx={{ minHeight: 80, maxHeight: 420, overflow: 'auto', pt: 0 }}>
        {debounced.length < 2 ? (
          <Box sx={{ p: 3 }}>
            <Typography color="text.secondary" variant="body2" align="center">
              اكتب حرفين على الأقل للبحث
            </Typography>
          </Box>
        ) : hits.length === 0 && !loading ? (
          <Box sx={{ p: 3 }}>
            <Typography color="text.secondary" variant="body2" align="center">
              لا توجد نتائج مطابقة
            </Typography>
          </Box>
        ) : (
          hits.map((hit, i) => {
            const first = i === 0 || hits[i - 1].kind !== hit.kind;
            return (
              <Box key={hit.key}>
                {first && (
                  <ListSubheader disableSticky sx={{ bgcolor: 'transparent', lineHeight: '32px' }}>
                    {hit.kind === 'client' ? 'العملاء' : 'القضايا'}
                  </ListSubheader>
                )}
                <ListItemButton onClick={() => openHit(hit)}>
                  <ListItemText primary={hit.label} secondary={hit.secondary} primaryTypographyProps={{ noWrap: true }} />
                  <Chip size="small" variant="outlined" label="فتح ↗" />
                </ListItemButton>
              </Box>
            );
          })
        )}
      </List>
    </Dialog>
  );
}
