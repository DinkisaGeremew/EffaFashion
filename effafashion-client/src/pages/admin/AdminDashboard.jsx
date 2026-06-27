import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { FiShoppingCart, FiBox, FiUsers, FiDollarSign } from 'react-icons/fi';
import api from '../../api';
import { formatPrice } from '../../utils/helpers';

const StatCard = ({ label, value, icon: Icon, color }) => (
  <div style={{ background: '#fff', borderRadius: 12, padding: 24, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', display: 'flex', alignItems: 'center', gap: 20 }}>
    <div style={{ width: 56, height: 56, borderRadius: 12, background: color + '20', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
      <Icon size={24} color={color} />
    </div>
    <div>
      <div style={{ fontSize: 24, fontWeight: 700 }}>{value}</div>
      <div style={{ fontSize: 13, color: '#999', marginTop: 2 }}>{label}</div>
    </div>
  </div>
);

export default function AdminDashboard() {
  const [stats, setStats]     = useState(null);
  const [orders, setOrders]   = useState([]);
  const [top, setTop]         = useState([]);

  useEffect(() => {
    Promise.all([
      api.get('/admin/stats'),
      api.get('/orders/admin/all?limit=8'),
      api.get('/admin/top-products'),
    ]).then(([s, o, t]) => {
      setStats(s.data);
      setOrders(o.data.orders);
      setTop(t.data);
    });
  }, []);

  const statusStyle = (s) => {
    const map = { pending:'#856404,#fff3cd', processing:'#0c5460,#d1ecf1', shipped:'#004085,#cce5ff', delivered:'#155724,#d4edda', cancelled:'#721c24,#f8d7da' };
    const [color, bg] = (map[s] || '').split(',');
    return { color, background: bg, padding: '3px 10px', borderRadius: 20, fontSize: 11, fontWeight: 600 };
  };

  if (!stats) return <div className="spinner-center"><div className="spinner" /></div>;

  return (
    <div>
      <h1 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 28 }}>Dashboard</h1>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 20, marginBottom: 32 }}>
        <StatCard label="Total Orders"    value={stats.total_orders}    icon={FiShoppingCart} color="#D4AF37" />
        <StatCard label="Total Revenue"   value={formatPrice(stats.total_revenue)}  icon={FiDollarSign}  color="#28a745" />
        <StatCard label="Active Products" value={stats.total_products}  icon={FiBox}          color="#17a2b8" />
        <StatCard label="Customers"       value={stats.total_users}     icon={FiUsers}        color="#dc3545" />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '3fr 2fr', gap: 24 }}>
        {/* Recent Orders */}
        <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
          <div style={{ padding: '20px 24px', borderBottom: '1px solid #f0f0f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 18 }}>Recent Orders</h3>
            <Link to="/admin/orders" style={{ fontSize: 13, color: '#D4AF37', fontWeight: 600 }}>View All</Link>
          </div>
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead><tr style={{ background: '#f9f9f9' }}>
              {['Order #','Customer','Amount','Status','Date'].map(h => (
                <th key={h} style={{ padding: '10px 16px', textAlign: 'left', fontSize: 12, fontWeight: 600, color: '#999', letterSpacing: 0.5 }}>{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {orders.map(o => (
                <tr key={o.id} style={{ borderBottom: '1px solid #f5f5f5' }}>
                  <td style={{ padding: '12px 16px', color: '#D4AF37', fontWeight: 600, fontSize: 13 }}>{o.order_number}</td>
                  <td style={{ padding: '12px 16px', fontSize: 14 }}>{o.full_name}</td>
                  <td style={{ padding: '12px 16px', fontWeight: 700, fontSize: 14 }}>{formatPrice(o.total_amount)}</td>
                  <td style={{ padding: '12px 16px' }}><span style={statusStyle(o.status)}>{o.status}</span></td>
                  <td style={{ padding: '12px 16px', fontSize: 12, color: '#999' }}>{new Date(o.created_at).toLocaleDateString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Top Products */}
        <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
          <div style={{ padding: '20px 24px', borderBottom: '1px solid #f0f0f0' }}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 18 }}>Top Products</h3>
          </div>
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead><tr style={{ background: '#f9f9f9' }}>
              {['Product','Sold','Revenue'].map(h => (
                <th key={h} style={{ padding: '10px 16px', textAlign: 'left', fontSize: 12, fontWeight: 600, color: '#999' }}>{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {top.length ? top.map((t, i) => (
                <tr key={i} style={{ borderBottom: '1px solid #f5f5f5' }}>
                  <td style={{ padding: '12px 16px', fontSize: 13, fontWeight: 600 }}>{t.name}</td>
                  <td style={{ padding: '12px 16px', fontSize: 14 }}>{t.sold}</td>
                  <td style={{ padding: '12px 16px', color: '#D4AF37', fontWeight: 700, fontSize: 14 }}>{formatPrice(t.revenue)}</td>
                </tr>
              )) : (
                <tr><td colSpan={3} style={{ padding: 30, textAlign: 'center', color: '#999', fontSize: 14 }}>No sales data yet</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
