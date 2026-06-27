import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

import authRoutes      from './routes/auth.js';
import productRoutes   from './routes/products.js';
import categoryRoutes  from './routes/categories.js';
import cartRoutes      from './routes/cart.js';
import orderRoutes     from './routes/orders.js';
import wishlistRoutes  from './routes/wishlist.js';
import couponRoutes    from './routes/coupons.js';
import adminRoutes     from './routes/admin.js';
import miscRoutes      from './routes/misc.js';

dotenv.config();

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const app  = express();
const PORT = process.env.PORT || 5000;

app.use(cors({ origin: process.env.CLIENT_URL || '*', credentials: true }));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve uploaded files
app.use('/uploads', express.static(path.join(__dirname, '../uploads')));

// Routes
app.use('/api/auth',       authRoutes);
app.use('/api/products',   productRoutes);
app.use('/api/categories', categoryRoutes);
app.use('/api/cart',       cartRoutes);
app.use('/api/orders',     orderRoutes);
app.use('/api/wishlist',   wishlistRoutes);
app.use('/api/coupons',    couponRoutes);
app.use('/api/admin',      adminRoutes);
app.use('/api',            miscRoutes);

app.get('/api/health', (_, res) => res.json({ status: 'ok' }));

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
