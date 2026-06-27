import { Router } from 'express';
import { query } from '../db.js';
import { authMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/', authMiddleware, async (req, res) => {
  try {
    const rows = await query(
      `SELECT c.id,c.quantity,c.size,c.color,c.product_id,
              p.name,p.price,p.sale_price,p.image,p.stock,p.slug
       FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$1`,
      [req.user.id]
    );
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/', authMiddleware, async (req, res) => {
  try {
    const { product_id, quantity = 1, size = '', color = '' } = req.body;
    const prod = await query('SELECT stock FROM products WHERE id=$1 AND is_active=true', [product_id]);
    if (!prod.length) return res.status(404).json({ message: 'Product not found' });
    if (prod[0].stock < 1) return res.status(400).json({ message: 'Out of stock' });
    const existing = await query(
      'SELECT id,quantity FROM cart WHERE user_id=$1 AND product_id=$2 AND size=$3 AND color=$4',
      [req.user.id, product_id, size, color]
    );
    if (existing.length) {
      await query('UPDATE cart SET quantity=quantity+$1 WHERE id=$2', [quantity, existing[0].id]);
    } else {
      await query('INSERT INTO cart (user_id,product_id,quantity,size,color) VALUES ($1,$2,$3,$4,$5)',
        [req.user.id, product_id, quantity, size, color]);
    }
    res.json({ message: 'Added to cart' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/:id', authMiddleware, async (req, res) => {
  try {
    await query('UPDATE cart SET quantity=$1 WHERE id=$2 AND user_id=$3',
      [req.body.quantity, req.params.id, req.user.id]);
    res.json({ message: 'Cart updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.delete('/:id', authMiddleware, async (req, res) => {
  try {
    await query('DELETE FROM cart WHERE id=$1 AND user_id=$2', [req.params.id, req.user.id]);
    res.json({ message: 'Item removed' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.delete('/', authMiddleware, async (req, res) => {
  try {
    await query('DELETE FROM cart WHERE user_id=$1', [req.user.id]);
    res.json({ message: 'Cart cleared' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
