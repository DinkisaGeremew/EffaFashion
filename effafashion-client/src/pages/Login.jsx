import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import useAuthStore from '../store/useAuthStore';
import useCartStore from '../store/useCartStore';
import useWishlistStore from '../store/useWishlistStore';
import api from '../api';
import toast from 'react-hot-toast';

export default function Login() {
  const [form, setForm]     = useState({ email: '', password: '' });
  const [loading, setLoading] = useState(false);
  const { setAuth }         = useAuthStore();
  const fetchCart           = useCartStore((s) => s.fetchCart);
  const fetchWishlist       = useWishlistStore((s) => s.fetchWishlist);
  const navigate            = useNavigate();
  const [params]            = useSearchParams();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const { data } = await api.post('/auth/login', form);
      setAuth(data.user, data.token);
      await Promise.all([fetchCart(), fetchWishlist()]);
      toast.success(`Welcome back, ${data.user.full_name}!`);
      navigate(params.get('redirect') || (data.user.role === 'admin' ? '/admin' : '/'));
    } catch (err) {
      toast.error(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ minHeight: '100vh', display: 'grid', gridTemplateColumns: '1fr 1fr' }}>
      {/* Left */}
      <div style={{ background: '#000', position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 60 }}>
        <div style={{ position: 'absolute', inset: 0, backgroundImage: "url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&fit=crop')", backgroundSize: 'cover', backgroundPosition: 'center', opacity: 0.2 }} />
        <div style={{ position: 'relative', zIndex: 1, color: '#fff', maxWidth: 380 }}>
          <Link to="/" style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, display: 'block', marginBottom: 48 }}>
            <span style={{ color: '#D4AF37' }}>EFFA</span><span>FASHION</span>
          </Link>
          <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 38, lineHeight: 1.2, marginBottom: 20 }}>
            Welcome<br />Back to <span style={{ color: '#D4AF37' }}>Luxury</span>
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.55)', lineHeight: 1.9, fontSize: 15 }}>
            Sign in to access your exclusive fashion collection, track your orders, and enjoy a personalised experience.
          </p>
        </div>
      </div>

      {/* Right */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px 60px', background: '#fff' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 6 }}>Sign In</h2>
          <p style={{ color: '#999', marginBottom: 32, fontSize: 14 }}>Enter your credentials to access your account</p>

          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label className="form-label">Email Address <span className="required">*</span></label>
              <input className="form-control" type="email" placeholder="your@email.com"
                value={form.email} onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required />
            </div>
            <div className="form-group">
              <label className="form-label">Password <span className="required">*</span></label>
              <input className="form-control" type="password" placeholder="Your password"
                value={form.password} onChange={e => setForm(f => ({ ...f, password: e.target.value }))} required />
            </div>
            <button type="submit" className="btn btn-gold btn-block btn-lg" disabled={loading} style={{ marginTop: 8 }}>
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <p style={{ textAlign: 'center', marginTop: 24, fontSize: 14, color: '#999' }}>
            Don't have an account? <Link to="/register" style={{ color: '#D4AF37', fontWeight: 600 }}>Create one free →</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
