import { Router } from 'express';
import db from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';

const router = Router();

// GET /api/admin/stats
router.get('/stats', adminMiddleware, async (req, res) => {
  try {
    const [[stats]] = await db.query(`
      SELECT
        (SELECT COUNT(*) FROM orders)                                                        AS total_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled')      AS total_revenue,
        (SELECT COUNT(*) FROM products WHERE is_active=1)                                   AS total_products,
        (SELECT COUNT(*) FROM users WHERE role='customer')                                  AS total_users,
        (SELECT COUNT(*) FROM orders WHERE status='pending')                                AS pending_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders
         WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())
         AND status != 'cancelled')                                                          AS monthly_revenue
    `);
    res.json(stats);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/admin/chart — last 6 months revenue
router.get('/chart', adminMiddleware, async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
             COALESCE(SUM(total_amount),0)   AS revenue
      FROM orders WHERE status != 'cancelled'
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
      GROUP BY month ORDER BY month ASC
    `);
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/admin/top-products
router.get('/top-products', adminMiddleware, async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT p.name, p.image, SUM(oi.quantity) AS sold,
             SUM(oi.quantity * oi.price)        AS revenue
      FROM order_items oi JOIN products p ON oi.product_id = p.id
      GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5
    `);
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/admin/users
router.get('/users', adminMiddleware, async (req, res) => {
  try {
    const [rows] = await db.query(
      'SELECT id, full_name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC'
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/admin/users/:id
router.put('/users/:id', adminMiddleware, async (req, res) => {
  try {
    const { is_active, role } = req.body;
    await db.query('UPDATE users SET is_active=?, role=? WHERE id=?', [is_active ? 1 : 0, role, req.params.id]);
    res.json({ message: 'User updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
