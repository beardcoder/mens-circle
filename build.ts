/**
 * Bun Build Script
 *
 * Replaces Vite as the frontend bundler. Generates a Vite-compatible
 * manifest.json so Laravel's @vite() Blade directive continues to work.
 */
import { rmSync } from 'node:fs';
import { resolve, relative } from 'node:path';

const projectRoot = import.meta.dir;
const buildDir = resolve(projectRoot, 'public/build');
const assetsDir = resolve(buildDir, 'assets');
const isWatch = process.argv.includes('--watch');
const isDev = process.argv.includes('--dev');

async function build(): Promise<void> {
  const startTime = performance.now();

  // Clean previous build
  rmSync(buildDir, { recursive: true, force: true });

  // Build JavaScript
  const jsResult = await Bun.build({
    entrypoints: [resolve(projectRoot, 'resources/js/app.ts')],
    outdir: assetsDir,
    naming: '[name]-[hash].[ext]',
    minify: !isDev,
    target: 'browser',
    sourcemap: isDev ? 'linked' : 'none',
    define: {
      'import.meta.env.DEV': isDev ? 'true' : 'false',
      'import.meta.env.PROD': isDev ? 'false' : 'true',
    },
  });

  if (!jsResult.success) {
    console.error('JS build failed:');
    for (const log of jsResult.logs) {
      console.error(log);
    }
    process.exit(1);
  }

  // Build CSS
  const cssResult = await Bun.build({
    entrypoints: [resolve(projectRoot, 'resources/css/app.css')],
    outdir: assetsDir,
    naming: '[name]-[hash].[ext]',
    minify: !isDev,
    target: 'browser',
  });

  if (!cssResult.success) {
    console.error('CSS build failed:');
    for (const log of cssResult.logs) {
      console.error(log);
    }
    process.exit(1);
  }

  // Generate Vite-compatible manifest
  const manifest: Record<string, ManifestEntry> = {};

  // Process JS outputs
  for (const output of jsResult.outputs) {
    if (output.kind === 'entry-point') {
      manifest['resources/js/app.ts'] = {
        file: relative(buildDir, output.path),
        name: 'app',
        src: 'resources/js/app.ts',
        isEntry: true,
      };
    }
  }

  // Process CSS outputs (Bun reports CSS entry points with kind "asset" and loader "css")
  for (const output of cssResult.outputs) {
    if (output.loader === 'css') {
      manifest['resources/css/app.css'] = {
        file: relative(buildDir, output.path),
        name: 'app',
        src: 'resources/css/app.css',
        isEntry: true,
      };
    }
  }

  await Bun.write(resolve(buildDir, 'manifest.json'), JSON.stringify(manifest, null, 2));

  const duration = (performance.now() - startTime).toFixed(0);
  const outputCount = jsResult.outputs.length + cssResult.outputs.length;
  console.log(`Build completed in ${duration}ms (${outputCount} files)`);
}

interface ManifestEntry {
  file: string;
  name?: string;
  src: string;
  isEntry?: boolean;
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
