import { Router } from 'express';
import { query } from '../db.js';

const router = Router();

router.post('/newsletter', async (req, res) => {
  try {
    const { email } = req.body;
    if (!email) return res.status(400).json({ message: 'Email required' });
    await query('INSERT INTO newsletter (email) VALUES ($1) ON CONFLICT (email) DO NOTHING', [email]);
    res.json({ message: 'Subscribed successfully' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/contact', async (req, res) => {
  try {
    const { name, email, subject, message } = req.body;
    if (!name || !email || !message) return res.status(400).json({ message: 'Name, email and message required' });
    await query('INSERT INTO contact_messages (name,email,subject,message) VALUES ($1,$2,$3,$4)',
      [name, email, subject || '', message]);
    res.json({ message: 'Message sent successfully' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/reviews', async (req, res) => {
  try {
    const { product_id, user_id, rating, title, comment } = req.body;
    await query('INSERT INTO reviews (product_id,user_id,rating,title,comment) VALUES ($1,$2,$3,$4,$5)',
      [product_id, user_id, rating, title || '', comment || '']);
    res.status(201).json({ message: 'Review submitted' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
