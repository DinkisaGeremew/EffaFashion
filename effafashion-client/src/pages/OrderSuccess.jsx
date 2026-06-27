import { useSearchParams, Link } from 'react-router-dom';

export default function OrderSuccess() {
  const [params] = useSearchParams();
  const order    = params.get('order');
  return (
    <div style={{ paddingTop: 120, textAlign: 'center', minHeight: '70vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
      <div style={{ maxWidth: 500 }}>
        <div style={{ fontSize: 72, marginBottom: 20 }}>✅</div>
        <h1 style={{ fontFamily: "'Playfair Display',serif", fontSize: 36, marginBottom: 12 }}>Order Placed!</h1>
        <p style={{ color: '#555', fontSize: 16, marginBottom: 8 }}>Your order number is:</p>
        <div style={{ background: '#f9f9f9', border: '2px solid #D4AF37', borderRadius: 8, padding: '16px 32px', display: 'inline-block', fontFamily: 'monospace', fontSize: 22, fontWeight: 700, color: '#D4AF37', marginBottom: 24 }}>{order}</div>
        <p style={{ color: '#666', marginBottom: 32, lineHeight: 1.8 }}>
          We've received your order and payment screenshot. Our team will verify your payment and process your order within 24 hours.
        </p>
        <div style={{ display: 'flex', gap: 16, justifyContent: 'center' }}>
          <Link to="/orders" className="btn btn-gold btn-lg">View My Orders</Link>
          <Link to="/products" className="btn btn-outline btn-lg">Continue Shopping</Link>
        </div>
      </div>
    </div>
  );
}
