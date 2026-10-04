// In dev we hit the Vite proxy at a same-origin relative path (see vite.config.ts) so the
// JSON login POST isn't blocked by the production API's missing CORS preflight. In a
// production build we call VITE_API_BASE directly — the deployed origin must be allowed
// by the back-end's CORS policy (or served behind the same domain) for POSTs to work.
export const API_BASE = import.meta.env.DEV
  ? '/apiAdmin'
  : (import.meta.env.VITE_API_BASE as string) || 'https://back.ramilawyersys.com/apiAdmin';
export const OLD_APP = (import.meta.env.VITE_OLD_APP as string) || 'https://web.ramilawyersys.com';
export const IS_DEMO = String(import.meta.env.VITE_DEMO ?? 'false') === 'true';
