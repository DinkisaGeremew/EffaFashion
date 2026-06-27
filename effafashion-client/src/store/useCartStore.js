import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '../api';
import toast from 'react-hot-toast';

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      loading: false,

      fetchCart: async () => {
        const token = localStorage.getItem('token');
        if (!token) return;
        try {
          const { data } = await api.get('/cart');
          set({ items: data });
        } catch {}
      },

      addToCart: async (product_id, quantity = 1, size = '', color = '') => {
        const token = localStorage.getItem('token');
        if (!token) { toast.error('Please login to add to cart'); return; }
        try {
          await api.post('/cart', { product_id, quantity, size, color });
          await get().fetchCart();
          toast.success('Added to cart');
        } catch (err) {
          toast.error(err.response?.data?.message || 'Failed to add');
        }
      },

      updateItem: async (id, quantity) => {
        try {
          await api.put(`/cart/${id}`, { quantity });
          await get().fetchCart();
        } catch {}
      },

      removeItem: async (id) => {
        try {
          await api.delete(`/cart/${id}`);
          set((s) => ({ items: s.items.filter((i) => i.id !== id) }));
          toast.success('Removed from cart');
        } catch {}
      },

      clearCart: () => set({ items: [] }),

      get total() {
        return get().items.reduce((sum, i) => sum + (i.sale_price || i.price) * i.quantity, 0);
      },
      get count() {
        return get().items.reduce((sum, i) => sum + i.quantity, 0);
      },
    }),
    { name: 'cart-store', partialize: (s) => ({ items: s.items }) }
  )
);

export default useCartStore;
