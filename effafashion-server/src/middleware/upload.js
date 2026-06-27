import multer from 'multer';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function makeStorage(folder) {
  const dir = path.join(__dirname, '../../uploads', folder);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  return multer.diskStorage({
    destination: (_, __, cb) => cb(null, dir),
    filename:    (_, file, cb) => {
      const ext  = path.extname(file.originalname).toLowerCase();
      const name = Date.now() + '_' + Math.random().toString(36).slice(2) + ext;
      cb(null, name);
    },
  });
}

const imageFilter = (_, file, cb) => {
  const allowed = ['.jpg', '.jpeg', '.png', '.webp'];
  cb(null, allowed.includes(path.extname(file.originalname).toLowerCase()));
};

const paymentFilter = (_, file, cb) => {
  const allowed = ['.jpg', '.jpeg', '.png', '.webp', '.pdf'];
  cb(null, allowed.includes(path.extname(file.originalname).toLowerCase()));
};

export const uploadProduct  = multer({ storage: makeStorage('products'),  fileFilter: imageFilter,  limits: { fileSize: 5 * 1024 * 1024 } });
export const uploadAvatar   = multer({ storage: makeStorage('avatars'),   fileFilter: imageFilter,  limits: { fileSize: 2 * 1024 * 1024 } });
export const uploadPayment  = multer({ storage: makeStorage('payments'),  fileFilter: paymentFilter, limits: { fileSize: 5 * 1024 * 1024 } });
