import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { FiShoppingBag, FiHeart } from 'react-icons/fi';
import api from '../api';
import { formatPrice, discountPercent, getImageUrl } from '../utils/helpers';
import useCartStore from '../store/useCartStore';
import useWishlistStore from '../store/useWishlistStore';
import ProductCard from '../components/ProductCard';

export default function ProductDetail() {
  const { slug }        = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [selectedSize,  setSize]  = useState('');
  const [selectedColor, setColor] = useState('');
  const [qty, setQty]   = useState(1);
  const [img, setImg]   = useState('');

  const addToCart  = useCartStore((s) => s.addToCart);
  const toggle     = useWishlistStore((s) => s.toggle);
  const inWishlist = useWishlistStore((s) => s.isInWishlist(data?.product?.id));

  useEffect(() => {
    setLoading(true);
    api.get(`/products/${slug}`).then(r => {
      setData(r.data);
      setImg(getImageUrl(r.data.product.image));
    }).catch(() => setData(null)).finally(() => setLoading(false));
  }, [slug]);

  if (loading) return <div className="spinner-center" style={{ marginTop: 120 }}><div className="spinner" /></div>;
  if (!data)   return <div className="text-center" style={{ padding: 120 }}>Product not found. <Link to="/products">Go back</Link></div>;

  const { product, related, reviews, rating } = data;
  const price    = product.sale_price || product.price;
  const discount = discountPercent(product.price, product.sale_price);
  const sizes    = Array.isArray(product.sizes)  ? product.sizes  : JSON.parse(product.sizes  || '[]');
  const colors   = Array.isArray(product.colors) ? product.colors : JSON.parse(product.colors || '[]');

  return (
    <div style={{ paddingTop: 90 }}>
      <div className="container" style={{ padding: '40px 20px' }}>

        {/* Breadcrumb */}
        <div style={{ fontSize: 13, color: '#999', marginBottom: 30 }}>
          <Link to="/">Home</Link> / <Link to="/products">Products</Link> / {product.name}
        </div>

        {/* Main Grid */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 60, marginBottom: 60 }}>

          {/* Images */}
          <div>
            <div style={{ borderRadius: 12, overflow: 'hidden', background: '#f5f5f5', aspectRatio: '3/4', marginBottom: 12 }}>
              <img src={img} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            </div>
          </div>

          {/* Info */}
          <div>
            <div style={{ fontSize: 12, color: '#D4AF37', fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 8 }}>
              {product.category_name}
            </div>
            <h1 style={{ fontFamily: "'Playfair Display',serif", fontSize: 32, marginBottom: 16, lineHeight: 1.2 }}>{product.name}</h1>

            <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 20 }}>
              <span style={{ fontSize: 28, fontWeight: 700, color: product.sale_price ? '#dc3545' : '#000' }}>{formatPrice(price)}</span>
              {product.sale_price && <span style={{ fontSize: 18, color: '#999', textDecoration: 'line-through' }}>{formatPrice(product.price)}</span>}
              {discount > 0 && <span style={{ background: '#dc3545', color: '#fff', padding: '3px 10px', borderRadius: 4, fontSize: 13, fontWeight: 700 }}>-{discount}%</span>}
            </div>

            <p style={{ color: '#555', lineHeight: 1.8, marginBottom: 24 }}>{product.description}</p>

            {/* Sizes */}
            {sizes.length > 0 && (
              <div style={{ marginBottom: 20 }}>
                <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 10 }}>Size</div>
                <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                  {sizes.map(s => (
                    <button key={s} onClick={() => setSize(s)}
                      style={{ padding: '8px 16px', border: `2px solid ${selectedSize === s ? '#D4AF37' : '#ddd'}`, borderRadius: 6, background: selectedSize === s ? '#D4AF37' : '#fff', color: selectedSize === s ? '#000' : '#333', fontWeight: 600, cursor: 'pointer' }}>
                      {s}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Colors */}
            {colors.length > 0 && (
              <div style={{ marginBottom: 20 }}>
                <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 10 }}>Color</div>
                <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                  {colors.map(c => (
                    <button key={c} onClick={() => setColor(c)}
                      style={{ padding: '8px 16px', border: `2px solid ${selectedColor === c ? '#D4AF37' : '#ddd'}`, borderRadius: 6, background: selectedColor === c ? '#D4AF37' : '#fff', color: selectedColor === c ? '#000' : '#333', fontWeight: 600, cursor: 'pointer' }}>
                      {c}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Qty */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
              <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 36, height: 36, border: '1px solid #ddd', borderRadius: 6, background: '#fff', fontSize: 18, cursor: 'pointer' }}>−</button>
              <span style={{ width: 40, textAlign: 'center', fontWeight: 600 }}>{qty}</span>
              <button onClick={() => setQty(q => Math.min(product.stock, q + 1))} style={{ width: 36, height: 36, border: '1px solid #ddd', borderRadius: 6, background: '#fff', fontSize: 18, cursor: 'pointer' }}>+</button>
              <span style={{ fontSize: 13, color: '#999' }}>{product.stock} in stock</span>
            </div>

            <div style={{ display: 'flex', gap: 12 }}>
              <button className="btn btn-gold btn-lg" style={{ flex: 1 }}
                onClick={() => addToCart(product.id, qty, selectedSize, selectedColor)}
                disabled={product.stock === 0}>
                <FiShoppingBag /> {product.stock === 0 ? 'Out of Stock' : 'Add to Cart'}
              </button>
              <button className={`btn btn-outline${inWishlist ? ' btn-dark' : ''}`} onClick={() => toggle(product.id)}>
                <FiHeart fill={inWishlist ? 'currentColor' : 'none'} />
              </button>
            </div>
          </div>
        </div>

        {/* Reviews */}
        {reviews.length > 0 && (
          <div style={{ marginBottom: 60 }}>
            <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 24 }}>
              Reviews ({reviews.length}) — {Number(rating.avg).toFixed(1)} ★
            </h2>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(300px,1fr))', gap: 20 }}>
              {reviews.map((r, i) => (
                <div key={i} style={{ background: '#f9f9f9', borderRadius: 12, padding: 20 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
                    <strong>{r.full_name}</strong>
                    <span style={{ color: '#D4AF37' }}>{'★'.repeat(r.rating)}</span>
                  </div>
                  {r.title && <div style={{ fontWeight: 600, marginBottom: 6 }}>{r.title}</div>}
                  <p style={{ color: '#666', fontSize: 14 }}>{r.comment}</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Related */}
        {related.length > 0 && (
          <div>
            <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 24 }}>You May Also Like</h2>
            <div className="products-grid">
              {related.map(p => <ProductCard key={p.id} product={p} />)}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
