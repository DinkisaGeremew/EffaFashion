import { Router } from 'express';
import { query } from '../db.js';
import { authMiddleware, adminMiddleware } from '../middleware/auth.js';
import { uploadPayment } from '../middleware/upload.js';
import crypto from 'crypto';

const router = Router();

function genOrderNumber() {
  return 'EFF-' + crypto.randomBytes(4).toString('hex').toUpperCase();
}

router.post('/', authMiddleware, uploadPayment.single('payment_screenshot'), async (req, res) => {
  try {
    const { shipping_name, shipping_email, shipping_phone, shipping_address,
            shipping_city, shipping_country = 'Ethiopia', payment_method = 'bank_transfer',
            notes, coupon_code } = req.body;

    if (!shipping_name || !shipping_email || !shipping_phone || !shipping_address || !shipping_city)
      return res.status(400).json({ message: 'All shipping fields are required' });
    if (!req.file)
      return res.status(400).json({ message: 'Payment screenshot is required' });

    const cartItems = await query(
      `SELECT c.quantity,c.size,c.color,c.product_id,
              p.name,p.price,p.sale_price,p.image,p.stock
       FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$1`,
      [req.user.id]
    );
    if (!cartItems.length) return res.status(400).json({ message: 'Cart is empty' });

    let subtotal = cartItems.reduce((s, i) => s + (parseFloat(i.sale_price) || parseFloat(i.price)) * i.quantity, 0);
    let discount = 0;

    if (coupon_code) {
      const coupons = await query(
        `SELECT * FROM coupons WHERE code=$1 AND is_active=true
         AND (expires_at IS NULL OR expires_at >= CURRENT_DATE)
         AND (max_uses IS NULL OR used_count < max_uses)`,
        [coupon_code.toUpperCase()]
      );
      if (coupons.length && subtotal >= coupons[0].min_order) {
        const c = coupons[0];
        discount = c.discount_type === 'percentage'
          ? subtotal * c.discount_value / 100 : c.discount_value;
        await query('UPDATE coupons SET used_count=used_count+1 WHERE id=$1', [c.id]);
      }
    }

    const shipping_amount = subtotal >= 20000 ? 0 : 2500;
    const total           = subtotal - discount + shipping_amount;
    const order_number    = genOrderNumber();
    const screenshot      = req.file.filename;

    const orderRows = await query(
      `INSERT INTO orders (user_id,order_number,total_amount,shipping_amount,discount_amount,
        payment_method,payment_status,payment_screenshot,shipping_name,shipping_email,
        shipping_phone,shipping_address,shipping_city,shipping_country,notes)
       VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15) RETURNING id`,
      [req.user.id, order_number, total, shipping_amount, discount, payment_method,
       'pending_verification', screenshot, shipping_name, shipping_email, shipping_phone,
       shipping_address, shipping_city, shipping_country, notes || '']
    );
    const orderId = orderRows[0].id;

    for (const item of cartItems) {
      const price = parseFloat(item.sale_price) || parseFloat(item.price);
      await query(
        `INSERT INTO order_items (order_id,product_id,product_name,product_image,quantity,price,size,color)
         VALUES ($1,$2,$3,$4,$5,$6,$7,$8)`,
        [orderId, item.product_id, item.name, item.image, item.quantity, price, item.size || '', item.color || '']
      );
      await query('UPDATE products SET stock=stock-$1 WHERE id=$2', [item.quantity, item.product_id]);
    }
    await query('DELETE FROM cart WHERE user_id=$1', [req.user.id]);

    res.status(201).json({ order_number, order_id: orderId, message: 'Order placed successfully' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/', authMiddleware, async (req, res) => {
  try {
    const rows = await query('SELECT * FROM orders WHERE user_id=$1 ORDER BY created_at DESC', [req.user.id]);
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/admin/all', adminMiddleware, async (req, res) => {
  try {
    const { status, page = 1, limit = 20 } = req.query;
    const offset = (page - 1) * limit;
    const params = [];
    let   where  = '';
    if (status) { where = 'WHERE o.status=$1'; params.push(status); }
    const orders = await query(
      `SELECT o.*,u.full_name,u.email FROM orders o
       JOIN users u ON o.user_id=u.id ${where}
       ORDER BY o.created_at DESC LIMIT $${params.length+1} OFFSET $${params.length+2}`,
      [...params, parseInt(limit), offset]
    );
    const countRows = await query(`SELECT COUNT(*) AS total FROM orders o ${where}`, params);
    res.json({ orders, total: parseInt(countRows[0].total) });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/admin/:id', adminMiddleware, async (req, res) => {
  try {
    const { status, payment_status } = req.body;
    await query('UPDATE orders SET status=$1,payment_status=$2 WHERE id=$3',
      [status, payment_status, req.params.id]);
    res.json({ message: 'Order updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/:id', authMiddleware, async (req, res) => {
  try {
    const orders = await query('SELECT * FROM orders WHERE id=$1 AND user_id=$2', [req.params.id, req.user.id]);
    if (!orders.length) return res.status(404).json({ message: 'Order not found' });
    const items = await query('SELECT * FROM order_items WHERE order_id=$1', [req.params.id]);
    res.json({ ...orders[0], items });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
