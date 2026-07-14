/**
 * Authentication Hook
 * Provides login, logout, user state, and token management.
 * Ref: 02-FEATURE-SPEC.md §1, 09a-STATE-MANAGEMENT.md
 */

import { useState, useEffect } from 'react';
import { api } from '../lib/api';

export function useAuth() {
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      api.me()
        .then((res) => setUser(res.data))
        .catch(() => {
          localStorage.removeItem('auth_token');
          setUser(null);
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (email: string, password: string) => {
    const response = await api.login(email, password);
    const userData = response.data.user;
    setUser(userData);
    return response;
  };

  const logout = async () => {
    await api.logout();
    setUser(null);
  };

  return { user, loading, login, logout };
}