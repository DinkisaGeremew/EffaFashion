import { Router } from 'express';
import { query } from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/stats', adminMiddleware, async (req, res) => {
  try {
    const rows = await query(`
      SELECT
        (SELECT COUNT(*) FROM orders)                                                          AS total_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status!='cancelled')          AS total_revenue,
        (SELECT COUNT(*) FROM products WHERE is_active=true)                                  AS total_products,
        (SELECT COUNT(*) FROM users WHERE role='customer')                                    AS total_users,
        (SELECT COUNT(*) FROM orders WHERE status='pending')                                  AS pending_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders
         WHERE EXTRACT(MONTH FROM created_at)=EXTRACT(MONTH FROM NOW())
         AND EXTRACT(YEAR FROM created_at)=EXTRACT(YEAR FROM NOW())
         AND status!='cancelled')                                                              AS monthly_revenue
    `);
    res.json(rows[0]);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/chart', adminMiddleware, async (req, res) => {
  try {
    const rows = await query(`
      SELECT TO_CHAR(created_at,'YYYY-MM') AS month,
             COALESCE(SUM(total_amount),0) AS revenue
      FROM orders WHERE status!='cancelled'
        AND created_at >= NOW() - INTERVAL '6 months'
      GROUP BY month ORDER BY month ASC
    `);
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/top-products', adminMiddleware, async (req, res) => {
  try {
    const rows = await query(`
      SELECT p.name,p.image,SUM(oi.quantity) AS sold,SUM(oi.quantity*oi.price) AS revenue
      FROM order_items oi JOIN products p ON oi.product_id=p.id
      GROUP BY oi.product_id,p.name,p.image ORDER BY sold DESC LIMIT 5
    `);
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/users', adminMiddleware, async (req, res) => {
  try {
    const rows = await query('SELECT id,full_name,email,phone,role,is_active,created_at FROM users ORDER BY created_at DESC');
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/users/:id', adminMiddleware, async (req, res) => {
  try {
    const { is_active, role } = req.body;
    await query('UPDATE users SET is_active=$1,role=$2 WHERE id=$3', [!!is_active, role, req.params.id]);
    res.json({ message: 'User updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
