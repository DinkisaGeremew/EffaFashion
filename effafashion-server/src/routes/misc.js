import { Router } from 'express';
import db from '../db.js';

const router = Router();

// POST /api/newsletter
router.post('/newsletter', async (req, res) => {
  try {
    const { email } = req.body;
    if (!email) return res.status(400).json({ message: 'Email required' });
    await db.query('INSERT IGNORE INTO newsletter (email) VALUES (?)', [email]);
    res.json({ message: 'Subscribed successfully' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/contact
router.post('/contact', async (req, res) => {
  try {
    const { name, email, subject, message } = req.body;
    if (!name || !email || !message) return res.status(400).json({ message: 'Name, email and message required' });
    await db.query(
      'INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)',
      [name, email, subject || '', message]
    );
    res.json({ message: 'Message sent successfully' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/reviews
router.post('/reviews', async (req, res) => {
  try {
    const { product_id, user_id, rating, title, comment } = req.body;
    await db.query(
      'INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES (?,?,?,?,?)',
      [product_id, user_id, rating, title || '', comment || '']
    );
    res.status(201).json({ message: 'Review submitted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
