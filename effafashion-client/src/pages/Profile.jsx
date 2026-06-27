import { useState } from 'react';
import useAuthStore from '../store/useAuthStore';
import api from '../api';
import toast from 'react-hot-toast';

export default function Profile() {
  const { user, refreshUser } = useAuthStore();
  const [tab, setTab]         = useState('profile');
  const [form, setForm]       = useState({ full_name: user?.full_name || '', phone: user?.phone || '', address: user?.address || '', city: user?.city || '', country: user?.country || 'Ethiopia' });
  const [pwForm, setPwForm]   = useState({ current_password: '', new_password: '', confirm: '' });
  const [loading, setLoading] = useState(false);

  const saveProfile = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.put('/auth/profile', form);
      await refreshUser();
      toast.success('Profile updated!');
    } catch { toast.error('Failed to update'); } finally { setLoading(false); }
  };

  const changePassword = async (e) => {
    e.preventDefault();
    if (pwForm.new_password !== pwForm.confirm) { toast.error('Passwords do not match'); return; }
    setLoading(true);
    try {
      await api.put('/auth/password', pwForm);
      toast.success('Password updated!');
      setPwForm({ current_password: '', new_password: '', confirm: '' });
    } catch (err) { toast.error(err.response?.data?.message || 'Failed'); } finally { setLoading(false); }
  };

  const tabs = [['profile','Profile'],['password','Password']];

  return (
    <div style={{ paddingTop: 90 }}>
      <div style={{ background: '#000', padding: '40px 0' }}>
        <div className="container"><h1 style={{ fontFamily: "'Playfair Display',serif", color: '#fff', fontSize: 36 }}>My Profile</h1></div>
      </div>
      <div className="container" style={{ padding: '40px 20px', maxWidth: 700 }}>
        <div style={{ display: 'flex', gap: 8, marginBottom: 32, borderBottom: '2px solid #eee', paddingBottom: 0 }}>
          {tabs.map(([k, l]) => (
            <button key={k} onClick={() => setTab(k)}
              style={{ padding: '12px 24px', border: 'none', background: 'none', fontWeight: 600, cursor: 'pointer', borderBottom: tab === k ? '2px solid #D4AF37' : '2px solid transparent', color: tab === k ? '#D4AF37' : '#555', marginBottom: -2 }}>
              {l}
            </button>
          ))}
        </div>

        {tab === 'profile' && (
          <form onSubmit={saveProfile}>
            <div className="grid-2">
              {[['full_name','Full Name'],['phone','Phone'],['city','City'],['country','Country']].map(([k, l]) => (
                <div key={k} className="form-group">
                  <label className="form-label">{l}</label>
                  <input className="form-control" value={form[k] || ''} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))} />
                </div>
              ))}
              <div className="form-group" style={{ gridColumn: '1/-1' }}>
                <label className="form-label">Address</label>
                <textarea className="form-control" rows={2} value={form.address || ''} onChange={e => setForm(f => ({ ...f, address: e.target.value }))} />
              </div>
            </div>
            <button type="submit" className="btn btn-gold" disabled={loading}>{loading ? 'Saving...' : 'Save Changes'}</button>
          </form>
        )}

        {tab === 'password' && (
          <form onSubmit={changePassword} style={{ maxWidth: 400 }}>
            {[['current_password','Current Password'],['new_password','New Password'],['confirm','Confirm Password']].map(([k, l]) => (
              <div key={k} className="form-group">
                <label className="form-label">{l}</label>
                <input className="form-control" type="password" value={pwForm[k]} onChange={e => setPwForm(f => ({ ...f, [k]: e.target.value }))} required />
              </div>
            ))}
            <button type="submit" className="btn btn-gold" disabled={loading}>{loading ? 'Updating...' : 'Update Password'}</button>
          </form>
        )}
      </div>
    </div>
  );
}
