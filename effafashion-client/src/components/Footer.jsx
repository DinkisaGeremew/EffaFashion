import { Link } from 'react-router-dom';
import { FiInstagram, FiTwitter, FiFacebook, FiMail, FiPhone, FiMapPin } from 'react-icons/fi';
import { useState } from 'react';
import api from '../api';
import toast from 'react-hot-toast';

export default function Footer() {
  const [email, setEmail] = useState('');

  const subscribe = async (e) => {
    e.preventDefault();
    try {
      await api.post('/newsletter', { email });
      toast.success('Subscribed!');
      setEmail('');
    } catch { toast.error('Already subscribed or invalid email'); }
  };

  return (
    <footer style={{ background: '#000', color: 'rgba(255,255,255,0.7)', paddingTop: 60 }}>
      <div className="container">
        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1.5fr', gap: 40, paddingBottom: 40, borderBottom: '1px solid rgba(255,255,255,0.1)' }}>

          <div>
            <div style={{ fontFamily: "'Playfair Display',serif", fontSize: 24, marginBottom: 16 }}>
              <span style={{ color: '#D4AF37' }}>EFFA</span><span style={{ color: '#fff' }}>FASHION</span>
            </div>
            <p style={{ fontSize: 14, lineHeight: 1.8, marginBottom: 20 }}>
              Premium luxury fashion for the modern individual who values elegance and sophistication.
            </p>
            <div style={{ display: 'flex', gap: 12 }}>
              {[FiInstagram, FiTwitter, FiFacebook].map((Icon, i) => (
                <a key={i} href="#" style={{ width: 38, height: 38, borderRadius: '50%', background: 'rgba(212,175,55,0.15)', border: '1px solid rgba(212,175,55,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#D4AF37', transition: 'all 0.2s' }}>
                  <Icon size={16} />
                </a>
              ))}
            </div>
          </div>

          <div>
            <h4 style={{ color: '#D4AF37', fontSize: 13, letterSpacing: 2, textTransform: 'uppercase', marginBottom: 20 }}>Shop</h4>
            {['Women', 'Men', 'Accessories', 'New Arrivals', 'Sale'].map((l) => (
              <Link key={l} to="/products" style={{ display: 'block', fontSize: 14, marginBottom: 10, transition: 'color 0.2s' }}
                onMouseOver={(e) => e.target.style.color = '#D4AF37'}
                onMouseOut={(e) => e.target.style.color = 'rgba(255,255,255,0.7)'}>
                {l}
              </Link>
            ))}
          </div>

          <div>
            <h4 style={{ color: '#D4AF37', fontSize: 13, letterSpacing: 2, textTransform: 'uppercase', marginBottom: 20 }}>Info</h4>
            {[['About', '/about'], ['Contact', '/contact'], ['My Orders', '/orders'], ['Profile', '/profile']].map(([l, h]) => (
              <Link key={l} to={h} style={{ display: 'block', fontSize: 14, marginBottom: 10, transition: 'color 0.2s' }}
                onMouseOver={(e) => e.target.style.color = '#D4AF37'}
                onMouseOut={(e) => e.target.style.color = 'rgba(255,255,255,0.7)'}>
                {l}
              </Link>
            ))}
          </div>

          <div>
            <h4 style={{ color: '#D4AF37', fontSize: 13, letterSpacing: 2, textTransform: 'uppercase', marginBottom: 20 }}>Newsletter</h4>
            <p style={{ fontSize: 13, marginBottom: 16 }}>Get 20% off your first order.</p>
            <form onSubmit={subscribe} style={{ display: 'flex', gap: 8 }}>
              <input value={email} onChange={(e) => setEmail(e.target.value)}
                type="email" placeholder="Your email" required
                style={{ flex: 1, padding: '10px 14px', background: 'rgba(255,255,255,0.08)', border: '1px solid rgba(255,255,255,0.15)', borderRadius: 6, color: '#fff', fontSize: 13, outline: 'none' }} />
              <button type="submit" className="btn btn-gold btn-sm">Join</button>
            </form>
            <div style={{ marginTop: 20, display: 'flex', flexDirection: 'column', gap: 10, fontSize: 13 }}>
              <span style={{ display: 'flex', alignItems: 'center', gap: 8 }}><FiMapPin size={14} color="#D4AF37" /> Burayu Dire, Ethiopia</span>
              <span style={{ display: 'flex', alignItems: 'center', gap: 8 }}><FiMail size={14} color="#D4AF37" /> info@effafashion.com</span>
            </div>
          </div>
        </div>

        <div style={{ textAlign: 'center', padding: '20px 0', fontSize: 13, color: 'rgba(255,255,255,0.35)' }}>
          &copy; {new Date().getFullYear()} EffaFashion. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
