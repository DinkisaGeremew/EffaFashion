import pkg from 'pg';
import dotenv from 'dotenv';
dotenv.config();

const { Pool } = pkg;

const isProduction = process.env.NODE_ENV === 'production' || process.env.DATABASE_URL?.includes('render.com') || process.env.DATABASE_URL?.includes('amazonaws');

const pool = new Pool(
  process.env.DATABASE_URL
    ? {
        connectionString: process.env.DATABASE_URL,
        ssl: isProduction ? { rejectUnauthorized: false } : false,
      }
    : {
        host:     process.env.DB_HOST     || 'localhost',
        port:     parseInt(process.env.DB_PORT || '5432'),
        user:     process.env.DB_USER     || 'postgres',
        password: process.env.DB_PASS     || '',
        database: process.env.DB_NAME     || 'effafashion',
        ssl:      false,
      }
);

// Helper: run a query and return rows (mimics mysql2 style)
export async function query(text, params) {
  const res = await pool.query(text, params);
  return res.rows;
}

export default pool;
