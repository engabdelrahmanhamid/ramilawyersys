import { useEffect, useState } from 'react';

// Mirrors the old app's 500ms debounce on search inputs so we don't hammer the API.
export function useDebounced<T>(value: T, delay = 400): T {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const id = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(id);
  }, [value, delay]);
  return debounced;
}
