import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import useWishlistStore from '../store/useWishlistStore';
import ProductCard from '../components/ProductCard';

export default function Wishlist() {
  const { items, fetchWishlist } = useWishlistStore();
  useEffect(() => { fetchWishlist(); }, []);

  return (
    <div style={{ paddingTop: 90 }}>
      <div style={{ background: '#000', padding: '40px 0' }}>
        <div className="container"><h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 36 }}>My Wishlist</h1></div>
      </div>
      <div className="container" style={{ padding: '40px 20px' }}>
        {items.length === 0 ? (
          <div className="text-center" style={{ padding: 80 }}>
            <div style={{ fontSize: 64, marginBottom: 20 }}>❤️</div>
            <h2 style={{ fontFamily: "'Playfair Display',serif", marginBottom: 12 }}>Your wishlist is empty</h2>
            <Link to="/products" className="btn btn-gold btn-lg">Explore Products</Link>
          </div>
        ) : (
          <div className="products-grid">
            {items.map(p => <ProductCard key={p.id} product={p} />)}
          </div>
        )}
      </div>
    </div>
  );
}
