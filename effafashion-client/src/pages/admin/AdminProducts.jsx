import { useEffect, useState } from 'react';
import { FiPlus, FiEdit2, FiTrash2 } from 'react-icons/fi';
import api from '../../api';
import { formatPrice, getImageUrl } from '../../utils/helpers';
import toast from 'react-hot-toast';

const EMPTY = { name: '', category_id: '', description: '', price: '', sale_price: '', stock: '0', sizes: '', colors: '', is_featured: false };

export default function AdminProducts() {
  const [products,   setProducts]   = useState([]);
  const [categories, setCategories] = useState([]);
  const [showModal,  setShowModal]  = useState(false);
  const [editing,    setEditing]    = useState(null);
  const [form,       setForm]       = useState(EMPTY);
  const [imageFile,  setImageFile]  = useState(null);
  const [loading,    setLoading]    = useState(false);

  const load = () => api.get('/products?limit=100').then(r => setProducts(r.data.products));

  useEffect(() => {
    load();
    api.get('/categories').then(r => setCategories(r.data));
  }, []);

  const openAdd  = () => { setEditing(null); setForm(EMPTY); setImageFile(null); setShowModal(true); };
  const openEdit = (p) => {
    setEditing(p);
    setForm({
      name: p.name, category_id: p.category_id, description: p.description || '',
      price: p.price, sale_price: p.sale_price || '', stock: p.stock,
      sizes: Array.isArray(p.sizes) ? p.sizes.join(', ') : (JSON.parse(p.sizes || '[]')).join(', '),
      colors: Array.isArray(p.colors) ? p.colors.join(', ') : (JSON.parse(p.colors || '[]')).join(', '),
      is_featured: !!p.is_featured, is_active: p.is_active !== 0,
    });
    setImageFile(null);
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const fd = new FormData();
      const sizes  = JSON.stringify(form.sizes.split(',').map(s => s.trim()).filter(Boolean));
      const colors = JSON.stringify(form.colors.split(',').map(s => s.trim()).filter(Boolean));
      Object.entries({ ...form, sizes, colors }).forEach(([k, v]) => fd.append(k, v));
      if (imageFile) fd.append('image', imageFile);

      if (editing) {
        await api.put(`/products/${editing.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        toast.success('Product updated');
      } else {
        await api.post('/products', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        toast.success('Product added');
      }
      setShowModal(false);
      load();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed');
    } finally { setLoading(false); }
  };

  const deleteProduct = async (id) => {
    if (!window.confirm('Delete this product?')) return;
    await api.delete(`/products/${id}`);
    toast.success('Product deleted');
    load();
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
        <h2 style={{ fontSize: 20, fontWeight: 700 }}>Products ({products.length})</h2>
        <button className="btn btn-gold" onClick={openAdd}><FiPlus /> Add Product</button>
      </div>

      <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
          <thead>
            <tr style={{ background: '#f9f9f9', borderBottom: '1px solid #eee' }}>
              {['Image','Name','Category','Price','Stock','Status','Actions'].map(h => (
                <th key={h} style={{ padding: '12px 16px', textAlign: 'left', fontSize: 12, fontWeight: 700, color: '#999', textTransform: 'uppercase', letterSpacing: 0.5 }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {products.map(p => (
              <tr key={p.id} style={{ borderBottom: '1px solid #f5f5f5' }}>
                <td style={{ padding: '12px 16px' }}>
                  <img src={getImageUrl(p.image)} alt={p.name} style={{ width: 48, height: 60, objectFit: 'cover', borderRadius: 6 }} onError={e => e.target.src='/placeholder.jpg'} />
                </td>
                <td style={{ padding: '12px 16px', fontWeight: 600 }}>{p.name}</td>
                <td style={{ padding: '12px 16px', color: '#666' }}>{p.category_name}</td>
                <td style={{ padding: '12px 16px' }}>
                  <div style={{ fontWeight: 600 }}>{formatPrice(p.sale_price || p.price)}</div>
                  {p.sale_price && <div style={{ fontSize: 12, color: '#999', textDecoration: 'line-through' }}>{formatPrice(p.price)}</div>}
                </td>
                <td style={{ padding: '12px 16px' }}>
                  <span style={{ color: p.stock < 5 ? '#dc3545' : '#28a745', fontWeight: 600 }}>{p.stock}</span>
                </td>
                <td style={{ padding: '12px 16px' }}>
                  <span className={`status-badge ${p.is_active ? 'status-delivered' : 'status-cancelled'}`}>{p.is_active ? 'Active' : 'Inactive'}</span>
                </td>
                <td style={{ padding: '12px 16px' }}>
                  <div style={{ display: 'flex', gap: 8 }}>
                    <button onClick={() => openEdit(p)} style={{ padding: '6px 12px', background: '#f0f0f0', border: 'none', borderRadius: 6, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4, fontSize: 13 }}><FiEdit2 size={14} /> Edit</button>
                    <button onClick={() => deleteProduct(p.id)} style={{ padding: '6px 12px', background: '#fff0f0', border: 'none', borderRadius: 6, cursor: 'pointer', color: '#dc3545', display: 'flex', alignItems: 'center', gap: 4, fontSize: 13 }}><FiTrash2 size={14} /> Del</button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal */}
      {showModal && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 20 }}>
          <div style={{ background: '#fff', borderRadius: 16, width: '100%', maxWidth: 640, maxHeight: '90vh', overflow: 'auto', padding: 32 }}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 22, marginBottom: 24 }}>{editing ? 'Edit Product' : 'Add Product'}</h3>
            <form onSubmit={handleSubmit}>
              <div className="grid-2">
                <div className="form-group" style={{ gridColumn: '1/-1' }}>
                  <label className="form-label">Product Name <span className="required">*</span></label>
                  <input className="form-control" value={form.name} onChange={e => setForm(f => ({ ...f, name: e.target.value }))} required />
                </div>
                <div className="form-group">
                  <label className="form-label">Category <span className="required">*</span></label>
                  <select className="form-control" value={form.category_id} onChange={e => setForm(f => ({ ...f, category_id: e.target.value }))} required>
                    <option value="">Select category</option>
                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                </div>
                <div className="form-group">
                  <label className="form-label">Stock</label>
                  <input className="form-control" type="number" min="0" value={form.stock} onChange={e => setForm(f => ({ ...f, stock: e.target.value }))} />
                </div>
                <div className="form-group">
                  <label className="form-label">Price (ETB) <span className="required">*</span></label>
                  <input className="form-control" type="number" step="0.01" value={form.price} onChange={e => setForm(f => ({ ...f, price: e.target.value }))} required />
                </div>
                <div className="form-group">
                  <label className="form-label">Sale Price (ETB)</label>
                  <input className="form-control" type="number" step="0.01" value={form.sale_price} onChange={e => setForm(f => ({ ...f, sale_price: e.target.value }))} />
                </div>
                <div className="form-group">
                  <label className="form-label">Sizes (comma separated)</label>
                  <input className="form-control" placeholder="XS, S, M, L, XL" value={form.sizes} onChange={e => setForm(f => ({ ...f, sizes: e.target.value }))} />
                </div>
                <div className="form-group">
                  <label className="form-label">Colors (comma separated)</label>
                  <input className="form-control" placeholder="Black, White, Gold" value={form.colors} onChange={e => setForm(f => ({ ...f, colors: e.target.value }))} />
                </div>
                <div className="form-group" style={{ gridColumn: '1/-1' }}>
                  <label className="form-label">Description</label>
                  <textarea className="form-control" rows={3} value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} />
                </div>
                <div className="form-group" style={{ gridColumn: '1/-1' }}>
                  <label className="form-label">Product Image</label>
                  <input type="file" className="form-control" accept="image/*" onChange={e => setImageFile(e.target.files[0])} />
                </div>
                <div className="form-group" style={{ gridColumn: '1/-1', display: 'flex', gap: 20 }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer', fontSize: 14 }}>
                    <input type="checkbox" checked={form.is_featured} onChange={e => setForm(f => ({ ...f, is_featured: e.target.checked }))} style={{ accentColor: '#D4AF37', width: 16, height: 16 }} />
                    Mark as Featured
                  </label>
                  {editing && (
                    <label style={{ display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer', fontSize: 14 }}>
                      <input type="checkbox" checked={form.is_active} onChange={e => setForm(f => ({ ...f, is_active: e.target.checked }))} style={{ accentColor: '#D4AF37', width: 16, height: 16 }} />
                      Active
                    </label>
                  )}
                </div>
              </div>
              <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', marginTop: 8 }}>
                <button type="button" className="btn btn-outline" onClick={() => setShowModal(false)}>Cancel</button>
                <button type="submit" className="btn btn-gold" disabled={loading}>{loading ? 'Saving...' : editing ? 'Update' : 'Add Product'}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
