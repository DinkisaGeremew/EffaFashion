import { useEffect, useState } from 'react';
import api from '../../api';
import toast from 'react-hot-toast';

export default function AdminUsers() {
  const [users, setUsers] = useState([]);
  useEffect(() => { api.get('/admin/users').then(r => setUsers(r.data)); }, []);

  const toggle = async (user) => {
    await api.put(`/admin/users/${user.id}`, { is_active: !user.is_active, role: user.role });
    toast.success('User updated');
    setUsers(us => us.map(u => u.id === user.id ? { ...u, is_active: !u.is_active } : u));
  };

  return (
    <div>
      <h2 style={{ fontSize: 20, fontWeight: 700, marginBottom: 24 }}>Users ({users.length})</h2>
      <div style={{ background: '#fff', borderRadius: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
          <thead>
            <tr style={{ background: '#f9f9f9', borderBottom: '1px solid #eee' }}>
              {['Name','Email','Phone','Role','Status','Joined','Action'].map(h => (
                <th key={h} style={{ padding: '12px 16px', textAlign: 'left', fontSize: 12, fontWeight: 700, color: '#999', textTransform: 'uppercase' }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {users.map(u => (
              <tr key={u.id} style={{ borderBottom: '1px solid #f5f5f5' }}>
                <td style={{ padding: '12px 16px', fontWeight: 600 }}>{u.full_name}</td>
                <td style={{ padding: '12px 16px', color: '#666' }}>{u.email}</td>
                <td style={{ padding: '12px 16px', color: '#666' }}>{u.phone || '—'}</td>
                <td style={{ padding: '12px 16px' }}><span style={{ background: u.role === 'admin' ? '#D4AF37' : '#f0f0f0', color: u.role === 'admin' ? '#000' : '#555', padding: '3px 10px', borderRadius: 20, fontSize: 12, fontWeight: 600 }}>{u.role}</span></td>
                <td style={{ padding: '12px 16px' }}><span className={`status-badge ${u.is_active ? 'status-delivered' : 'status-cancelled'}`}>{u.is_active ? 'Active' : 'Inactive'}</span></td>
                <td style={{ padding: '12px 16px', color: '#999', fontSize: 13 }}>{new Date(u.created_at).toLocaleDateString()}</td>
                <td style={{ padding: '12px 16px' }}>
                  {u.role !== 'admin' && (
                    <button onClick={() => toggle(u)} style={{ padding: '6px 14px', border: '1px solid', borderRadius: 6, cursor: 'pointer', fontSize: 13, background: u.is_active ? '#fff0f0' : '#f0fff0', borderColor: u.is_active ? '#dc3545' : '#28a745', color: u.is_active ? '#dc3545' : '#28a745' }}>
                      {u.is_active ? 'Disable' : 'Enable'}
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
