import { Router } from 'express';
import slugify from 'slugify';
import db from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/', async (req, res) => {
  try {
    const [rows] = await db.query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC');
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

router.post('/', adminMiddleware, async (req, res) => {
  try {
    const { name, description } = req.body;
    const slug = slugify(name, { lower: true, strict: true });
    const [result] = await db.query(
      'INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)',
      [name, slug, description || '']
    );
    res.status(201).json({ id: result.insertId, message: 'Category created' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

router.put('/:id', adminMiddleware, async (req, res) => {
  try {
    const { name, description, is_active } = req.body;
    await db.query('UPDATE categories SET name=?, description=?, is_active=? WHERE id=?',
      [name, description, is_active ? 1 : 0, req.params.id]);
    res.json({ message: 'Category updated' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

export default router;
