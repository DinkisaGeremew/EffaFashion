import { Router } from 'express';
import db from '../db.js';
import { authMiddleware, adminMiddleware } from '../middleware/auth.js';
import { uploadPayment } from '../middleware/upload.js';
import crypto from 'crypto';

const router = Router();

function genOrderNumber() {
  return 'EFF-' + crypto.randomBytes(4).toString('hex').toUpperCase();
}

// POST /api/orders — place order
router.post('/', authMiddleware, uploadPayment.single('payment_screenshot'), async (req, res) => {
  try {
    const { shipping_name, shipping_email, shipping_phone, shipping_address,
            shipping_city, shipping_country = 'Ethiopia', payment_method = 'bank_transfer',
            notes, coupon_code } = req.body;

    if (!shipping_name || !shipping_email || !shipping_phone || !shipping_address || !shipping_city)
      return res.status(400).json({ message: 'All shipping fields are required' });
    if (!req.file)
      return res.status(400).json({ message: 'Payment screenshot is required' });

    // Get cart
    const [cartItems] = await db.query(
      `SELECT c.quantity, c.size, c.color, c.product_id,
              p.name, p.price, p.sale_price, p.image, p.stock
       FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?`,
      [req.user.id]
    );
    if (!cartItems.length) return res.status(400).json({ message: 'Cart is empty' });

    let subtotal = cartItems.reduce((sum, item) => {
      return sum + (item.sale_price || item.price) * item.quantity;
    }, 0);

    // Coupon
    let discount = 0;
    if (coupon_code) {
      const [coupons] = await db.query(
        `SELECT * FROM coupons WHERE code=? AND is_active=1
         AND (expires_at IS NULL OR expires_at >= CURDATE())
         AND (max_uses IS NULL OR used_count < max_uses)`,
        [coupon_code.toUpperCase()]
      );
      if (coupons.length && subtotal >= coupons[0].min_order) {
        const c = coupons[0];
        discount = c.discount_type === 'percentage'
          ? subtotal * c.discount_value / 100
          : c.discount_value;
        await db.query('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?', [c.id]);
      }
    }

    const shipping_amount = subtotal >= 20000 ? 0 : 2500;
    const total = subtotal - discount + shipping_amount;
    const order_number = genOrderNumber();
    const screenshot = req.file.filename;

    const [order] = await db.query(
      `INSERT INTO orders (user_id, order_number, total_amount, shipping_amount, discount_amount,
        payment_method, payment_status, payment_screenshot, shipping_name, shipping_email,
        shipping_phone, shipping_address, shipping_city, shipping_country, notes)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`,
      [req.user.id, order_number, total, shipping_amount, discount, payment_method,
       'pending_verification', screenshot, shipping_name, shipping_email, shipping_phone,
       shipping_address, shipping_city, shipping_country, notes || '']
    );

    const orderId = order.insertId;
    for (const item of cartItems) {
      const price = item.sale_price || item.price;
      await db.query(
        `INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, size, color)
         VALUES (?,?,?,?,?,?,?,?)`,
        [orderId, item.product_id, item.name, item.image, item.quantity, price, item.size || '', item.color || '']
      );
      await db.query('UPDATE products SET stock = stock - ? WHERE id = ?', [item.quantity, item.product_id]);
    }

    // Clear cart
    await db.query('DELETE FROM cart WHERE user_id = ?', [req.user.id]);

    res.status(201).json({ order_number, order_id: orderId, message: 'Order placed successfully' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/orders — user's orders
router.get('/', authMiddleware, async (req, res) => {
  try {
    const [orders] = await db.query(
      'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC',
      [req.user.id]
    );
    res.json(orders);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/orders/:id
router.get('/:id', authMiddleware, async (req, res) => {
  try {
    const [orders] = await db.query(
      'SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1',
      [req.params.id, req.user.id]
    );
    if (!orders.length) return res.status(404).json({ message: 'Order not found' });
    const [items] = await db.query('SELECT * FROM order_items WHERE order_id = ?', [req.params.id]);
    res.json({ ...orders[0], items });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// ── Admin routes ──────────────────────────────────────────────────────────────

// GET /api/orders/admin/all
router.get('/admin/all', adminMiddleware, async (req, res) => {
  try {
    const { status, page = 1, limit = 20 } = req.query;
    const offset = (page - 1) * limit;
    const where  = status ? 'WHERE o.status = ?' : '';
    const params = status ? [status] : [];
    const [orders] = await db.query(
      `SELECT o.*, u.full_name, u.email FROM orders o
       JOIN users u ON o.user_id = u.id ${where}
       ORDER BY o.created_at DESC LIMIT ? OFFSET ?`,
      [...params, parseInt(limit), offset]
    );
    const [[{ total }]] = await db.query(
      `SELECT COUNT(*) AS total FROM orders o ${where}`, params
    );
    res.json({ orders, total });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/orders/admin/:id
router.put('/admin/:id', adminMiddleware, async (req, res) => {
  try {
    const { status, payment_status } = req.body;
    await db.query('UPDATE orders SET status=?, payment_status=? WHERE id=?',
      [status, payment_status, req.params.id]);
    res.json({ message: 'Order updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
