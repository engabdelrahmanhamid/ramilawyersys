import { API_BASE } from './config';

// The token lives in sessionStorage (cleared when the tab closes) until the back-end
// supports an httpOnly cookie; the old app kept it in localStorage indefinitely.
const TOKEN_KEY = 'rami.token';

export const tokenStore = {
  get: () => sessionStorage.getItem(TOKEN_KEY),
  set: (token: string) => sessionStorage.setItem(TOKEN_KEY, token),
  clear: () => sessionStorage.removeItem(TOKEN_KEY),
};

export class ApiError extends Error {
  constructor(message: string, public unauthorized = false) {
    super(message);
  }
}

type Envelope<T> = { status: number; message?: string; data: T };

export type Page<T> = {
  data: T[];
  pagination: { total: number; count: number; per_page: number; current_page: number; total_pages: number };
};

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const token = tokenStore.get();
  const headers: Record<string, string> = { Accept: 'application/json', lang: 'ar' };
  if (token) headers.Authorization = `Bearer ${token}`;
  if (init.body && !(init.body instanceof FormData)) headers['Content-Type'] = 'application/json';

  let response: Response;
  try {
    response = await fetch(`${API_BASE}/${path.replace(/^\//, '')}`, { ...init, headers: { ...headers, ...(init.headers as object) } });
  } catch {
    throw new ApiError('تعذر الاتصال بالخادم. تحقق من الإنترنت.');
  }
  if (response.status === 401) {
    tokenStore.clear();
    throw new ApiError('انتهت جلسة الدخول، سجّل الدخول مرة أخرى.', true);
  }
  const body = (await response.json().catch(() => null)) as Envelope<T> | null;
  if (!response.ok || !body) throw new ApiError(`خطأ من الخادم (${response.status}).`);
  if (body.status !== 1) throw new ApiError(body.message || 'تعذر تنفيذ الطلب.');
  return body.data;
}

export function get<T>(path: string, params: Record<string, string | number | undefined | null> = {}) {
  const query = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') query.set(key, String(value));
  });
  const qs = query.toString();
  return request<T>(qs ? `${path}?${qs}` : path);
}

export function post<T>(path: string, data: Record<string, unknown>) {
  return request<T>(path, { method: 'POST', body: JSON.stringify(data) });
}
