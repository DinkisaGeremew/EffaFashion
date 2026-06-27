import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { useEffect } from 'react';

import Navbar        from './components/Navbar';
import Footer        from './components/Footer';
import Home          from './pages/Home';
import Products      from './pages/Products';
import ProductDetail from './pages/ProductDetail';
import Cart          from './pages/Cart';
import Checkout      from './pages/Checkout';
import OrderSuccess  from './pages/OrderSuccess';
import Orders        from './pages/Orders';
import Wishlist      from './pages/Wishlist';
import Login         from './pages/Login';
import Register      from './pages/Register';
import Profile       from './pages/Profile';

import AdminLayout   from './pages/admin/AdminLayout';
import Dashboard     from './pages/admin/Dashboard';
import AdminProducts from './pages/admin/AdminProducts';
import AdminOrders   from './pages/admin/AdminOrders';
import AdminUsers    from './pages/admin/AdminUsers';
import AdminCoupons  from './pages/admin/AdminCoupons';

import useAuthStore    from './store/useAuthStore';
import useCartStore    from './store/useCartStore';
import useWishlistStore from './store/useWishlistStore';

// Layout with Navbar + Footer
function PublicLayout() {
  return (
    <>
      <Navbar />
      <main><Outlet /></main>
      <Footer />
    </>
  );
}

// Protected route
function Protected({ role }) {
  const user = useAuthStore((s) => s.user);
  if (!user) return <Navigate to="/login" replace />;
  if (role === 'admin' && user.role !== 'admin') return <Navigate to="/" replace />;
  return <Outlet />;
}

export default function App() {
  const { user }       = useAuthStore();
  const fetchCart      = useCartStore((s) => s.fetchCart);
  const fetchWishlist  = useWishlistStore((s) => s.fetchWishlist);

  useEffect(() => {
    if (user) { fetchCart(); fetchWishlist(); }
  }, [user]);

  return (
    <BrowserRouter>
      <Toaster position="top-right" toastOptions={{ duration: 3000, style: { fontFamily: 'Poppins, sans-serif', fontSize: 14 } }} />
      <Routes>
        {/* Auth pages — no navbar */}
        <Route path="/login"    element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Admin — protected */}
        <Route element={<Protected role="admin" />}>
          <Route path="/admin" element={<AdminLayout><Dashboard /></AdminLayout>} />
          <Route path="/admin/products" element={<AdminLayout><AdminProducts /></AdminLayout>} />
          <Route path="/admin/orders"   element={<AdminLayout><AdminOrders /></AdminLayout>} />
          <Route path="/admin/users"    element={<AdminLayout><AdminUsers /></AdminLayout>} />
          <Route path="/admin/coupons"  element={<AdminLayout><AdminCoupons /></AdminLayout>} />
        </Route>

        {/* Public pages */}
        <Route element={<PublicLayout />}>
          <Route path="/"                element={<Home />} />
          <Route path="/products"        element={<Products />} />
          <Route path="/products/:slug"  element={<ProductDetail />} />
          <Route path="/cart"            element={<Cart />} />
          <Route path="/wishlist"        element={<Wishlist />} />

          {/* Protected customer routes */}
          <Route element={<Protected />}>
            <Route path="/checkout"      element={<Checkout />} />
            <Route path="/order-success" element={<OrderSuccess />} />
            <Route path="/orders"        element={<Orders />} />
            <Route path="/profile"       element={<Profile />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
