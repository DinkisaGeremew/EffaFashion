import { Link, useLocation, useNavigate } from 'react-router-dom';
import { FiGrid, FiBox, FiShoppingCart, FiUsers, FiTag, FiLogOut, FiMenu, FiX } from 'react-icons/fi';
import { useState } from 'react';
import useAuthStore from '../../store/useAuthStore';

const NAV = [
  { to: '/admin',          icon: FiGrid,         label: 'Dashboard'  },
  { to: '/admin/products', icon: FiBox,           label: 'Products'   },
  { to: '/admin/orders',   icon: FiShoppingCart,  label: 'Orders'     },
  { to: '/admin/users',    icon: FiUsers,         label: 'Users'      },
  { to: '/admin/coupons',  icon: FiTag,           label: 'Coupons'    },
];

export default function AdminLayout({ children }) {
  const { pathname }   = useLocation();
  const { logout }     = useAuthStore();
  const navigate       = useNavigate();
  const [open, setOpen] = useState(false);

  const handleLogout = () => { logout(); navigate('/login'); };

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: '#f5f5f5' }}>
      {/* Sidebar */}
      <aside style={{ width: 240, background: '#000', color: '#fff', display: 'flex', flexDirection: 'column', position: 'fixed', top: 0, bottom: 0, left: 0, zIndex: 200, transform: open ? 'translateX(0)' : undefined }}>
        <div style={{ padding: '24px 20px', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
          <Link to="/" style={{ fontFamily: "'Playfair Display',serif", fontSize: 20 }}>
            <span style={{ color: '#D4AF37' }}>EFFA</span><span>FASHION</span>
          </Link>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.35)', marginTop: 4, letterSpacing: 1, textTransform: 'uppercase' }}>Admin Panel</div>
        </div>

        <nav style={{ flex: 1, padding: '16px 0' }}>
          {NAV.map(({ to, icon: Icon, label }) => {
            const active = pathname === to || (to !== '/admin' && pathname.startsWith(to));
            return (
              <Link key={to} to={to}
                style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 20px', color: active ? '#D4AF37' : 'rgba(255,255,255,0.65)', background: active ? 'rgba(212,175,55,0.1)' : 'transparent', borderLeft: active ? '3px solid #D4AF37' : '3px solid transparent', fontWeight: active ? 600 : 400, fontSize: 14, transition: 'all 0.2s' }}>
                <Icon size={18} /> {label}
              </Link>
            );
          })}
        </nav>

        <button onClick={handleLogout}
          style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '16px 20px', background: 'none', border: 'none', color: 'rgba(255,255,255,0.5)', cursor: 'pointer', fontSize: 14, borderTop: '1px solid rgba(255,255,255,0.08)' }}>
          <FiLogOut size={18} /> Logout
        </button>
      </aside>

      {/* Main */}
      <div style={{ marginLeft: 240, flex: 1, display: 'flex', flexDirection: 'column' }}>
        <header style={{ background: '#fff', padding: '0 28px', height: 64, display: 'flex', alignItems: 'center', justifyContent: 'space-between', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', position: 'sticky', top: 0, zIndex: 100 }}>
          <h2 style={{ fontSize: 18, fontWeight: 600, color: '#111' }}>
            {NAV.find(n => n.to === pathname || (n.to !== '/admin' && pathname.startsWith(n.to)))?.label || 'Admin'}
          </h2>
          <Link to="/" style={{ fontSize: 13, color: '#D4AF37', fontWeight: 600 }}>← View Site</Link>
        </header>
        <main style={{ flex: 1, padding: 28 }}>{children}</main>
      </div>
    </div>
  );
}
