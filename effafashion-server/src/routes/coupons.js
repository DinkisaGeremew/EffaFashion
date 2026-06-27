import { Router } from 'express';
import db from '../db.js';
import { authMiddleware, adminMiddleware } from '../middleware/auth.js';

const router = Router();

// POST /api/coupons/validate
router.post('/validate', authMiddleware, async (req, res) => {
  try {
    const { code, order_total } = req.body;
    const [rows] = await db.query(
      `SELECT * FROM coupons WHERE code=? AND is_active=1
       AND (expires_at IS NULL OR expires_at >= CURDATE())
       AND (max_uses IS NULL OR used_count < max_uses)`,
      [code.toUpperCase()]
    );
    if (!rows.length) return res.json({ valid: false, message: 'Invalid or expired coupon' });
    const c = rows[0];
    if (order_total < c.min_order)
      return res.json({ valid: false, message: `Minimum order of ETB ${c.min_order} required` });
    const discount = c.discount_type === 'percentage'
      ? order_total * c.discount_value / 100
      : c.discount_value;
    res.json({ valid: true, discount, coupon: c, message: 'Coupon applied!' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Admin CRUD
router.get('/', adminMiddleware, async (req, res) => {
  const [rows] = await db.query('SELECT * FROM coupons ORDER BY created_at DESC');
  res.json(rows);
});

router.post('/', adminMiddleware, async (req, res) => {
  const { code, discount_type, discount_value, min_order, max_uses, expires_at } = req.body;
  await db.query(
    'INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses, expires_at) VALUES (?,?,?,?,?,?)',
    [code.toUpperCase(), discount_type, discount_value, min_order || 0, max_uses || null, expires_at || null]
  );
  res.status(201).json({ message: 'Coupon created' });
});

router.put('/:id', adminMiddleware, async (req, res) => {
  const { is_active } = req.body;
  await db.query('UPDATE coupons SET is_active=? WHERE id=?', [is_active ? 1 : 0, req.params.id]);
  res.json({ message: 'Coupon updated' });
});

export default router;
