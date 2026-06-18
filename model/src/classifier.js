// Core, environment-agnostic logic for the Teachable Machine image model.
// No DOM, no webcam — just load the model, preprocess a pixel tensor, predict.
// Browser-only concerns (webcam, DOM rendering) live in entry points, not here.

import './node-compat.js'; // must precede tfjs-node — see file for why
import * as tf from '@tensorflow/tfjs-node';
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));

// The model + metadata exported by Teachable Machine live alongside this package.
const MODEL_DIR = resolve(__dirname, '..');
const MODEL_URL = `file://${resolve(MODEL_DIR, 'model.json')}`;
const METADATA_PATH = resolve(MODEL_DIR, 'metadata.json');

/**
 * Load the model and its label metadata once, returning a reusable classifier.
 */
export async function loadClassifier() {
  const [model, metadata] = await Promise.all([
    tf.loadLayersModel(MODEL_URL),
    readFile(METADATA_PATH, 'utf8').then(JSON.parse),
  ]);

  const labels = metadata.labels;
  const imageSize = metadata.imageSize ?? 224;

  return { model, labels, imageSize };
}

/**
 * Decode raw image bytes and reshape them into the tensor the model expects:
 * a [1, size, size, 3] batch normalized to the [-1, 1] range Teachable Machine uses.
 */
function preprocess(buffer, imageSize) {
  return tf.tidy(() => {
    const decoded = tf.node.decodeImage(buffer, 3); // [h, w, 3], drops any alpha channel
    const resized = tf.image.resizeBilinear(decoded, [imageSize, imageSize]);
    const normalized = resized.toFloat().div(127.5).sub(1);
    return normalized.expandDims(0);
  });
}

/**
 * Classify the image at `imagePath`. Returns predictions sorted high → low confidence.
 * @returns {Promise<Array<{ className: string, probability: number }>>}
 */
export async function classifyImage(classifier, imagePath) {
  const { model, labels, imageSize } = classifier;
  const buffer = await readFile(imagePath);

  const input = preprocess(buffer, imageSize);
  const output = model.predict(input);
  const probabilities = Array.from(await output.data());

  tf.dispose([input, output]);

  return labels
    .map((className, i) => ({ className, probability: probabilities[i] }))
    .sort((a, b) => b.probability - a.probability);
}
