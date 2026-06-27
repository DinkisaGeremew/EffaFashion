import { Router } from 'express';
import slugify from 'slugify';
import db from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';
import { uploadProduct } from '../middleware/upload.js';

const router = Router();

// GET /api/products
router.get('/', async (req, res) => {
  try {
    const { category, search, min_price, max_price, featured, limit = 12, page = 1 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);
    const where  = ['p.is_active = 1'];
    const params = [];

    if (category)   { where.push('p.category_id = ?');  params.push(category); }
    if (search)     { where.push('(p.name LIKE ? OR p.description LIKE ?)'); params.push(`%${search}%`, `%${search}%`); }
    if (min_price)  { where.push('p.price >= ?'); params.push(min_price); }
    if (max_price)  { where.push('p.price <= ?'); params.push(max_price); }
    if (featured === '1') { where.push('p.is_featured = 1'); }

    const whereStr = where.join(' AND ');
    const [products] = await db.query(
      `SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.stock, p.image,
              p.sizes, p.colors, p.is_featured, p.views, p.created_at,
              c.name AS category_name, c.id AS category_id
       FROM products p LEFT JOIN categories c ON p.category_id = c.id
       WHERE ${whereStr} ORDER BY p.created_at DESC LIMIT ? OFFSET ?`,
      [...params, parseInt(limit), offset]
    );
    const [[{ total }]] = await db.query(
      `SELECT COUNT(*) AS total FROM products p WHERE ${whereStr}`, params
    );
    res.json({ products, total, page: parseInt(page), pages: Math.ceil(total / limit) });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/products/:slug
router.get('/:slug', async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT p.*, c.name AS category_name FROM products p
       LEFT JOIN categories c ON p.category_id = c.id
       WHERE p.slug = ? AND p.is_active = 1 LIMIT 1`,
      [req.params.slug]
    );
    if (!rows.length) return res.status(404).json({ message: 'Product not found' });
    await db.query('UPDATE products SET views = views + 1 WHERE id = ?', [rows[0].id]);

    // Parse JSON fields
    const product = rows[0];
    try { product.sizes  = JSON.parse(product.sizes  || '[]'); } catch { product.sizes  = []; }
    try { product.colors = JSON.parse(product.colors || '[]'); } catch { product.colors = []; }
    try { product.images = JSON.parse(product.images || '[]'); } catch { product.images = []; }

    // Related products
    const [related] = await db.query(
      `SELECT id, name, slug, price, sale_price, image FROM products
       WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4`,
      [product.category_id, product.id]
    );
    // Reviews
    const [reviews] = await db.query(
      `SELECT r.rating, r.title, r.comment, r.created_at, u.full_name
       FROM reviews r JOIN users u ON r.user_id = u.id
       WHERE r.product_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC`,
      [product.id]
    );
    const [[rating]] = await db.query(
      'SELECT AVG(rating) AS avg, COUNT(*) AS total FROM reviews WHERE product_id = ? AND is_approved = 1',
      [product.id]
    );
    res.json({ product, related, reviews, rating });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/products — admin only
router.post('/', adminMiddleware, uploadProduct.single('image'), async (req, res) => {
  try {
    const { name, category_id, description, price, sale_price, stock, sizes, colors, is_featured } = req.body;
    if (!name || !category_id || !price) return res.status(400).json({ message: 'Name, category and price required' });
    let slug = slugify(name, { lower: true, strict: true });
    const [existing] = await db.query('SELECT id FROM products WHERE slug = ?', [slug]);
    if (existing.length) slug += '-' + Date.now();
    const image = req.file ? req.file.filename : '';
    const [result] = await db.query(
      `INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, sizes, colors, is_featured)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [category_id, name, slug, description || '', price, sale_price || null,
       stock || 0, image, sizes || '[]', colors || '[]', is_featured ? 1 : 0]
    );
    res.status(201).json({ id: result.insertId, slug, message: 'Product created' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/products/:id — admin only
router.put('/:id', adminMiddleware, uploadProduct.single('image'), async (req, res) => {
  try {
    const { name, category_id, description, price, sale_price, stock, sizes, colors, is_featured, is_active } = req.body;
    const fields = ['name=?','category_id=?','description=?','price=?','sale_price=?',
                    'stock=?','sizes=?','colors=?','is_featured=?','is_active=?'];
    const values = [name, category_id, description, price, sale_price || null,
                    stock, sizes || '[]', colors || '[]', is_featured ? 1 : 0, is_active ? 1 : 0];
    if (req.file) { fields.push('image=?'); values.push(req.file.filename); }
    values.push(req.params.id);
    await db.query(`UPDATE products SET ${fields.join(',')} WHERE id=?`, values);
    res.json({ message: 'Product updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/products/:id — admin only
router.delete('/:id', adminMiddleware, async (req, res) => {
  try {
    await db.query('UPDATE products SET is_active = 0 WHERE id = ?', [req.params.id]);
    res.json({ message: 'Product deleted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
