import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react';
import { get, post, tokenStore } from './api';
import type { Admin } from './types';

type AuthState = {
  admin: Admin | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [admin, setAdmin] = useState<Admin | null>(null);
  const [loading, setLoading] = useState(!!tokenStore.get());

  useEffect(() => {
    if (!tokenStore.get()) return;
    get<Admin>('auth/my_info')
      .then(setAdmin)
      .catch(() => tokenStore.clear())
      .finally(() => setLoading(false));
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const result = await post<Admin>('auth/login', { email, password });
    if (!result.token) throw new Error('لم يُرجع الخادم رمز الدخول.');
    tokenStore.set(result.token);
    setAdmin(result);
  }, []);

  const logout = useCallback(async () => {
    // The old app has no logout endpoint — it just clears the token client-side.
    tokenStore.clear();
    setAdmin(null);
  }, []);

  return <AuthContext.Provider value={{ admin, loading, login, logout }}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const value = useContext(AuthContext);
  if (!value) throw new Error('useAuth must be used inside AuthProvider');
  return value;
}
