import { create } from 'zustand';
import api from '../api';
import toast from 'react-hot-toast';

const useWishlistStore = create((set, get) => ({
  items: [],

  fetchWishlist: async () => {
    const token = localStorage.getItem('token');
    if (!token) return;
    try {
      const { data } = await api.get('/wishlist');
      set({ items: data });
    } catch {}
  },

  toggle: async (product_id) => {
    const token = localStorage.getItem('token');
    if (!token) { toast.error('Please login first'); return; }
    try {
      const { data } = await api.post('/wishlist/toggle', { product_id });
      if (data.status === 'added') {
        toast.success('Added to wishlist');
      } else {
        toast.success('Removed from wishlist');
        set((s) => ({ items: s.items.filter((i) => i.id !== product_id) }));
      }
      await get().fetchWishlist();
    } catch {}
  },

  isInWishlist: (id) => get().items.some((i) => i.id === id),
}));

export default useWishlistStore;
