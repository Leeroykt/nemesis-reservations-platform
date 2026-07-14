import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

export function useApi<T = any>(url: string, params?: Record<string, any>) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const response = await api.get<T>(url, params);
        setData(response.data);
      } catch (err: any) {
        setError(err.message || 'An error occurred.');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [url, JSON.stringify(params)]);

  return { data, loading, error };
}