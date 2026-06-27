import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '../api';

const useAuthStore = create(
  persist(
    (set) => ({
      user:  null,
      token: null,
      setAuth: (user, token) => {
        localStorage.setItem('token', token);
        set({ user, token });
      },
      logout: () => {
        localStorage.removeItem('token');
        set({ user: null, token: null });
      },
      refreshUser: async () => {
        try {
          const { data } = await api.get('/auth/me');
          set({ user: data });
        } catch {
          localStorage.removeItem('token');
          set({ user: null, token: null });
        }
      },
    }),
    { name: 'auth-store', partialize: (s) => ({ user: s.user, token: s.token }) }
  )
);

export default useAuthStore;
