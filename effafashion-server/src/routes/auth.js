import { Router } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import db from '../db.js';
import { authMiddleware } from '../middleware/auth.js';
import { uploadAvatar } from '../middleware/upload.js';

const router = Router();

// POST /api/auth/register
router.post('/register', async (req, res) => {
  try {
    const { full_name, email, password, phone } = req.body;
    if (!full_name || !email || !password)
      return res.status(400).json({ message: 'Name, email and password are required' });

    const [existing] = await db.query('SELECT id FROM users WHERE email = ?', [email]);
    if (existing.length) return res.status(409).json({ message: 'Email already registered' });

    const hash = await bcrypt.hash(password, 12);
    const [result] = await db.query(
      'INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)',
      [full_name, email, hash, phone || null]
    );
    const token = jwt.sign({ id: result.insertId, role: 'customer' }, process.env.JWT_SECRET, { expiresIn: '7d' });
    res.status(201).json({ token, user: { id: result.insertId, full_name, email, role: 'customer' } });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/auth/login
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    const [rows] = await db.query('SELECT * FROM users WHERE email = ? AND is_active = 1', [email]);
    if (!rows.length) return res.status(401).json({ message: 'Invalid email or password' });

    const user = rows[0];
    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.status(401).json({ message: 'Invalid email or password' });

    const token = jwt.sign({ id: user.id, role: user.role }, process.env.JWT_SECRET, { expiresIn: '7d' });
    const { password: _, ...safe } = user;
    res.json({ token, user: safe });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/auth/me
router.get('/me', authMiddleware, async (req, res) => {
  try {
    const [rows] = await db.query(
      'SELECT id, full_name, email, phone, address, city, country, role, profile_image, created_at FROM users WHERE id = ?',
      [req.user.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'User not found' });
    res.json(rows[0]);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/auth/profile
router.put('/profile', authMiddleware, uploadAvatar.single('avatar'), async (req, res) => {
  try {
    const { full_name, phone, address, city, country } = req.body;
    const image = req.file ? req.file.filename : undefined;
    const fields = ['full_name = ?', 'phone = ?', 'address = ?', 'city = ?', 'country = ?'];
    const values = [full_name, phone, address, city, country];
    if (image) { fields.push('profile_image = ?'); values.push(image); }
    values.push(req.user.id);
    await db.query(`UPDATE users SET ${fields.join(', ')} WHERE id = ?`, values);
    res.json({ message: 'Profile updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/auth/password
router.put('/password', authMiddleware, async (req, res) => {
  try {
    const { current_password, new_password } = req.body;
    const [rows] = await db.query('SELECT password FROM users WHERE id = ?', [req.user.id]);
    const match = await bcrypt.compare(current_password, rows[0].password);
    if (!match) return res.status(400).json({ message: 'Current password is incorrect' });
    const hash = await bcrypt.hash(new_password, 12);
    await db.query('UPDATE users SET password = ? WHERE id = ?', [hash, req.user.id]);
    res.json({ message: 'Password updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
