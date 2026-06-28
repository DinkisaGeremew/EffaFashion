import { useState } from 'react';
import { FiMapPin, FiMail, FiPhone, FiClock, FiSend } from 'react-icons/fi';
import api from '../api';
import toast from 'react-hot-toast';

export default function Contact() {
  const [form, setForm]       = useState({ name: '', email: '', subject: '', message: '' });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post('/contact', form);
      toast.success('Message sent! We\'ll get back to you soon.');
      setForm({ name: '', email: '', subject: '', message: '' });
    } catch { toast.error('Failed to send message. Please try again.'); }
    finally { setLoading(false); }
  };

  const INFO = [
    { icon: FiMapPin, label: 'Address',      value: 'Burayu Dire, Addis Ababa, Ethiopia' },
    { icon: FiMail,   label: 'Email',         value: 'info@effafashion.com' },
    { icon: FiPhone,  label: 'Phone',         value: '+251 900 000 000' },
    { icon: FiClock,  label: 'Working Hours', value: 'Mon–Sat: 9AM – 7PM' },
  ];

  return (
    <div style={{ paddingTop: 70 }}>

      {/* Hero */}
      <section style={{ background: '#000', padding: '80px 0', textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', inset: 0, backgroundImage: "url('https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=1600&fit=crop')", backgroundSize: 'cover', backgroundPosition: 'center', opacity: 0.2 }} />
        <div style={{ position: 'relative', zIndex: 1 }}>
          <span style={{ display: 'inline-block', background: 'rgba(212,175,55,0.2)', color: '#D4AF37', padding: '6px 18px', borderRadius: 20, fontSize: 12, fontWeight: 700, letterSpacing: 2, textTransform: 'uppercase', marginBottom: 20 }}>Get In Touch</span>
          <h1 style={{ fontFamily: "'Playfair Display',serif", fontSize: 'clamp(36px,6vw,60px)', color: '#fff', marginBottom: 16 }}>
            Contact <span style={{ color: '#D4AF37' }}>Us</span>
          </h1>
          <p style={{ color: 'rgba(255,255,255,0.55)', fontSize: 16, maxWidth: 480, margin: '0 auto' }}>
            Have a question or feedback? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
          </p>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.6fr', gap: 60, alignItems: 'start' }}>

            {/* Info */}
            <div>
              <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 28, marginBottom: 8 }}>Let's Talk</h2>
              <p style={{ color: '#666', lineHeight: 1.8, marginBottom: 32 }}>
                We're here to help with any questions about our products, orders, or anything else. Reach out through any of the channels below.
              </p>

              <div style={{ display: 'flex', flexDirection: 'column', gap: 20, marginBottom: 40 }}>
                {INFO.map(({ icon: Icon, label, value }) => (
                  <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                    <div style={{ width: 48, height: 48, borderRadius: '50%', background: 'rgba(212,175,55,0.1)', border: '1px solid rgba(212,175,55,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                      <Icon size={18} color="#D4AF37" />
                    </div>
                    <div>
                      <div style={{ fontSize: 12, color: '#999', fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 2 }}>{label}</div>
                      <div style={{ fontSize: 15, fontWeight: 500 }}>{value}</div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Map placeholder */}
              <div style={{ borderRadius: 16, overflow: 'hidden', background: '#f0f0f0', height: 200, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <div style={{ textAlign: 'center', color: '#999' }}>
                  <FiMapPin size={32} style={{ marginBottom: 8, color: '#D4AF37' }} />
                  <div style={{ fontSize: 14 }}>Burayu Dire, Addis Ababa</div>
                </div>
              </div>
            </div>

            {/* Form */}
            <div style={{ background: '#fff', border: '1px solid #eee', borderRadius: 20, padding: 40, boxShadow: '0 4px 24px rgba(0,0,0,0.06)' }}>
              <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 24, marginBottom: 24 }}>Send a Message</h3>
              <form onSubmit={handleSubmit}>
                <div className="grid-2">
                  <div className="form-group">
                    <label className="form-label">Your Name <span className="required">*</span></label>
                    <input className="form-control" placeholder="Full name" value={form.name}
                      onChange={e => setForm(f => ({ ...f, name: e.target.value }))} required />
                  </div>
                  <div className="form-group">
                    <label className="form-label">Email Address <span className="required">*</span></label>
                    <input className="form-control" type="email" placeholder="your@email.com" value={form.email}
                      onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required />
                  </div>
                </div>
                <div className="form-group">
                  <label className="form-label">Subject</label>
                  <input className="form-control" placeholder="What is this about?" value={form.subject}
                    onChange={e => setForm(f => ({ ...f, subject: e.target.value }))} />
                </div>
                <div className="form-group">
                  <label className="form-label">Message <span className="required">*</span></label>
                  <textarea className="form-control" rows={6} placeholder="Write your message here..."
                    value={form.message} onChange={e => setForm(f => ({ ...f, message: e.target.value }))} required />
                </div>
                <button type="submit" className="btn btn-gold btn-lg btn-block" disabled={loading}>
                  <FiSend /> {loading ? 'Sending...' : 'Send Message'}
                </button>
              </form>
            </div>

          </div>
        </div>
      </section>
    </div>
  );
}
