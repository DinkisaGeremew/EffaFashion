import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import useAuthStore from '../store/useAuthStore';
import api from '../api';
import toast from 'react-hot-toast';

export default function Register() {
  const [form, setForm]       = useState({ full_name: '', email: '', password: '', phone: '' });
  const [loading, setLoading] = useState(false);
  const { setAuth }           = useAuthStore();
  const navigate              = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (form.password.length < 6) { toast.error('Password must be at least 6 characters'); return; }
    setLoading(true);
    try {
      const { data } = await api.post('/auth/register', form);
      setAuth(data.user, data.token);
      toast.success('Account created! Welcome to EffaFashion.');
      navigate('/');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Registration failed');
    } finally { setLoading(false); }
  };

  return (
    <div style={{ minHeight: '100vh', display: 'grid', gridTemplateColumns: '1fr 1fr' }}>
      <div style={{ background: '#000', position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 60 }}>
        <div style={{ position: 'absolute', inset: 0, backgroundImage: "url('https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800&fit=crop')", backgroundSize: 'cover', opacity: 0.2 }} />
        <div style={{ position: 'relative', zIndex: 1, color: '#fff', maxWidth: 380 }}>
          <Link to="/" style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, display: 'block', marginBottom: 48 }}>
            <span style={{ color: '#D4AF37' }}>EFFA</span><span>FASHION</span>
          </Link>
          <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 38, lineHeight: 1.2, marginBottom: 20 }}>
            Join the <span style={{ color: '#D4AF37' }}>Luxury</span><br />Community
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.55)', lineHeight: 1.9, fontSize: 15 }}>
            Create a free account and get 20% off your first order, plus exclusive member deals.
          </p>
        </div>
      </div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px 60px', background: '#fff' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 6 }}>Create Account</h2>
          <p style={{ color: '#999', marginBottom: 32, fontSize: 14 }}>Fill in your details to get started</p>
          <form onSubmit={handleSubmit}>
            {[['full_name','Full Name','text','Your full name'],['email','Email','email','your@email.com'],['phone','Phone','tel','+251 900 000 000'],['password','Password','password','Min. 6 characters']].map(([k, l, t, p]) => (
              <div key={k} className="form-group">
                <label className="form-label">{l} {k !== 'phone' && <span className="required">*</span>}</label>
                <input className="form-control" type={t} placeholder={p}
                  value={form[k]} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))}
                  required={k !== 'phone'} />
              </div>
            ))}
            <button type="submit" className="btn btn-gold btn-block btn-lg" disabled={loading} style={{ marginTop: 8 }}>
              {loading ? 'Creating account...' : 'Create Account'}
            </button>
          </form>
          <p style={{ textAlign: 'center', marginTop: 24, fontSize: 14, color: '#999' }}>
            Already have an account? <Link to="/login" style={{ color: '#D4AF37', fontWeight: 600 }}>Sign in →</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
