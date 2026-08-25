import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';

import { api, bindAuthHooks } from './api';
import type { ApiUser, AuthSession } from './types';

const TOKEN_KEY = 'cfts_token';
const USER_KEY = 'cfts_user';

type RegisterPayload = {
  firstname: string;
  middlename?: string;
  lastname: string;
  email: string;
  password: string;
  password_confirmation: string;
  birthday?: string;
  cellphone_no?: string;
  address?: string;
};

type AuthContextValue = {
  ready: boolean;
  token: string | null;
  user: ApiUser | null;
  login: (email: string, password: string) => Promise<void>;
  register: (payload: RegisterPayload) => Promise<void>;
  logout: () => Promise<void>;
  setUser: (user: ApiUser) => void;
};

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [ready, setReady] = useState(false);
  const [token, setToken] = useState<string | null>(null);
  const tokenRef = useRef<string | null>(null);
  const [user, setUserState] = useState<ApiUser | null>(null);

  const persistSession = useCallback(async (session: AuthSession) => {
    tokenRef.current = session.token;
    setToken(session.token);
    setUserState(session.user);
    await SecureStore.setItemAsync(TOKEN_KEY, session.token);
    await SecureStore.setItemAsync(USER_KEY, JSON.stringify(session.user));
  }, []);

  const clearSession = useCallback(async () => {
    tokenRef.current = null;
    setToken(null);
    setUserState(null);
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    await SecureStore.deleteItemAsync(USER_KEY);
  }, []);

  useEffect(() => {
    (async () => {
      try {
        const storedToken = await SecureStore.getItemAsync(TOKEN_KEY);
        const storedUser = await SecureStore.getItemAsync(USER_KEY);

        if (storedToken) {
          tokenRef.current = storedToken;
          setToken(storedToken);
          if (storedUser) {
            try {
              setUserState(JSON.parse(storedUser) as ApiUser);
            } catch {}
          }
        }
      } catch {
      } finally {
        setReady(true);
      }
    })();
  }, []);

  useEffect(() => {
    bindAuthHooks(
      () => Promise.resolve(tokenRef.current),
      () => {
        void clearSession();
      }
    );
  }, [clearSession]);

  const login = useCallback(
    async (email: string, password: string) => {
      const response = await api.post<ApiEnvelope<AuthSession>>('/auth/login', { email, password });
      await persistSession(response.data.data);
    },
    [persistSession]
  );

  const register = useCallback(
    async (payload: RegisterPayload) => {
      const response = await api.post<ApiEnvelope<AuthSession>>('/auth/register', payload);
      await persistSession(response.data.data);
    },
    [persistSession]
  );

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch {}
    await clearSession();
  }, [clearSession]);

  const value = useMemo(
    () => ({
      ready,
      token,
      user,
      login,
      register,
      logout,
      setUser: setUserState,
    }),
    [ready, token, user, login, register, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);

  if (!ctx) {
    throw new Error('useAuth must be used inside <AuthProvider>');
  }

  return ctx;
}

type ApiEnvelope<T> = { success: boolean; data: T };
