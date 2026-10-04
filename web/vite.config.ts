import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

// The production API allows simple GETs cross-origin but rejects the CORS preflight
// that a JSON POST (login, create, update) triggers from another origin. In dev we
// therefore proxy /apiAdmin through the Vite server so the browser only ever talks to
// localhost (same-origin, no preflight) and Vite forwards the request server-side.
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const apiBase = env.VITE_API_BASE || 'https://back.ramilawyersys.com/apiAdmin';
  const target = new URL(apiBase).origin;

  return {
    plugins: [react()],
    build: { outDir: 'dist', sourcemap: false },
    server: {
      proxy: {
        '/apiAdmin': { target, changeOrigin: true, secure: true },
      },
    },
  };
});
