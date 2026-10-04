import { CircularProgress, Box } from '@mui/material';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider, useAuth } from './auth';
import { Layout } from './components/Layout';
import { ActivityPage } from './pages/Activity';
import { ClientsPage } from './pages/Clients';
import { LoginPage } from './pages/Login';
import { TodayPage } from './pages/Today';
import { WorkspacePage } from './pages/Workspace';
import { AppTheme } from './theme';

const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: 1, refetchOnWindowFocus: true, staleTime: 60_000 } },
});

function App() {
  const { admin, loading } = useAuth();
  if (loading) {
    return (
      <Box minHeight="100vh" display="grid" sx={{ placeItems: 'center' }}>
        <CircularProgress />
      </Box>
    );
  }
  if (!admin) return <LoginPage />;
  return (
    <Layout>
      <Routes>
        <Route path="/" element={<WorkspacePage />} />
        <Route path="/today" element={<TodayPage />} />
        <Route path="/activity" element={<ActivityPage />} />
        <Route path="/clients" element={<ClientsPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Layout>
  );
}

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <AppTheme>
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <BrowserRouter>
            <App />
          </BrowserRouter>
        </AuthProvider>
      </QueryClientProvider>
    </AppTheme>
  </StrictMode>,
);
