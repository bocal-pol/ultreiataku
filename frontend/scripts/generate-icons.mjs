/**
 * generate-icons.mjs
 * Génère les PNG PWA (192×192 et 512×512) depuis public/icons/icon-192.svg
 * Usage: node scripts/generate-icons.mjs
 */

import sharp from 'sharp';
import { readFileSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = resolve(__dirname, '..');
const svgPath = resolve(rootDir, 'public/icons/icon-192.svg');
const svgBuffer = readFileSync(svgPath);

const sizes = [192, 512];

for (const size of sizes) {
  const outPath = resolve(rootDir, `public/icons/icon-${size}.png`);
  await sharp(svgBuffer)
    .resize(size, size)
    .png({ compressionLevel: 9 })
    .toFile(outPath);

  const { size: fileSize } = await import('fs').then(m => m.promises.stat(outPath));
  console.log(`icon-${size}.png — ${size}×${size}px — ${fileSize} bytes`);
}

console.log('Icons generated successfully.');
