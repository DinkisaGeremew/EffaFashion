import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../api';
import ProductCard from '../components/ProductCard';

export default function Products() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [total, setTotal]       = useState(0);
  const [pages, setPages]       = useState(1);
  const [loading, setLoading]   = useState(true);

  const page     = parseInt(searchParams.get('page') || '1');
  const category = searchParams.get('category') || '';
  const search   = searchParams.get('search')   || '';
  const featured = searchParams.get('featured') || '';

  useEffect(() => { api.get('/categories').then(r => setCategories(r.data)); }, []);

  useEffect(() => {
    setLoading(true);
    const params = new URLSearchParams({ page, limit: 12 });
    if (category) params.set('category', category);
    if (search)   params.set('search', search);
    if (featured) params.set('featured', featured);
    api.get(`/products?${params}`).then(r => {
      setProducts(r.data.products);
      setTotal(r.data.total);
      setPages(r.data.pages);
    }).finally(() => setLoading(false));
  }, [page, category, search, featured]);

  const setParam = (key, val) => {
    const p = new URLSearchParams(searchParams);
    if (val) p.set(key, val); else p.delete(key);
    p.delete('page');
    setSearchParams(p);
  };

  return (
    <div style={{ paddingTop: 70 }}>
      <div style={{ background: '#000', padding: '50px 0' }}>
        <div className="container text-center">
          <h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 42 }}>
            {search ? `Search: "${search}"` : featured ? 'New Arrivals' : 'All Products'}
          </h1>
          <p style={{ color: 'rgba(255,255,255,0.5)', marginTop: 8 }}>{total} products found</p>
        </div>
      </div>

      <div className="container" style={{ padding: '40px 20px' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '240px 1fr', gap: 32 }}>

          {/* Sidebar */}
          <aside>
            <div style={{ background: '#fff', border: '1px solid #eee', borderRadius: 12, padding: 24 }}>
              <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 18, marginBottom: 20 }}>Filter</h3>

              <div style={{ marginBottom: 24 }}>
                <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: 1, color: '#999', textTransform: 'uppercase', marginBottom: 12 }}>Category</div>
                <div style={{ cursor: 'pointer', padding: '8px 0', borderBottom: '1px solid #f0f0f0', fontWeight: !category ? 700 : 400, color: !category ? '#D4AF37' : '#333' }}
                  onClick={() => setParam('category', '')}>All</div>
                {categories.map(c => (
                  <div key={c.id} style={{ cursor: 'pointer', padding: '8px 0', borderBottom: '1px solid #f0f0f0', fontWeight: category == c.id ? 700 : 400, color: category == c.id ? '#D4AF37' : '#333' }}
                    onClick={() => setParam('category', c.id)}>{c.name}</div>
                ))}
              </div>

              {(category || search || featured) && (
                <button className="btn btn-outline btn-sm btn-block"
                  onClick={() => setSearchParams({})}>Clear Filters</button>
              )}
            </div>
          </aside>

          {/* Products */}
          <div>
            {loading ? (
              <div className="spinner-center"><div className="spinner" /></div>
            ) : products.length === 0 ? (
              <div className="text-center" style={{ padding: 80 }}>
                <p style={{ fontSize: 18, color: '#999' }}>No products found.</p>
              </div>
            ) : (
              <>
                <div className="products-grid">
                  {products.map(p => <ProductCard key={p.id} product={p} />)}
                </div>

                {pages > 1 && (
                  <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 40 }}>
                    {Array.from({ length: pages }, (_, i) => i + 1).map(p => (
                      <button key={p} onClick={() => setParam('page', p)}
                        style={{ width: 38, height: 38, borderRadius: 6, border: '1px solid', cursor: 'pointer', fontWeight: 600, background: page === p ? '#D4AF37' : '#fff', borderColor: page === p ? '#D4AF37' : '#ddd', color: page === p ? '#000' : '#555' }}>
                        {p}
                      </button>
                    ))}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
