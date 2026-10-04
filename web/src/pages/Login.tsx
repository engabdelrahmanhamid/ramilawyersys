import { Alert, Box, Button, Card, CardContent, Chip, Stack, TextField, Typography } from '@mui/material';
import { useState, type FormEvent } from 'react';
import { useAuth } from '../auth';
import { IS_DEMO } from '../config';

export function LoginPage() {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setError(null);
    try {
      await login(email.trim(), password);
    } catch (e) {
      const message = (e as Error).message;
      setError(message === 'invalid email or password' ? 'البريد أو كلمة المرور غير صحيحة' : message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <Box minHeight="100vh" display="grid" sx={{ placeItems: 'center', bgcolor: 'background.default', p: 2 }}>
      <Card sx={{ width: '100%', maxWidth: 400 }}>
        <CardContent component="form" onSubmit={submit}>
          <Stack spacing={2}>
            <Stack direction="row" alignItems="center" justifyContent="space-between">
              <Typography variant="h5" fontWeight={700}>
                تسجيل الدخول
              </Typography>
              {IS_DEMO && <Chip label="نسخة تجريبية" color="error" size="small" />}
            </Stack>
            {error && <Alert severity="error">{error}</Alert>}
            <TextField label="البريد الإلكتروني" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required autoFocus inputProps={{ dir: 'ltr' }} />
            <TextField label="كلمة المرور" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required inputProps={{ dir: 'ltr' }} />
            <Button type="submit" variant="contained" size="large" disabled={busy}>
              {busy ? 'جارٍ الدخول…' : 'دخول'}
            </Button>
          </Stack>
        </CardContent>
      </Card>
    </Box>
  );
}
