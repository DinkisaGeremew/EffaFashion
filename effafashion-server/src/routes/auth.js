import { Router } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { query } from '../db.js';
import { authMiddleware } from '../middleware/auth.js';
import { uploadAvatar } from '../middleware/upload.js';

const router = Router();

router.post('/register', async (req, res) => {
  try {
    const { full_name, email, password, phone } = req.body;
    if (!full_name || !email || !password)
      return res.status(400).json({ message: 'Name, email and password are required' });
    const existing = await query('SELECT id FROM users WHERE email = $1', [email]);
    if (existing.length) return res.status(409).json({ message: 'Email already registered' });
    const hash = await bcrypt.hash(password, 12);
    const rows = await query(
      'INSERT INTO users (full_name, email, password, phone) VALUES ($1,$2,$3,$4) RETURNING id',
      [full_name, email, hash, phone || null]
    );
    const token = jwt.sign({ id: rows[0].id, role: 'customer' }, process.env.JWT_SECRET, { expiresIn: '7d' });
    res.status(201).json({ token, user: { id: rows[0].id, full_name, email, role: 'customer' } });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    const rows = await query('SELECT * FROM users WHERE email=$1 AND is_active=true', [email]);
    if (!rows.length) return res.status(401).json({ message: 'Invalid email or password' });
    const user  = rows[0];
    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.status(401).json({ message: 'Invalid email or password' });
    const token = jwt.sign({ id: user.id, role: user.role }, process.env.JWT_SECRET, { expiresIn: '7d' });
    const { password: _, ...safe } = user;
    res.json({ token, user: safe });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.get('/me', authMiddleware, async (req, res) => {
  try {
    const rows = await query(
      'SELECT id,full_name,email,phone,address,city,country,role,profile_image,created_at FROM users WHERE id=$1',
      [req.user.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'User not found' });
    res.json(rows[0]);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/profile', authMiddleware, uploadAvatar.single('avatar'), async (req, res) => {
  try {
    const { full_name, phone, address, city, country } = req.body;
    const image = req.file ? req.file.filename : undefined;
    if (image) {
      await query('UPDATE users SET full_name=$1,phone=$2,address=$3,city=$4,country=$5,profile_image=$6 WHERE id=$7',
        [full_name, phone, address, city, country, image, req.user.id]);
    } else {
      await query('UPDATE users SET full_name=$1,phone=$2,address=$3,city=$4,country=$5 WHERE id=$6',
        [full_name, phone, address, city, country, req.user.id]);
    }
    res.json({ message: 'Profile updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/password', authMiddleware, async (req, res) => {
  try {
    const { current_password, new_password } = req.body;
    const rows  = await query('SELECT password FROM users WHERE id=$1', [req.user.id]);
    const match = await bcrypt.compare(current_password, rows[0].password);
    if (!match) return res.status(400).json({ message: 'Current password is incorrect' });
    const hash = await bcrypt.hash(new_password, 12);
    await query('UPDATE users SET password=$1 WHERE id=$2', [hash, req.user.id]);
    res.json({ message: 'Password updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
