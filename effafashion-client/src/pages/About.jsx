export default function About() {
  return (
    <div style={{ paddingTop: 70 }}>

      {/* Hero */}
      <section style={{ position: 'relative', minHeight: 420, display: 'flex', alignItems: 'center', background: '#000', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', inset: 0, backgroundImage: "url('https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=1600&fit=crop')", backgroundSize: 'cover', backgroundPosition: 'center', opacity: 0.3 }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(to right,rgba(0,0,0,0.9),rgba(0,0,0,0.4))' }} />
        <div className="container" style={{ position: 'relative', zIndex: 1, color: '#fff' }}>
          <span style={{ display: 'inline-block', background: 'rgba(212,175,55,0.2)', color: '#D4AF37', padding: '6px 18px', borderRadius: 20, fontSize: 12, fontWeight: 700, letterSpacing: 2, textTransform: 'uppercase', marginBottom: 20 }}>Our Story</span>
          <h1 style={{ fontFamily: "'Playfair Display',serif", fontSize: 'clamp(36px,6vw,64px)', lineHeight: 1.1, marginBottom: 20 }}>
            About <span style={{ color: '#D4AF37' }}>EffaFashion</span>
          </h1>
          <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 17, maxWidth: 560, lineHeight: 1.9 }}>
            Premium luxury fashion for the modern individual who values elegance and sophistication.
          </p>
        </div>
      </section>

      {/* Mission */}
      <section className="section">
        <div className="container">
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 80, alignItems: 'center' }}>
            <div>
              <span style={{ fontSize: 12, color: '#D4AF37', fontWeight: 700, letterSpacing: 2, textTransform: 'uppercase' }}>Who We Are</span>
              <h2 style={{ fontFamily: "'Playfair Display',serif", fontSize: 'clamp(28px,4vw,42px)', margin: '16px 0', lineHeight: 1.2 }}>
                Crafting Luxury <span style={{ color: '#D4AF37' }}>Since Day One</span>
              </h2>
              <p style={{ color: '#666', lineHeight: 1.9, marginBottom: 16 }}>
                EffaFashion was founded with a single vision — to bring world-class luxury fashion to Ethiopia and beyond. We believe that style is a language, and we help you speak it fluently.
              </p>
              <p style={{ color: '#666', lineHeight: 1.9, marginBottom: 24 }}>
                Based in Burayu Dire, Ethiopia, our carefully curated collections span women's wear, men's fashion, and premium accessories — all selected for their craftsmanship, quality, and timeless elegance.
              </p>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
                {[['5,000+','Happy Customers'],['500+','Products'],['50+','Brands'],['10+','Years Experience']].map(([n, l]) => (
                  <div key={l} style={{ background: '#f9f9f9', borderRadius: 12, padding: 20 }}>
                    <div style={{ fontFamily: "'Playfair Display',serif", fontSize: 32, color: '#D4AF37', fontWeight: 700 }}>{n}</div>
                    <div style={{ fontSize: 13, color: '#666', marginTop: 4 }}>{l}</div>
                  </div>
                ))}
              </div>
            </div>
            <div style={{ borderRadius: 20, overflow: 'hidden', aspectRatio: '4/5' }}>
              <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=750&fit=crop" alt="EffaFashion" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            </div>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="section section-gray">
        <div className="container">
          <div className="section-header">
            <h2>Our <span>Values</span></h2>
            <p>What drives everything we do</p>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 24 }}>
            {[
              { icon: '✦', title: 'Premium Quality', desc: 'Every product is handpicked for its craftsmanship, material quality, and attention to detail.' },
              { icon: '♦', title: 'Customer First', desc: 'Your satisfaction is our priority. We offer seamless shopping, fast delivery, and easy returns.' },
              { icon: '❋', title: 'Timeless Style', desc: 'We curate pieces that transcend trends — fashion that looks as good tomorrow as it does today.' },
            ].map(({ icon, title, desc }) => (
              <div key={title} style={{ background: '#fff', borderRadius: 16, padding: 32, textAlign: 'center', boxShadow: '0 2px 12px rgba(0,0,0,0.06)' }}>
                <div style={{ fontSize: 32, color: '#D4AF37', marginBottom: 16 }}>{icon}</div>
                <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 20, marginBottom: 12 }}>{title}</h3>
                <p style={{ color: '#666', fontSize: 14, lineHeight: 1.8 }}>{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Team */}
      <section className="section">
        <div className="container">
          <div className="section-header">
            <h2>Meet the <span>Team</span></h2>
            <p>The passionate people behind EffaFashion</p>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 24 }}>
            {[
              { name: 'Effa Geremew', role: 'Founder & CEO', img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop' },
              { name: 'Amara Tadesse', role: 'Head of Design', img: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=300&h=300&fit=crop' },
              { name: 'Kebede Alemu', role: 'Operations Manager', img: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop' },
            ].map(({ name, role, img }) => (
              <div key={name} style={{ textAlign: 'center' }}>
                <div style={{ width: 140, height: 140, borderRadius: '50%', overflow: 'hidden', margin: '0 auto 16px', border: '3px solid #D4AF37' }}>
                  <img src={img} alt={name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                </div>
                <h3 style={{ fontFamily: "'Playfair Display',serif", fontSize: 18, marginBottom: 4 }}>{name}</h3>
                <p style={{ color: '#D4AF37', fontSize: 13, fontWeight: 600 }}>{role}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

    </div>
  );
}
