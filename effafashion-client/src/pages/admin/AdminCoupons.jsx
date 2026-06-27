import { useEffect, useState } from 'react';
import { FiPlus } from 'react-icons/fi';
import api from '../../api';
import toast from 'react-hot-toast';

const EMPTY = { code: '', discount_type: 'percentage', discount_value: '', min_order: '', max_uses: '', expires_at: '' };

export default function AdminCoupons() {
  const [coupons,   setCoupons]   = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [form,      setForm]      = useState(EMPTY);
  const [loading,   setLoading]   = useState(false);

  const load = () => api.get('/coupons').then(r => setCoupons(r.data));
  useEffect(() => { load(); }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post('/coupons', form);
      toast.success('Coupon created');
      setShowModal(false);
      setForm(EMPTY);
      load();
    } catch (err) { toast.error(err.response?.data?.message || 'Failed'); }
    finally { setLoading(false); }
  };

  const toggleCoupon = async (c) => {
    await api.put(`/coupons/${c.id}`, { is_active: !c.is_active });
    setCoupons(cs => cs.map(x => x.id === c.id ? { ...x, is_active: !x.is_active } : x));
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <h2 style={{ fontSize: 20, fontWeight: 700 }}>Coupons</h2>
        <button className="btn btn-gold" onClick={() => setShowModal(true)}><FiPlus /> New Coupon</button>
      </div>

      <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
          <thead>
            <tr style={{ background: '#f9f9f9', borderBottom: '1px solid #eee' }}>
              {['Code','Type','Value','Min Order','Uses','Expires','Status','Action'].map(h => (
                <th key={h} style={{ padding: '12px 16px', textAlign: 'left', fontSize: 12, fontWeight: 700, color: '#999', textTransform: 'uppercase' }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {coupons.map(c => (
              <tr key={c.id} style={{ borderBottom: '1px solid #f5f5f5' }}>
                <td style={{ padding: '12px 16px', fontWeight: 700, color: '#D4AF37', fontFamily: 'monospace' }}>{c.code}</td>
                <td style={{ padding: '12px 16px', textTransform: 'capitalize' }}>{c.discount_type}</td>
                <td style={{ padding: '12px 16px', fontWeight: 600 }}>{c.discount_type === 'percentage' ? c.discount_value + '%' : 'ETB ' + c.discount_value}</td>
                <td style={{ padding: '12px 16px' }}>ETB {c.min_order}</td>
                <td style={{ padding: '12px 16px' }}>{c.used_count}{c.max_uses ? '/' + c.max_uses : ''}</td>
                <td style={{ padding: '12px 16px', color: '#999', fontSize: 13 }}>{c.expires_at || '—'}</td>
                <td style={{ padding: '12px 16px' }}><span className={`status-badge ${c.is_active ? 'status-delivered' : 'status-cancelled'}`}>{c.is_active ? 'Active' : 'Inactive'}</span></td>
                <td style={{ padding: '12px 16px' }}>
                  <button onClick={() => toggleCoupon(c)} style={{ padding: '5px 12px', border: '1px solid', borderRadius: 6, cursor: 'pointer', fontSize: 12, background: c.is_active ? '#fff0f0' : '#f0fff0', borderColor: c.is_active ? '#dc3545' : '#28a745', color: c.is_active ? '#dc3545' : '#28a745' }}>
                    {c.is_active ? 'Disable' : 'Enable'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <div style={{ background: '#fff', borderRadius: 16, width: '100%', maxWidth: 480, padding: 32 }}>
            <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 22, marginBottom: 24 }}>New Coupon</h3>
            <form onSubmit={handleSubmit}>
              {[['code','Code','text',true],['discount_value','Discount Value','number',true],['min_order','Min Order (ETB)','number',false],['max_uses','Max Uses','number',false],['expires_at','Expires','date',false]].map(([k,l,t,req]) => (
                <div key={k} className="form-group">
                  <label className="form-label">{l} {req && <span className="required">*</span>}</label>
                  <input className="form-control" type={t} value={form[k]} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))} required={req} style={k === 'code' ? { textTransform: 'uppercase' } : {}} />
                </div>
              ))}
              <div className="form-group">
                <label className="form-label">Discount Type</label>
                <select className="form-control" value={form.discount_type} onChange={e => setForm(f => ({ ...f, discount_type: e.target.value }))}>
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed (ETB)</option>
                </select>
              </div>
              <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', marginTop: 8 }}>
                <button type="button" className="btn btn-outline" onClick={() => setShowModal(false)}>Cancel</button>
                <button type="submit" className="btn btn-gold" disabled={loading}>{loading ? 'Creating...' : 'Create Coupon'}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
