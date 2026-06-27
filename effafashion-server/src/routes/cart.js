import { Router } from 'express';
import db from '../db.js';
import { authMiddleware } from '../middleware/auth.js';

const router = Router();

// GET /api/cart
router.get('/', authMiddleware, async (req, res) => {
  try {
    const [items] = await db.query(
      `SELECT c.id, c.quantity, c.size, c.color, c.product_id,
              p.name, p.price, p.sale_price, p.image, p.stock, p.slug
       FROM cart c JOIN products p ON c.product_id = p.id
       WHERE c.user_id = ?`,
      [req.user.id]
    );
    res.json(items);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/cart
router.post('/', authMiddleware, async (req, res) => {
  try {
    const { product_id, quantity = 1, size = '', color = '' } = req.body;
    const [[product]] = await db.query('SELECT stock FROM products WHERE id = ? AND is_active = 1', [product_id]);
    if (!product) return res.status(404).json({ message: 'Product not found' });
    if (product.stock < 1) return res.status(400).json({ message: 'Out of stock' });

    const [existing] = await db.query(
      'SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND size=? AND color=?',
      [req.user.id, product_id, size, color]
    );
    if (existing.length) {
      await db.query('UPDATE cart SET quantity = quantity + ? WHERE id = ?', [quantity, existing[0].id]);
    } else {
      await db.query('INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?,?,?,?,?)',
        [req.user.id, product_id, quantity, size, color]);
    }
    res.json({ message: 'Added to cart' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/cart/:id
router.put('/:id', authMiddleware, async (req, res) => {
  try {
    const { quantity } = req.body;
    await db.query('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?',
      [quantity, req.params.id, req.user.id]);
    res.json({ message: 'Cart updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/cart/:id
router.delete('/:id', authMiddleware, async (req, res) => {
  try {
    await db.query('DELETE FROM cart WHERE id = ? AND user_id = ?', [req.params.id, req.user.id]);
    res.json({ message: 'Item removed' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/cart — clear all
router.delete('/', authMiddleware, async (req, res) => {
  try {
    await db.query('DELETE FROM cart WHERE user_id = ?', [req.user.id]);
    res.json({ message: 'Cart cleared' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
