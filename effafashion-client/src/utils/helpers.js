export const formatPrice = (price) =>
  'ETB ' + Number(price).toLocaleString('en-US', { minimumFractionDigits: 2 });

export const discountPercent = (price, salePrice) =>
  salePrice ? Math.round(((price - salePrice) / price) * 100) : 0;

export const getImageUrl = (image) => {
  if (!image) return '/placeholder.jpg';
  if (image.startsWith('http')) return image;
  return `${import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:5000'}/uploads/products/${image}`;
};

export const timeAgo = (date) => {
  const diff = (Date.now() - new Date(date)) / 1000;
  if (diff < 60)    return 'just now';
  if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  return new Date(date).toLocaleDateString();
};
