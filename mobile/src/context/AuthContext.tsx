import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { AccountApi, AuthApi } from '../api';
import { getToken, setToken } from '../api/client';
import type { Me } from '../api/types';

type AuthState = {
  booting: boolean;
  me: Me | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshMe: () => Promise<void>;
};

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [booting, setBooting] = useState(true);
  const [me, setMe] = useState<Me | null>(null);

  const refreshMe = useCallback(async () => {
    const profile = await AccountApi.me();
    setMe(profile);
  }, []);

  useEffect(() => {
    (async () => {
      const token = await getToken();
      if (token) {
        try {
          await refreshMe();
        } catch {
          await setToken(null);
        }
      }
      setBooting(false);
    })();
  }, [refreshMe]);

  const login = useCallback(async (email: string, password: string) => {
    const { token } = await AuthApi.login(email, password);
    await setToken(token);
    await refreshMe();
  }, [refreshMe]);

  const logout = useCallback(async () => {
    try {
      await AuthApi.logout();
    } catch {
      // token may already be invalid — still clear local state
    }
    await setToken(null);
    setMe(null);
  }, []);

  return (
    <AuthContext.Provider value={{ booting, me, login, logout, refreshMe }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
