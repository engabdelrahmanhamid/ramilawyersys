import { AppBar, Box, Button, Chip, Container, Toolbar, Typography } from '@mui/material';
import type { ReactNode } from 'react';
import { useAuth } from '../auth';
import { IS_DEMO, OLD_APP } from '../config';

export function Layout({ children }: { children: ReactNode }) {
  const { admin, logout } = useAuth();
  return (
    <Box minHeight="100vh" bgcolor="background.default">
      <AppBar position="sticky" color="inherit" elevation={0} sx={{ borderBottom: 1, borderColor: 'divider' }}>
        <Toolbar sx={{ gap: { xs: 1, sm: 2 } }}>
          <Typography variant="h6" fontWeight={700} noWrap sx={{ flexGrow: 1, fontSize: { xs: 16, sm: 20 } }}>
            مكتب رامي
          </Typography>
          {IS_DEMO && <Chip label="نسخة تجريبية" color="error" size="small" />}
          <Button href={OLD_APP} target="_blank" rel="noopener" size="small" sx={{ display: { xs: 'none', sm: 'inline-flex' } }}>
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
    </Box>
  );
}
