import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FiShoppingBag, FiHeart, FiUser, FiMenu, FiX, FiSearch } from 'react-icons/fi';
import useAuthStore from '../store/useAuthStore';
import useCartStore from '../store/useCartStore';
import useWishlistStore from '../store/useWishlistStore';
import './Navbar.css';

export default function Navbar() {
  const [menuOpen, setMenuOpen]   = useState(false);
  const [scrolled, setScrolled]   = useState(false);
  const [search, setSearch]       = useState('');
  const { user, logout }          = useAuthStore();
  const items                     = useCartStore((s) => s.items);
  const wishlist                  = useWishlistStore((s) => s.items);
  const navigate                  = useNavigate();

  const cartCount     = items.reduce((s, i) => s + i.quantity, 0);
  const wishlistCount = wishlist.length;

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const handleSearch = (e) => {
    e.preventDefault();
    if (search.trim()) { navigate(`/products?search=${encodeURIComponent(search)}`); setSearch(''); }
  };

  return (
    <nav className={`navbar${scrolled ? ' scrolled' : ''}`}>
      <div className="container navbar-inner">
        <Link to="/" className="navbar-logo">
          <span className="logo-effa">EFFA</span><span className="logo-fashion">FASHION</span>
        </Link>

        <ul className={`navbar-links${menuOpen ? ' open' : ''}`}>
          <li><Link to="/" onClick={() => setMenuOpen(false)}>Home</Link></li>
          <li><Link to="/products" onClick={() => setMenuOpen(false)}>Shop</Link></li>
          <li><Link to="/products?category=1" onClick={() => setMenuOpen(false)}>Women</Link></li>
          <li><Link to="/products?category=2" onClick={() => setMenuOpen(false)}>Men</Link></li>
          <li><Link to="/products?category=3" onClick={() => setMenuOpen(false)}>Accessories</Link></li>
          <li><Link to="/about" onClick={() => setMenuOpen(false)}>About</Link></li>
          <li><Link to="/contact" onClick={() => setMenuOpen(false)}>Contact</Link></li>
        </ul>

        <div className="navbar-actions">
          <form onSubmit={handleSearch} className="navbar-search">
            <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search..." />
            <button type="submit"><FiSearch /></button>
          </form>

          <Link to="/wishlist" className="nav-icon-btn">
            <FiHeart />
            {wishlistCount > 0 && <span className="nav-badge">{wishlistCount}</span>}
          </Link>

          <Link to="/cart" className="nav-icon-btn">
            <FiShoppingBag />
            {cartCount > 0 && <span className="nav-badge">{cartCount}</span>}
          </Link>

          {user ? (
            <div className="nav-user">
              <Link to={user.role === 'admin' ? '/admin' : '/profile'} className="nav-icon-btn">
                <FiUser />
              </Link>
              {user.role === 'admin' && (
                <Link to="/admin" className="btn btn-gold btn-sm">Admin</Link>
              )}
              <button className="btn btn-outline btn-sm" onClick={logout}>Logout</button>
            </div>
          ) : (
            <Link to="/login" className="btn btn-gold btn-sm">Login</Link>
          )}

          <button className="menu-toggle" onClick={() => setMenuOpen(!menuOpen)}>
            {menuOpen ? <FiX /> : <FiMenu />}
          </button>
        </div>
      </div>
    </nav>
  );
}
