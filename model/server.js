#!/usr/bin/env node
// Persistent classification daemon.
//
// Loads @tensorflow/tfjs-node and the model ONCE, then answers many requests
// over HTTP. This amortizes the ~700ms-1s tfjs-node cold start that the one-shot
// CLI pays on every invocation, so each subsequent classification is ~50ms.
//
// Usage:
//   node server.js [--port N] [--host H]
//   PORT=8765 HOST=127.0.0.1 node server.js
//
// Endpoints:
//   GET  /health   -> { ok: true }
//   POST /classify -> body { files: string[], top?: number }
//                     returns the same shape as cli.js --json:
//                     [{ file, predictions, error }]

import http from 'node:http';
import { loadClassifier, classifyImage } from './src/classifier.js';

function parseArgs(argv) {
  let port = Number(process.env.PORT) || 8765;
  let host = process.env.HOST || '127.0.0.1';
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === '--port') port = Number(argv[++i]);
    else if (argv[i] === '--host') host = argv[++i];
  }
  return { port, host };
}

function readBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    let size = 0;
    req.on('data', (c) => {
      size += c.length;
      if (size > 5 * 1024 * 1024) {
        reject(new Error('Request body too large'));
        req.destroy();
        return;
      }
      chunks.push(c);
    });
    req.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')));
    req.on('error', reject);
  });
}

function sendJson(res, status, payload) {
  const body = JSON.stringify(payload);
  res.writeHead(status, {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(body),
  });
  res.end(body);
}

async function main() {
  const { port, host } = parseArgs(process.argv.slice(2));

  // Pay the cold start once, up front.
  const t0 = Date.now();
  const classifier = await loadClassifier();
  console.error(`Model loaded in ${Date.now() - t0}ms`);

  const server = http.createServer(async (req, res) => {
    if (req.method === 'GET' && req.url === '/health') {
      return sendJson(res, 200, { ok: true });
    }

    if (req.method === 'POST' && req.url === '/classify') {
      let payload;
      try {
        payload = JSON.parse((await readBody(req)) || '{}');
      } catch {
        return sendJson(res, 400, { error: 'Invalid JSON body' });
      }

      const files = Array.isArray(payload.files) ? payload.files : [];
      const top = Number.isFinite(payload.top) ? payload.top : Infinity;

      if (files.length === 0) {
        return sendJson(res, 400, { error: 'No files provided' });
      }

      const results = [];
      for (const file of files) {
        try {
          const predictions = await classifyImage(classifier, file);
          results.push({
            file,
            predictions: predictions.slice(0, top),
            error: null,
          });
        } catch (err) {
          results.push({ file, predictions: null, error: err.message });
        }
      }

      return sendJson(res, 200, results);
    }

    sendJson(res, 404, { error: 'Not found' });
  });

  server.listen(port, host, () => {
    console.error(`Classifier daemon listening on http://${host}:${port}`);
  });

  const shutdown = () => server.close(() => process.exit(0));
  process.on('SIGINT', shutdown);
  process.on('SIGTERM', shutdown);
}

main().catch((err) => {
  console.error('Fatal:', err.message);
  process.exit(1);
});
