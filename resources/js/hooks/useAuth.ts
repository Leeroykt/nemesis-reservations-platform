/**
 * Authentication Hook
 * Provides login, logout, user state.
 * Ref: 02-FEATURE-SPEC.md §1, 09a-STATE-MANAGEMENT.md
 */

import { useState, useEffect } from 'react';
import { api, User, LoginResponse } from '@/lib/api';

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.me()
      .then((userData: User) => {
        setUser(userData);
      })
      .catch(() => {
        setUser(null);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  const login = async (email: string, password: string): Promise<User> => {
    const response: LoginResponse = await api.login(email, password);
    const userData = response.user;
    setUser(userData);
    return userData;
  };

  const logout = async (): Promise<void> => {
    await api.logout();
    setUser(null);
  };

  return { user, loading, login, logout };
}