import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { FiTrash2, FiMinus, FiPlus } from 'react-icons/fi';
import useCartStore from '../store/useCartStore';
import { formatPrice, getImageUrl } from '../utils/helpers';
import api from '../api';
import toast from 'react-hot-toast';

export default function Cart() {
  const { items, fetchCart, updateItem, removeItem } = useCartStore();
  const [coupon, setCoupon]     = useState('');
  const [discount, setDiscount] = useState(0);
  const [couponMsg, setCouponMsg] = useState('');

  useEffect(() => { fetchCart(); }, []);

  const subtotal = items.reduce((s, i) => s + (i.sale_price || i.price) * i.quantity, 0);
  const shipping = subtotal >= 20000 ? 0 : 2500;
  const total    = subtotal - discount + shipping;

  const applyCoupon = async () => {
    try {
      const { data } = await api.post('/coupons/validate', { code: coupon, order_total: subtotal });
      if (data.valid) { setDiscount(data.discount); setCouponMsg('✅ ' + data.message); }
      else            { setDiscount(0); setCouponMsg('❌ ' + data.message); }
    } catch { setCouponMsg('❌ Failed to apply coupon'); }
  };

  if (items.length === 0) return (
    <div style={{ paddingTop: 120, textAlign: 'center', minHeight: '60vh' }}>
      <div style={{ fontSize: 64, marginBottom: 20 }}>🛒</div>
      <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 12 }}>Your cart is empty</h2>
      <p style={{ color: '#999', marginBottom: 24 }}>Looks like you haven't added anything yet.</p>
      <Link to="/products" className="btn btn-gold btn-lg">Start Shopping</Link>
    </div>
  );

  return (
    <div style={{ paddingTop: 90 }}>
      <div style={{ background: '#000', padding: '40px 0' }}>
        <div className="container"><h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 36 }}>Shopping Cart</h1></div>
      </div>
      <div className="container" style={{ padding: '40px 20px' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 340px', gap: 32 }}>

          {/* Items */}
          <div>
            {items.map(item => (
              <div key={item.id} style={{ display: 'grid', gridTemplateColumns: '90px 1fr auto', gap: 20, padding: '20px 0', borderBottom: '1px solid #f0f0f0', alignItems: 'center' }}>
                <img src={getImageUrl(item.image)} alt={item.name} style={{ width: 90, height: 110, objectFit: 'cover', borderRadius: 8 }} />
                <div>
                  <Link to={`/products/${item.slug}`} style={{ fontWeight: 600, fontSize: 16 }}>{item.name}</Link>
                  {item.size  && <div style={{ fontSize: 13, color: '#999', marginTop: 4 }}>Size: {item.size}</div>}
                  {item.color && <div style={{ fontSize: 13, color: '#999' }}>Color: {item.color}</div>}
                  <div style={{ fontSize: 16, fontWeight: 700, color: '#D4AF37', marginTop: 8 }}>{formatPrice(item.sale_price || item.price)}</div>
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 12 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10, border: '1px solid #eee', borderRadius: 8, padding: '4px 8px' }}>
                    <button onClick={() => item.quantity > 1 ? updateItem(item.id, item.quantity - 1) : removeItem(item.id)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#555' }}><FiMinus /></button>
                    <span style={{ fontWeight: 600, minWidth: 24, textAlign: 'center' }}>{item.quantity}</span>
                    <button onClick={() => updateItem(item.id, item.quantity + 1)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#555' }}><FiPlus /></button>
                  </div>
                  <div style={{ fontWeight: 700 }}>{formatPrice((item.sale_price || item.price) * item.quantity)}</div>
                  <button onClick={() => removeItem(item.id)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#dc3545' }}><FiTrash2 /></button>
                </div>
              </div>
            ))}
          </div>

          {/* Summary */}
          <div style={{ background: '#f9f9f9', borderRadius: 12, padding: 24, height: 'fit-content', position: 'sticky', top: 90 }}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 20, marginBottom: 20 }}>Order Summary</h3>

            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
              <input value={coupon} onChange={e => setCoupon(e.target.value)} placeholder="Coupon code"
                className="form-control" style={{ flex: 1 }} />
              <button className="btn btn-gold btn-sm" onClick={applyCoupon}>Apply</button>
            </div>
            {couponMsg && <div style={{ fontSize: 13, marginBottom: 12, color: couponMsg.startsWith('✅') ? '#155724' : '#721c24' }}>{couponMsg}</div>}

            {[['Subtotal', formatPrice(subtotal)], ['Shipping', shipping === 0 ? 'Free' : formatPrice(shipping)], discount > 0 && ['Discount', '−' + formatPrice(discount)]].filter(Boolean).map(([l, v]) => (
              <div key={l} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 12, fontSize: 14 }}>
                <span style={{ color: '#666' }}>{l}</span><span style={{ fontWeight: 600 }}>{v}</span>
              </div>
            ))}

            <div style={{ borderTop: '2px solid #D4AF37', paddingTop: 16, display: 'flex', justifyContent: 'space-between', marginBottom: 20 }}>
              <span style={{ fontWeight: 700, fontSize: 16 }}>Total</span>
              <span style={{ fontWeight: 700, fontSize: 18, color: '#D4AF37' }}>{formatPrice(total)}</span>
            </div>

            <Link to="/checkout" className="btn btn-gold btn-block btn-lg">Proceed to Checkout</Link>
            <Link to="/products" className="btn btn-outline btn-block mt-4" style={{ textAlign: 'center' }}>Continue Shopping</Link>
          </div>
        </div>
      </div>
    </div>
  );
}
