export const API_BASE = (import.meta.env.VITE_API_BASE as string) || 'https://demo-back.ramilawyersys.com/apiAdmin';
export const OLD_APP = (import.meta.env.VITE_OLD_APP as string) || 'https://demo.ramilawyersys.com';
export const IS_DEMO = String(import.meta.env.VITE_DEMO ?? 'true') === 'true';
