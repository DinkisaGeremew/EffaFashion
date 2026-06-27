import { Router } from 'express';
import slugify from 'slugify';
import { query } from '../db.js';
import { adminMiddleware } from '../middleware/auth.js';

const router = Router();

router.get('/', async (req, res) => {
  try {
    const rows = await query('SELECT * FROM categories WHERE is_active=true ORDER BY name ASC');
    res.json(rows);
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.post('/', adminMiddleware, async (req, res) => {
  try {
    const { name, description } = req.body;
    const slug = slugify(name, { lower: true, strict: true });
    const rows = await query(
      'INSERT INTO categories (name,slug,description) VALUES ($1,$2,$3) RETURNING id',
      [name, slug, description || '']
    );
    res.status(201).json({ id: rows[0].id, message: 'Category created' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

router.put('/:id', adminMiddleware, async (req, res) => {
  try {
    const { name, description, is_active } = req.body;
    await query('UPDATE categories SET name=$1,description=$2,is_active=$3 WHERE id=$4',
      [name, description, !!is_active, req.params.id]);
    res.json({ message: 'Category updated' });
  } catch (err) { res.status(500).json({ message: err.message }); }
});

export default router;
