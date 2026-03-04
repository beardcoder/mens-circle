/**
 * Bun Build Script
 *
 * Bundles JS and CSS with plain filenames. Cache busting is handled
 * via ?v= query parameters in Blade templates using filemtime().
 * Font files are copied separately for proper preloading.
 */
import { mkdirSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';
import { Glob } from 'bun';

const projectRoot = import.meta.dir;
const outdir = resolve(projectRoot, 'public/build');
const fontsDir = resolve(outdir, 'fonts');
const isWatch = process.argv.includes('--watch');
const isDev = process.argv.includes('--dev');

const fontSources = [
  'node_modules/@fontsource-variable/dm-sans/files/*-wght-normal.woff2',
  'node_modules/@fontsource-variable/playfair-display/files/*-wght-normal.woff2',
];

async function copyFontsCss(): Promise<void> {
  const src = resolve(projectRoot, 'resources/css/base/_fonts.css');
  const dest = resolve(outdir, 'fonts.css');

  const file = Bun.file(src);
  if (!(await file.exists())) {
    throw new Error(`Font CSS source not found: ${src}`);
  }

  await Bun.write(dest, file);
}

async function copyFonts(): Promise<void> {
  mkdirSync(fontsDir, { recursive: true });

  for (const pattern of fontSources) {
    const glob = new Glob(pattern);

    for await (const path of glob.scan({ cwd: projectRoot })) {
      const filename = path.split('/').pop()!;

      await Bun.write(
        resolve(fontsDir, filename),
        Bun.file(resolve(projectRoot, path))
      );
    }
  }
}

async function build(): Promise<void> {
  const startTime = performance.now();

  rmSync(outdir, { recursive: true, force: true });

  await Promise.all([
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
    }).then((result) => {
      if (!result.success) {
        for (const log of result.logs) console.error(log);
        process.exit(1);
      }
    }),
    Bun.build({
      entrypoints: [resolve(projectRoot, 'resources/css/app.css')],
      outdir,
      naming: '[name].[ext]',
      minify: !isDev,
      target: 'browser',
      external: ['/build/fonts/*'],
    }).then((result) => {
      if (!result.success) {
        for (const log of result.logs) console.error(log);
        process.exit(1);
      }
    }),
    copyFonts(),
    copyFontsCss(),
  ]);

  const duration = (performance.now() - startTime).toFixed(0);

  console.log(`Build completed in ${duration}ms`);
}

await build();

if (isWatch) {
  const { watch } = await import('node:fs');
  const dirs = ['resources/js', 'resources/css'];

  console.log('Watching for changes...');

  for (const dir of dirs) {
    watch(
      resolve(projectRoot, dir),
      { recursive: true },
      async (_event, filename) => {
        if (!filename) return;
        console.log(`Changed: ${dir}/${filename}`);
        try {
          await build();
        } catch (error) {
          console.error('Build error:', error);
        }
      }
    );
  }
}
