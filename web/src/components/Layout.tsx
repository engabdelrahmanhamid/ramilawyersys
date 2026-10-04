import { AppBar, Box, Button, Chip, Container, Stack, Toolbar, Tooltip, Typography } from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import { useEffect, useState, type ReactNode } from 'react';
import { Link as RouterLink, useLocation } from 'react-router-dom';
import { useAuth } from '../auth';
import { IS_DEMO, OLD_APP } from '../config';
import { CommandPalette } from './CommandPalette';

const navItems = [
  { to: '/', label: 'يومي' },
  { to: '/clients', label: 'العملاء' },
];

export function Layout({ children }: { children: ReactNode }) {
  const { admin, logout } = useAuth();
  const location = useLocation();
  const [paletteOpen, setPaletteOpen] = useState(false);

  // Ctrl/Cmd+K opens the global search from anywhere.
  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setPaletteOpen((v) => !v);
      }
    }
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, []);

  return (
    <Box minHeight="100vh" bgcolor="background.default">
      <AppBar position="sticky" color="inherit" elevation={0} sx={{ borderBottom: 1, borderColor: 'divider' }}>
        <Toolbar sx={{ gap: { xs: 1, sm: 2 } }}>
          <Typography variant="h6" fontWeight={700} noWrap sx={{ fontSize: { xs: 16, sm: 20 } }}>
            مكتب رامي
          </Typography>

          <Stack direction="row" spacing={0.5} sx={{ flexGrow: 1 }}>
            {navItems.map((item) => {
              const active = item.to === '/' ? location.pathname === '/' : location.pathname.startsWith(item.to);
              return (
                <Button
                  key={item.to}
                  component={RouterLink}
                  to={item.to}
                  size="small"
                  color={active ? 'primary' : 'inherit'}
                  sx={{ fontWeight: active ? 700 : 400 }}
                >
                  {item.label}
                </Button>
              );
            })}
          </Stack>

          <Tooltip title="بحث شامل (Ctrl+K)">
            <Button onClick={() => setPaletteOpen(true)} size="small" color="inherit" startIcon={<SearchIcon />}>
              بحث
            </Button>
          </Tooltip>
          {IS_DEMO && <Chip label="نسخة تجريبية" color="error" size="small" />}
          <Button href={OLD_APP} target="_blank" rel="noopener" size="small" sx={{ display: { xs: 'none', md: 'inline-flex' } }}>
            الواجهة القديمة
          </Button>
          <Typography variant="body2" color="text.secondary" noWrap sx={{ display: { xs: 'none', sm: 'block' } }}>
            {admin?.name}
          </Typography>
          <Button onClick={logout} size="small" color="inherit">
            خروج
          </Button>
        </Toolbar>
      </AppBar>

      <Container maxWidth="lg" sx={{ py: 3 }}>
        {children}
      </Container>

      <CommandPalette open={paletteOpen} onClose={() => setPaletteOpen(false)} />
    </Box>
  );
}
