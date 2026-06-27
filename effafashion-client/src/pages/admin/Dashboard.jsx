import { useEffect, useState } from 'react';
import { FiShoppingCart, FiDollarSign, FiBox, FiUsers } from 'react-icons/fi';
import api from '../../api';
import { formatPrice } from '../../utils/helpers';

const STATUS_STYLE = { pending: 'status-pending', processing: 'status-processing', shipped: 'status-shipped', delivered: 'status-delivered', cancelled: 'status-cancelled' };

export default function Dashboard() {
  const [stats,   setStats]   = useState(null);
  const [chart,   setChart]   = useState([]);
  const [top,     setTop]     = useState([]);
  const [orders,  setOrders]  = useState([]);

  useEffect(() => {
    Promise.all([
      api.get('/admin/stats'),
      api.get('/admin/chart'),
      api.get('/admin/top-products'),
      api.get('/orders/admin/all?limit=6'),
    ]).then(([s, c, t, o]) => {
      setStats(s.data); setChart(c.data); setTop(t.data); setOrders(o.data.orders);
    });
  }, []);

  const CARDS = stats ? [
    { icon: FiShoppingCart, color: '#D4AF37', label: 'Total Orders',   value: stats.total_orders,   sub: `${stats.pending_orders} pending` },
    { icon: FiDollarSign,   color: '#28a745', label: 'Total Revenue',  value: formatPrice(stats.total_revenue), sub: `This month: ${formatPrice(stats.monthly_revenue)}` },
    { icon: FiBox,          color: '#17a2b8', label: 'Products',       value: stats.total_products, sub: 'Active' },
    { icon: FiUsers,        color: '#dc3545', label: 'Customers',      value: stats.total_users,    sub: 'Registered' },
  ] : [];

  return (
    <div>
      {/* Stat Cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 20, marginBottom: 28 }}>
        {CARDS.map(({ icon: Icon, color, label, value, sub }) => (
          <div key={label} style={{ background: '#fff', borderRadius: 12, padding: 24, boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
              <div>
                <div style={{ fontSize: 13, color: '#999', marginBottom: 8 }}>{label}</div>
                <div style={{ fontSize: 26, fontWeight: 700, color: '#111', marginBottom: 4 }}>{value}</div>
                <div style={{ fontSize: 12, color: '#999' }}>{sub}</div>
              </div>
              <div style={{ width: 48, height: 48, borderRadius: 12, background: `${color}20`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <Icon size={22} color={color} />
              </div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 24, marginBottom: 24 }}>
        {/* Revenue chart (simple bars) */}
        <div style={{ background: '#fff', borderRadius: 12, padding: 24, boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }}>
          <h3 style={{ fontSize: 16, fontWeight: 600, marginBottom: 20 }}>Revenue — Last 6 Months</h3>
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 10, height: 140 }}>
            {chart.map(({ month, revenue }) => {
              const max = Math.max(...chart.map(c => c.revenue), 1);
              const h   = Math.round((revenue / max) * 120);
              return (
                <div key={month} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
                  <div style={{ fontSize: 10, color: '#999' }}>{formatPrice(revenue).split('.')[0]}</div>
                  <div style={{ width: '100%', height: h, background: 'linear-gradient(to top,#D4AF37,#f0d060)', borderRadius: '4px 4px 0 0', minHeight: 4 }} />
                  <div style={{ fontSize: 10, color: '#aaa' }}>{month.slice(5)}</div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Top Products */}
        <div style={{ background: '#fff', borderRadius: 12, padding: 24, boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }}>
          <h3 style={{ fontSize: 16, fontWeight: 600, marginBottom: 20 }}>Top Products</h3>
          {top.length === 0 ? <p style={{ color: '#999', fontSize: 14 }}>No sales data yet</p> : top.map((p, i) => (
            <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '10px 0', borderBottom: '1px solid #f5f5f5', fontSize: 14 }}>
              <span style={{ fontWeight: 600 }}>{p.name}</span>
              <span style={{ color: '#D4AF37', fontWeight: 600 }}>{p.sold} sold</span>
            </div>
          ))}
        </div>
      </div>

      {/* Recent Orders */}
      <div style={{ background: '#fff', borderRadius: 12, padding: 24, boxShadow: '0 2px 8px rgba(0,0,0,0.06)' }}>
        <h3 style={{ fontSize: 16, fontWeight: 600, marginBottom: 20 }}>Recent Orders</h3>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
          <thead>
            <tr style={{ borderBottom: '2px solid #f0f0f0' }}>
              {['Order #','Customer','Amount','Status','Date'].map(h => (
                <th key={h} style={{ textAlign: 'left', padding: '8px 12px', color: '#999', fontWeight: 600, fontSize: 12, textTransform: 'uppercase', letterSpacing: 0.5 }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {orders.map(o => (
              <tr key={o.id} style={{ borderBottom: '1px solid #f5f5f5' }}>
                <td style={{ padding: '12px', color: '#D4AF37', fontWeight: 700 }}>{o.order_number}</td>
                <td style={{ padding: '12px' }}>{o.full_name}</td>
                <td style={{ padding: '12px', fontWeight: 600 }}>{formatPrice(o.total_amount)}</td>
                <td style={{ padding: '12px' }}><span className={`status-badge ${STATUS_STYLE[o.status] || ''}`}>{o.status}</span></td>
                <td style={{ padding: '12px', color: '#999' }}>{new Date(o.created_at).toLocaleDateString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
