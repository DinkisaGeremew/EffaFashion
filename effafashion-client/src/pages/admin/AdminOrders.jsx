import { useEffect, useState } from 'react';
import api from '../../api';
import { formatPrice } from '../../utils/helpers';
import toast from 'react-hot-toast';

const STATUSES  = ['pending','processing','shipped','delivered','cancelled','refunded'];
const PAY_ST    = ['unpaid','paid','refunded'];
const ST_STYLE  = { pending:'status-pending', processing:'status-processing', shipped:'status-shipped', delivered:'status-delivered', cancelled:'status-cancelled', refunded:'status-refunded' };

export default function AdminOrders() {
  const [orders,   setOrders]  = useState([]);
  const [total,    setTotal]   = useState(0);
  const [filter,   setFilter]  = useState('');
  const [page,     setPage]    = useState(1);
  const [selected, setSelected] = useState(null);

  const load = (p = 1, s = filter) => {
    const params = new URLSearchParams({ page: p, limit: 20 });
    if (s) params.set('status', s);
    api.get(`/orders/admin/all?${params}`).then(r => { setOrders(r.data.orders); setTotal(r.data.total); });
  };

  useEffect(() => { load(1, filter); }, [filter]);

  const updateOrder = async (id, status, payment_status) => {
    await api.put(`/orders/admin/${id}`, { status, payment_status });
    toast.success('Order updated');
    load(page, filter);
    setSelected(s => s ? { ...s, status, payment_status } : s);
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
        <h2 style={{ fontSize: 20, fontWeight: 700 }}>Orders ({total})</h2>
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          {['', ...STATUSES].map(s => (
            <button key={s} onClick={() => { setFilter(s); setPage(1); }}
              style={{ padding: '6px 14px', border: '1px solid', borderRadius: 20, cursor: 'pointer', fontSize: 13, background: filter === s ? '#D4AF37' : '#fff', borderColor: filter === s ? '#D4AF37' : '#ddd', color: filter === s ? '#000' : '#555', fontWeight: filter === s ? 700 : 400 }}>
              {s || 'All'}
            </button>
          ))}
        </div>
      </div>

      <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
          <thead>
            <tr style={{ background: '#f9f9f9', borderBottom: '1px solid #eee' }}>
              {['Order #','Customer','Amount','Status','Payment','Date','Action'].map(h => (
                <th key={h} style={{ padding: '12px 16px', textAlign: 'left', fontSize: 12, fontWeight: 700, color: '#999', textTransform: 'uppercase' }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {orders.map(o => (
              <tr key={o.id} style={{ borderBottom: '1px solid #f5f5f5', cursor: 'pointer' }} onClick={() => setSelected(o)}>
                <td style={{ padding: '12px 16px', color: '#D4AF37', fontWeight: 700 }}>{o.order_number}</td>
                <td style={{ padding: '12px 16px' }}>{o.full_name}<div style={{ fontSize: 12, color: '#999' }}>{o.email}</div></td>
                <td style={{ padding: '12px 16px', fontWeight: 600 }}>{formatPrice(o.total_amount)}</td>
                <td style={{ padding: '12px 16px' }}><span className={`status-badge ${ST_STYLE[o.status]||''}`}>{o.status}</span></td>
                <td style={{ padding: '12px 16px' }}><span className={`status-badge ${o.payment_status==='paid'?'status-delivered':'status-pending'}`}>{o.payment_status}</span></td>
                <td style={{ padding: '12px 16px', color: '#999', fontSize: 13 }}>{new Date(o.created_at).toLocaleDateString()}</td>
                <td style={{ padding: '12px 16px' }}>
                  <select value={o.status} onClick={e => e.stopPropagation()}
                    onChange={e => updateOrder(o.id, e.target.value, o.payment_status)}
                    style={{ padding: '6px 10px', border: '1px solid #ddd', borderRadius: 6, fontSize: 13, cursor: 'pointer' }}>
                    {STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Order detail modal */}
      {selected && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 20 }}
          onClick={() => setSelected(null)}>
          <div style={{ background: '#fff', borderRadius: 16, width: '100%', maxWidth: 560, padding: 32, maxHeight: '90vh', overflow: 'auto' }}
            onClick={e => e.stopPropagation()}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 22, marginBottom: 20 }}>{selected.order_number}</h3>
            <div className="grid-2" style={{ marginBottom: 20 }}>
              {[['Customer', selected.full_name],['Email', selected.email],['Phone', selected.shipping_phone],['City', selected.shipping_city],['Address', selected.shipping_address],['Payment', selected.payment_method?.replace('_',' ')]].map(([k,v]) => (
                <div key={k}><div style={{ fontSize: 11, color: '#999', fontWeight: 700, textTransform: 'uppercase', letterSpacing: 1, marginBottom: 4 }}>{k}</div><div style={{ fontSize: 14 }}>{v}</div></div>
              ))}
            </div>
            <div style={{ marginBottom: 20 }}>
              <div style={{ fontSize: 12, color: '#999', fontWeight: 700, textTransform: 'uppercase', marginBottom: 12 }}>Update Status</div>
              <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                <select value={selected.status} onChange={e => updateOrder(selected.id, e.target.value, selected.payment_status)}
                  style={{ padding: '8px 12px', border: '1px solid #ddd', borderRadius: 6, fontSize: 13 }}>
                  {STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
                </select>
                <select value={selected.payment_status} onChange={e => updateOrder(selected.id, selected.status, e.target.value)}
                  style={{ padding: '8px 12px', border: '1px solid #ddd', borderRadius: 6, fontSize: 13 }}>
                  {PAY_ST.map(s => <option key={s} value={s}>{s}</option>)}
                </select>
              </div>
            </div>
            <button className="btn btn-outline btn-sm" onClick={() => setSelected(null)}>Close</button>
          </div>
        </div>
      )}
    </div>
  );
}
