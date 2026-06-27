import { Router } from 'express';
import { query } from '../db.js';
import { authMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/', authMiddleware, async (req, res) => {
  try {
    const rows = await query(
      `SELECT p.id,p.name,p.slug,p.price,p.sale_price,p.image,p.stock
       FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=$1`,
      [req.user.id]
    );
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/toggle', authMiddleware, async (req, res) => {
  try {
    const { product_id } = req.body;
    const existing = await query('SELECT id FROM wishlist WHERE user_id=$1 AND product_id=$2', [req.user.id, product_id]);
    if (existing.length) {
      await query('DELETE FROM wishlist WHERE user_id=$1 AND product_id=$2', [req.user.id, product_id]);
      res.json({ status: 'removed' });
    } else {
      await query('INSERT INTO wishlist (user_id,product_id) VALUES ($1,$2)', [req.user.id, product_id]);
      res.json({ status: 'added' });
    }
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
