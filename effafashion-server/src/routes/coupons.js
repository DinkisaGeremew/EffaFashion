import { Router } from 'express';
import { query } from '../db.js';
import { authMiddleware, adminMiddleware } from '../middleware/auth.js';

const router = Router();

router.post('/validate', authMiddleware, async (req, res) => {
  try {
    const { code, order_total } = req.body;
    const rows = await query(
      `SELECT * FROM coupons WHERE code=$1 AND is_active=true
       AND (expires_at IS NULL OR expires_at >= CURRENT_DATE)
       AND (max_uses IS NULL OR used_count < max_uses)`,
      [code.toUpperCase()]
    );
    if (!rows.length) return res.json({ valid: false, message: 'Invalid or expired coupon' });
    const c = rows[0];
    if (order_total < c.min_order)
      return res.json({ valid: false, message: `Minimum order of ETB ${c.min_order} required` });
    const discount = c.discount_type === 'percentage'
      ? order_total * c.discount_value / 100 : c.discount_value;
    res.json({ valid: true, discount, coupon: c, message: 'Coupon applied!' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/', adminMiddleware, async (req, res) => {
  const rows = await query('SELECT * FROM coupons ORDER BY created_at DESC');
  res.json(rows);
});

router.post('/', adminMiddleware, async (req, res) => {
  const { code, discount_type, discount_value, min_order, max_uses, expires_at } = req.body;
  await query(
    'INSERT INTO coupons (code,discount_type,discount_value,min_order,max_uses,expires_at) VALUES ($1,$2,$3,$4,$5,$6)',
    [code.toUpperCase(), discount_type, discount_value, min_order || 0, max_uses || null, expires_at || null]
  );
  res.status(201).json({ message: 'Coupon created' });
});

router.put('/:id', adminMiddleware, async (req, res) => {
  await query('UPDATE coupons SET is_active=$1 WHERE id=$2', [!!req.body.is_active, req.params.id]);
  res.json({ message: 'Coupon updated' });
});

export default router;
