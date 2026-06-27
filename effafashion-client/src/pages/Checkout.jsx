import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useCartStore from '../store/useCartStore';
import useAuthStore from '../store/useAuthStore';
import { formatPrice } from '../utils/helpers';
import api from '../api';
import toast from 'react-hot-toast';

const PAYMENT_METHODS = [
  { id: 'bank_transfer', label: 'Bank Transfer (CBE)', details: { 'Bank': 'Commercial Bank of Ethiopia', 'Account': '1000123456789', 'Name': 'EffaFashion PLC' } },
  { id: 'telebirr',      label: 'Telebirr',            details: { 'Number': '+251 900 000 000', 'Name': 'EffaFashion' } },
  { id: 'crypto',        label: 'Crypto (USDT/BTC)',   details: { 'USDT (TRC20)': 'TXxxxxxxxxxxxxxxxxxxxxxxxxxx', 'BTC': 'bc1qxxxxxxxxxxxxxxxxxxxxxx' } },
];

export default function Checkout() {
  const { items, clearCart } = useCartStore();
  const { user }             = useAuthStore();
  const navigate             = useNavigate();
  const [method, setMethod]  = useState('bank_transfer');
  const [file, setFile]      = useState(null);
  const [loading, setLoading] = useState(false);
  const [form, setForm]      = useState({
    shipping_name:    user?.full_name || '',
    shipping_email:   user?.email    || '',
    shipping_phone:   user?.phone    || '',
    shipping_address: user?.address  || '',
    shipping_city:    user?.city     || '',
    shipping_country: 'Ethiopia',
    notes: '',
  });

  const subtotal = items.reduce((s, i) => s + (i.sale_price || i.price) * i.quantity, 0);
  const shipping = subtotal >= 20000 ? 0 : 2500;
  const total    = subtotal + shipping;

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!file) { toast.error('Please upload your payment screenshot'); return; }
    setLoading(true);
    try {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => fd.append(k, v));
      fd.append('payment_method', method);
      fd.append('payment_screenshot', file);
      const { data } = await api.post('/orders', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      clearCart();
      navigate(`/order-success?order=${data.order_number}`);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to place order');
    } finally {
      setLoading(false);
    }
  };

  const selectedMethod = PAYMENT_METHODS.find(m => m.id === method);

  return (
    <div style={{ paddingTop: 90 }}>
      <div style={{ background: '#000', padding: '40px 0' }}>
        <div className="container"><h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 36 }}>Checkout</h1></div>
      </div>
      <div className="container" style={{ padding: '40px 20px' }}>
        <form onSubmit={handleSubmit}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 360px', gap: 32 }}>

            {/* Left */}
            <div>
              {/* Shipping */}
              <div style={{ background: '#fff', border: '1px solid #eee', borderRadius: 12, padding: 28, marginBottom: 24 }}>
                <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 20, marginBottom: 20 }}>Shipping Information</h3>
                <div className="grid-2">
                  {[['shipping_name','Full Name'],['shipping_email','Email'],['shipping_phone','Phone'],['shipping_city','City'],['shipping_address','Address'],['shipping_country','Country']].map(([k, l]) => (
                    <div key={k} className="form-group" style={k === 'shipping_address' ? { gridColumn: '1/-1' } : {}}>
                      <label className="form-label">{l} <span className="required">*</span></label>
                      {k === 'shipping_address'
                        ? <textarea className="form-control" rows={2} value={form[k]} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))} required />
                        : <input className="form-control" value={form[k]} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))} required />
                      }
                    </div>
                  ))}
                </div>
                <div className="form-group">
                  <label className="form-label">Order Notes (optional)</label>
                  <textarea className="form-control" rows={2} value={form.notes} onChange={e => setForm(f => ({ ...f, notes: e.target.value }))} />
                </div>
              </div>

              {/* Payment */}
              <div style={{ background: '#fff', border: '1px solid #eee', borderRadius: 12, padding: 28 }}>
                <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 20, marginBottom: 20 }}>Payment Method</h3>
                <div style={{ display: 'flex', gap: 12, marginBottom: 20, flexWrap: 'wrap' }}>
                  {PAYMENT_METHODS.map(m => (
                    <button key={m.id} type="button" onClick={() => setMethod(m.id)}
                      style={{ padding: '12px 20px', border: `2px solid ${method === m.id ? '#D4AF37' : '#ddd'}`, borderRadius: 8, background: method === m.id ? 'rgba(212,175,55,0.08)' : '#fff', fontWeight: 600, cursor: 'pointer', color: method === m.id ? '#000' : '#555' }}>
                      {m.label}
                    </button>
                  ))}
                </div>

                {/* Payment details */}
                <div style={{ background: '#f9f9f9', borderRadius: 8, padding: 20, marginBottom: 20 }}>
                  <div style={{ fontWeight: 700, marginBottom: 12, color: '#D4AF37' }}>Send {formatPrice(total)} to:</div>
                  {Object.entries(selectedMethod.details).map(([k, v]) => (
                    <div key={k} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, padding: '6px 0', borderBottom: '1px solid #eee' }}>
                      <span style={{ color: '#666' }}>{k}</span>
                      <strong style={{ wordBreak: 'break-all', maxWidth: '60%', textAlign: 'right' }}>{v}</strong>
                    </div>
                  ))}
                </div>

                {/* Screenshot upload */}
                <div className="form-group">
                  <label className="form-label">Upload Payment Screenshot <span className="required">*</span></label>
                  <input type="file" accept="image/*,.pdf" className="form-control"
                    onChange={e => setFile(e.target.files[0])} required />
                  <div style={{ fontSize: 12, color: '#999', marginTop: 4 }}>JPG, PNG, PDF — Max 5MB</div>
                </div>
              </div>
            </div>

            {/* Right — Summary */}
            <div style={{ background: '#f9f9f9', borderRadius: 12, padding: 24, height: 'fit-content', position: 'sticky', top: 90 }}>
              <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 20, marginBottom: 20 }}>Order Summary</h3>
              {items.map(i => (
                <div key={i.id} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, marginBottom: 10 }}>
                  <span style={{ color: '#555' }}>{i.name} × {i.quantity}</span>
                  <span style={{ fontWeight: 600 }}>{formatPrice((i.sale_price || i.price) * i.quantity)}</span>
                </div>
              ))}
              <div style={{ borderTop: '1px solid #ddd', margin: '16px 0', paddingTop: 16 }}>
                {[['Subtotal', formatPrice(subtotal)], ['Shipping', shipping === 0 ? 'Free' : formatPrice(shipping)]].map(([l, v]) => (
                  <div key={l} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, marginBottom: 10 }}>
                    <span style={{ color: '#666' }}>{l}</span><span style={{ fontWeight: 600 }}>{v}</span>
                  </div>
                ))}
                <div style={{ borderTop: '2px solid #D4AF37', paddingTop: 14, display: 'flex', justifyContent: 'space-between' }}>
                  <span style={{ fontWeight: 700, fontSize: 16 }}>Total</span>
                  <span style={{ fontWeight: 700, fontSize: 18, color: '#D4AF37' }}>{formatPrice(total)}</span>
                </div>
              </div>
              <button type="submit" className="btn btn-gold btn-block btn-lg" disabled={loading}>
                {loading ? 'Placing Order...' : 'Place Order'}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
