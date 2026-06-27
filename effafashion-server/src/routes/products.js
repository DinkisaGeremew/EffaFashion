import { Router } from 'express';
import slugify from 'slugify';
import { query } from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';
import { uploadProduct } from '../middleware/upload.js';

const router = Router();

router.get('/', async (req, res) => {
  try {
    const { category, search, min_price, max_price, featured, limit = 12, page = 1 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);
    const where  = ['p.is_active = true'];
    const params = [];
    let   i      = 1;

    if (category)  { where.push(`p.category_id = $${i++}`);  params.push(category); }
    if (search)    { where.push(`(p.name ILIKE $${i} OR p.description ILIKE $${i++})`); params.push(`%${search}%`); }
    if (min_price) { where.push(`p.price >= $${i++}`); params.push(min_price); }
    if (max_price) { where.push(`p.price <= $${i++}`); params.push(max_price); }
    if (featured === '1') where.push('p.is_featured = true');

    const whereStr = where.join(' AND ');
    const products = await query(
      `SELECT p.id,p.name,p.slug,p.price,p.sale_price,p.stock,p.image,
              p.sizes,p.colors,p.is_featured,p.views,p.created_at,
              c.name AS category_name, c.id AS category_id
       FROM products p LEFT JOIN categories c ON p.category_id=c.id
       WHERE ${whereStr} ORDER BY p.created_at DESC
       LIMIT $${i++} OFFSET $${i++}`,
      [...params, parseInt(limit), offset]
    );
    const countRows = await query(
      `SELECT COUNT(*) AS total FROM products p WHERE ${whereStr}`, params
    );
    const total = parseInt(countRows[0].total);
    res.json({ products, total, page: parseInt(page), pages: Math.ceil(total / limit) });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/:slug', async (req, res) => {
  try {
    const rows = await query(
      `SELECT p.*,c.name AS category_name FROM products p
       LEFT JOIN categories c ON p.category_id=c.id
       WHERE p.slug=$1 AND p.is_active=true LIMIT 1`,
      [req.params.slug]
    );
    if (!rows.length) return res.status(404).json({ message: 'Product not found' });
    await query('UPDATE products SET views=views+1 WHERE id=$1', [rows[0].id]);

    const product = rows[0];
    try { product.sizes  = JSON.parse(product.sizes  || '[]'); } catch { product.sizes  = []; }
    try { product.colors = JSON.parse(product.colors || '[]'); } catch { product.colors = []; }
    try { product.images = JSON.parse(product.images || '[]'); } catch { product.images = []; }

    const related = await query(
      `SELECT id,name,slug,price,sale_price,image FROM products
       WHERE category_id=$1 AND id!=$2 AND is_active=true LIMIT 4`,
      [product.category_id, product.id]
    );
    const reviews = await query(
      `SELECT r.rating,r.title,r.comment,r.created_at,u.full_name
       FROM reviews r JOIN users u ON r.user_id=u.id
       WHERE r.product_id=$1 AND r.is_approved=true ORDER BY r.created_at DESC`,
      [product.id]
    );
    const ratingRows = await query(
      'SELECT AVG(rating) AS avg, COUNT(*) AS total FROM reviews WHERE product_id=$1 AND is_approved=true',
      [product.id]
    );
    res.json({ product, related, reviews, rating: ratingRows[0] });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/', adminMiddleware, uploadProduct.single('image'), async (req, res) => {
  try {
    const { name, category_id, description, price, sale_price, stock, sizes, colors, is_featured } = req.body;
    if (!name || !category_id || !price) return res.status(400).json({ message: 'Name, category and price required' });
    let slug = slugify(name, { lower: true, strict: true });
    const existing = await query('SELECT id FROM products WHERE slug=$1', [slug]);
    if (existing.length) slug += '-' + Date.now();
    const image = req.file ? req.file.filename : '';
    const rows = await query(
      `INSERT INTO products (category_id,name,slug,description,price,sale_price,stock,image,sizes,colors,is_featured)
       VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11) RETURNING id`,
      [category_id, name, slug, description || '', price, sale_price || null,
       stock || 0, image, sizes || '[]', colors || '[]', is_featured ? true : false]
    );
    res.status(201).json({ id: rows[0].id, slug, message: 'Product created' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/:id', adminMiddleware, uploadProduct.single('image'), async (req, res) => {
  try {
    const { name, category_id, description, price, sale_price, stock, sizes, colors, is_featured, is_active } = req.body;
    if (req.file) {
      await query(
        `UPDATE products SET name=$1,category_id=$2,description=$3,price=$4,sale_price=$5,
         stock=$6,sizes=$7,colors=$8,is_featured=$9,is_active=$10,image=$11 WHERE id=$12`,
        [name, category_id, description, price, sale_price || null, stock,
         sizes || '[]', colors || '[]', !!is_featured, is_active !== 'false', req.file.filename, req.params.id]
      );
    } else {
      await query(
        `UPDATE products SET name=$1,category_id=$2,description=$3,price=$4,sale_price=$5,
         stock=$6,sizes=$7,colors=$8,is_featured=$9,is_active=$10 WHERE id=$11`,
        [name, category_id, description, price, sale_price || null, stock,
         sizes || '[]', colors || '[]', !!is_featured, is_active !== 'false', req.params.id]
      );
    }
    res.json({ message: 'Product updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.delete('/:id', adminMiddleware, async (req, res) => {
  try {
    await query('UPDATE products SET is_active=false WHERE id=$1', [req.params.id]);
    res.json({ message: 'Product deleted' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
