import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api';
import { formatPrice } from '../utils/helpers';

const STATUS_STYLE = {
  pending:    'status-pending',    processing: 'status-processing',
  shipped:    'status-shipped',    delivered:  'status-delivered',
  cancelled:  'status-cancelled',  refunded:   'status-refunded',
};

export default function Orders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selected, setSelected] = useState(null);

  useEffect(() => {
    api.get('/orders').then(r => setOrders(r.data)).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="spinner-center" style={{ marginTop: 120 }}><div className="spinner" /></div>;

  return (
    <div style={{ paddingTop: 90 }}>
      <div style={{ background: '#000', padding: '40px 0' }}>
        <div className="container"><h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 36 }}>My Orders</h1></div>
      </div>

      <div className="container" style={{ padding: '40px 20px' }}>
        {orders.length === 0 ? (
          <div className="text-center" style={{ padding: 80 }}>
            <div style={{ fontSize: 64, marginBottom: 20 }}>📦</div>
            <h2 style={{ fontFamily: "'Playfair Display',serif", marginBottom: 12 }}>No orders yet</h2>
            <Link to="/products" className="btn btn-gold btn-lg">Start Shopping</Link>
          </div>
        ) : (
          <div style={{ display: 'grid', gap: 16 }}>
            {orders.map(o => (
              <div key={o.id} style={{ background: '#fff', border: '1px solid #eee', borderRadius: 12, padding: 24, cursor: 'pointer' }}
                onClick={() => setSelected(selected?.id === o.id ? null : o)}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12 }}>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: 16, color: '#D4AF37', marginBottom: 4 }}>{o.order_number}</div>
                    <div style={{ fontSize: 13, color: '#999' }}>{new Date(o.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                  </div>
                  <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
                    <span className={`status-badge ${STATUS_STYLE[o.status] || 'status-pending'}`}>{o.status}</span>
                    <span style={{ fontWeight: 700, fontSize: 16 }}>{formatPrice(o.total_amount)}</span>
                  </div>
                </div>

                {selected?.id === o.id && (
                  <div style={{ marginTop: 20, paddingTop: 20, borderTop: '1px solid #f0f0f0' }}>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20, marginBottom: 16 }}>
                      <div>
                        <div style={{ fontSize: 12, color: '#999', fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 8 }}>Shipping To</div>
                        <div style={{ fontSize: 14 }}>{o.shipping_name}</div>
                        <div style={{ fontSize: 14, color: '#666' }}>{o.shipping_address}, {o.shipping_city}</div>
                        <div style={{ fontSize: 14, color: '#666' }}>{o.shipping_phone}</div>
                      </div>
                      <div>
                        <div style={{ fontSize: 12, color: '#999', fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 8 }}>Payment</div>
                        <div style={{ fontSize: 14, textTransform: 'capitalize' }}>{o.payment_method?.replace('_', ' ')}</div>
                        <span className={`status-badge ${o.payment_status === 'paid' ? 'status-delivered' : 'status-pending'}`}>{o.payment_status}</span>
                      </div>
                    </div>
                    {o.notes && <div style={{ fontSize: 13, color: '#666', background: '#f9f9f9', padding: 12, borderRadius: 6 }}>Note: {o.notes}</div>}
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
