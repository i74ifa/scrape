#!/usr/bin/env node
// CLI entry point. Usage:
//   node cli.js <image> [more images...] [--json] [--top N]
//   classify <image>           (when installed via `npm link` / the "bin" field)

import { loadClassifier, classifyImage } from './src/classifier.js';

function parseArgs(argv) {
  const files = [];
  let json = false;
  let top = Infinity;

  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === '--json') json = true;
    else if (arg === '--top') top = Number(argv[++i]);
    else if (arg === '-h' || arg === '--help') return { help: true };
    else files.push(arg);
  }
  return { files, json, top };
}

const USAGE = `Teachable Machine image classifier

Usage:
  classify <image> [more images...] [options]

Options:
  --top N     Show only the top N predictions per image (default: all)
  --json      Output machine-readable JSON
  -h, --help  Show this help

Examples:
  classify photo.jpg
  classify a.jpg b.png --top 3
  classify ./pics/*.jpg --json`;

async function main() {
  const { files, json, top, help } = parseArgs(process.argv.slice(2));

  if (help || !files || files.length === 0) {
    console.log(USAGE);
    process.exit(help ? 0 : 1);
  }

  const classifier = await loadClassifier();
  const results = [];

  for (const file of files) {
    try {
      const predictions = await classifyImage(classifier, file);
      const top1 = predictions[0];
      results.push({ file, predictions, error: null });

      if (!json) {
        console.log(`\n${file}`);
        console.log(`  → ${top1.className} (${(top1.probability * 100).toFixed(1)}%)`);
        for (const p of predictions.slice(1, top)) {
          console.log(`    ${p.className}: ${(p.probability * 100).toFixed(1)}%`);
        }
      }
    } catch (err) {
      results.push({ file, predictions: null, error: err.message });
      if (!json) console.error(`\n${file}\n  ✗ ${err.message}`);
    }
  }

  if (json) {
    const trimmed = results.map((r) => ({
      ...r,
      predictions: r.predictions ? r.predictions.slice(0, top) : null,
    }));
    console.log(JSON.stringify(trimmed, null, 2));
  }

  if (results.some((r) => r.error)) process.exit(1);
}

main().catch((err) => {
  console.error('Fatal:', err.message);
  process.exit(1);
});
