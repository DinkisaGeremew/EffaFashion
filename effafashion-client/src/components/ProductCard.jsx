import { Link } from 'react-router-dom';
import { FiHeart, FiShoppingBag } from 'react-icons/fi';
import { formatPrice, discountPercent, getImageUrl } from '../utils/helpers';
import useCartStore from '../store/useCartStore';
import useWishlistStore from '../store/useWishlistStore';

export default function ProductCard({ product }) {
  const addToCart  = useCartStore((s) => s.addToCart);
  const toggle     = useWishlistStore((s) => s.toggle);
  const inWishlist = useWishlistStore((s) => s.isInWishlist(product.id));
  const discount   = discountPercent(product.price, product.sale_price);
  const price      = product.sale_price || product.price;

  return (
    <div className="product-card">
      <div className="product-card-image">
        <Link to={`/products/${product.slug}`}>
          <img src={getImageUrl(product.image)} alt={product.name} loading="lazy"
            onError={(e) => { e.target.src = '/placeholder.jpg'; }} />
        </Link>

        <div className="product-card-badges">
          {discount > 0  && <span className="badge-sale">-{discount}%</span>}
          {product.is_featured && <span className="badge-new">New</span>}
          {product.stock == 0  && <span className="badge-out">Sold Out</span>}
        </div>

        <button className={`wishlist-btn${inWishlist ? ' active' : ''}`}
          onClick={() => toggle(product.id)}>
          <FiHeart fill={inWishlist ? 'currentColor' : 'none'} />
        </button>

        <div className="product-card-overlay">
          <button className="btn btn-gold btn-sm btn-block"
            onClick={() => addToCart(product.id, 1)}
            disabled={product.stock == 0}>
            <FiShoppingBag /> {product.stock == 0 ? 'Out of Stock' : 'Quick Add'}
          </button>
        </div>
      </div>

      <div className="product-card-body">
        <div className="product-card-category">{product.category_name}</div>
        <h3 className="product-card-name">
          <Link to={`/products/${product.slug}`}>{product.name}</Link>
        </h3>
        <div className="product-card-price">
          <span className={`price-current${product.sale_price ? ' on-sale' : ''}`}>{formatPrice(price)}</span>
          {product.sale_price && <span className="price-original">{formatPrice(product.price)}</span>}
        </div>
      </div>
    </div>
  );
}
