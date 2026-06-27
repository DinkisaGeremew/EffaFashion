import { Router } from 'express';
import db from '../db.js';
import { authMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/', authMiddleware, async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.image, p.stock
       FROM wishlist w JOIN products p ON w.product_id = p.id
       WHERE w.user_id = ?`,
      [req.user.id]
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

router.post('/toggle', authMiddleware, async (req, res) => {
  try {
    const { product_id } = req.body;
    const [existing] = await db.query(
      'SELECT id FROM wishlist WHERE user_id=? AND product_id=?',
      [req.user.id, product_id]
    );
    if (existing.length) {
      await db.query('DELETE FROM wishlist WHERE user_id=? AND product_id=?', [req.user.id, product_id]);
      res.json({ status: 'removed' });
    } else {
      await db.query('INSERT INTO wishlist (user_id, product_id) VALUES (?,?)', [req.user.id, product_id]);
      res.json({ status: 'added' });
    }
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
