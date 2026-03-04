/**
 * Bun Build Script
 *
 * Bundles JS and CSS with plain filenames. Cache busting is handled
 * via ?v= query parameters in Blade templates using filemtime().
 */
import { rmSync } from 'node:fs';
import { resolve } from 'node:path';

const projectRoot = import.meta.dir;
const outdir = resolve(projectRoot, 'public/build');
const isWatch = process.argv.includes('--watch');
const isDev = process.argv.includes('--dev');

async function build(): Promise<void> {
  const startTime = performance.now();

  rmSync(outdir, { recursive: true, force: true });

  const [jsResult, cssResult] = await Promise.all([
    Bun.build({
      entrypoints: [resolve(projectRoot, 'resources/js/app.ts')],
      outdir,
      naming: '[name].[ext]',
      minify: !isDev,
      target: 'browser',
      sourcemap: isDev ? 'linked' : 'none',
      define: {
        'import.meta.env.DEV': isDev ? 'true' : 'false',
        'import.meta.env.PROD': isDev ? 'false' : 'true',
      },
    }),
    Bun.build({
      entrypoints: [resolve(projectRoot, 'resources/css/app.css')],
      outdir,
      naming: '[name].[ext]',
      minify: !isDev,
      target: 'browser',
    }),
  ]);

  for (const result of [jsResult, cssResult]) {
    if (!result.success) {
      for (const log of result.logs) {
        console.error(log);
      }
      process.exit(1);
    }
  }

  const duration = (performance.now() - startTime).toFixed(0);
  console.log(`Build completed in ${duration}ms`);
}

await build();

if (isWatch) {
  const { watch } = await import('node:fs');
  const dirs = ['resources/js', 'resources/css'];

  console.log('Watching for changes...');

  for (const dir of dirs) {
    watch(resolve(projectRoot, dir), { recursive: true }, async (_event, filename) => {
      if (!filename) return;
      console.log(`Changed: ${dir}/${filename}`);
      try {
        await build();
      } catch (error) {
        console.error('Build error:', error);
      }
    });
  }
}
