import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api';
import ProductCard from '../components/ProductCard';
import './Home.css';

export default function Home() {
  const [featured, setFeatured]     = useState([]);
  const [arrivals, setArrivals]     = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading]       = useState(true);

  useEffect(() => {
    Promise.all([
      api.get('/products?featured=1&limit=8'),
      api.get('/products?limit=4'),
      api.get('/categories'),
    ]).then(([f, a, c]) => {
      setFeatured(f.data.products);
      setArrivals(a.data.products);
      setCategories(c.data);
    }).finally(() => setLoading(false));
  }, []);

  const catImages = {
    women:        'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=400&h=530&fit=crop',
    men:          'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=530&fit=crop',
    accessories:  'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=400&h=530&fit=crop',
    'new-arrivals':'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=400&h=530&fit=crop',
    sale:         'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=400&h=530&fit=crop',
  };

  return (
    <>
      {/* ── Hero ── */}
      <section className="hero">
        <div className="hero-bg" style={{ backgroundImage: "url('https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=1600&h=900&fit=crop&q=80')" }} />
        <div className="hero-overlay" />
        <div className="container hero-content">
          <span className="hero-tag">New Collection 2025</span>
          <h1>Dress to <span style={{ color: '#D4AF37' }}>Impress</span></h1>
          <p>Discover exclusive luxury fashion collections crafted for the modern individual.</p>
          <div className="hero-btns">
            <Link to="/products" className="btn btn-gold btn-lg">Shop Now</Link>
            <Link to="/products?featured=1" className="btn btn-outline-white btn-lg">New Arrivals</Link>
          </div>
        </div>
      </section>

      {/* ── Marquee ── */}
      <div className="marquee-wrap">
        <div className="marquee-track">
          {['Free Delivery','New Arrivals 2025','Premium Quality','Exclusive Designs','Luxury Fashion','Men\'s Collection','Women\'s Collection','Accessories',
            'Free Delivery','New Arrivals 2025','Premium Quality','Exclusive Designs','Luxury Fashion','Men\'s Collection','Women\'s Collection','Accessories']
            .map((t, i) => <span key={i}>{t}</span>)}
        </div>
      </div>

      {/* ── Stats ── */}
      <section className="section-dark section-sm">
        <div className="container stats-grid">
          {[['5,000+','Happy Customers'],['500+','Products'],['50+','Brands'],['10+','Years Experience']].map(([n, l]) => (
            <div key={l} className="stat-card">
              <div className="stat-number">{n}</div>
              <div className="stat-label">{l}</div>
            </div>
          ))}
        </div>
      </section>

      {/* ── Categories ── */}
      <section className="section section-gray">
        <div className="container">
          <div className="section-header">
            <h2>Shop by <span>Category</span></h2>
            <p>Explore our curated collections for every style and occasion</p>
          </div>
          <div className="categories-grid">
            {categories.slice(0, 5).map((cat) => (
              <Link key={cat.id} to={`/products?category=${cat.id}`} className="category-card">
                <img src={catImages[cat.slug] || catImages.women} alt={cat.name} loading="lazy" />
                <div className="category-overlay">
                  <h3>{cat.name}</h3>
                  <span>Shop Now →</span>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* ── Featured Products ── */}
      {featured.length > 0 && (
        <section className="section">
          <div className="container">
            <div className="section-header">
              <h2>Featured <span>Products</span></h2>
              <p>Handpicked luxury pieces for the discerning fashion lover</p>
            </div>
            <div className="products-grid">
              {featured.map((p) => <ProductCard key={p.id} product={p} />)}
            </div>
            <div className="text-center mt-5">
              <Link to="/products" className="btn btn-dark btn-lg">View All Products →</Link>
            </div>
          </div>
        </section>
      )}

      {/* ── CTA Banner ── */}
      <section className="cta-section">
        <div className="cta-bg" style={{ backgroundImage: "url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1600&h=900&fit=crop')" }} />
        <div className="cta-overlay" />
        <div className="container text-center" style={{ position: 'relative', zIndex: 2 }}>
          <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 'clamp(28px,5vw,52px)', color: '#fff', marginBottom: 16 }}>
            Exclusive <span style={{ color: '#D4AF37' }}>Members</span> Sale
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 16, maxWidth: 500, margin: '0 auto 32px' }}>
            Sign up today and get 20% off your first order. Plus free delivery on all orders.
          </p>
          <div style={{ display: 'flex', gap: 16, justifyContent: 'center', flexWrap: 'wrap' }}>
            <Link to="/register" className="btn btn-gold btn-lg">Join Now — It's Free</Link>
            <Link to="/products" className="btn btn-outline-white btn-lg">View Sale Items</Link>
          </div>
        </div>
      </section>

      {/* ── New Arrivals ── */}
      {arrivals.length > 0 && (
        <section className="section section-gray">
          <div className="container">
            <div className="section-header">
              <h2>New <span>Arrivals</span></h2>
              <p>The latest additions to our luxury collection</p>
            </div>
            <div className="products-grid">
              {arrivals.map((p) => <ProductCard key={p.id} product={p} />)}
            </div>
          </div>
        </section>
      )}
    </>
  );
}
