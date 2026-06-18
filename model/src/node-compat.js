// Compatibility shim for newer Node.js (>=20) running @tensorflow/tfjs-node.
// tfjs-node's compiled kernels still call util.isNullOrUndefined(), which Node
// removed from the public `util` API. Restore it before TensorFlow loads.
//
// Must be imported BEFORE '@tensorflow/tfjs-node'. `util` is a process-wide
// singleton, so patching it here also fixes tfjs-node's internal require('util').
import util from 'node:util';

if (typeof util.isNullOrUndefined !== 'function') {
  util.isNullOrUndefined = (value) => value === null || value === undefined;
}
if (typeof util.isNull !== 'function') {
  util.isNull = (value) => value === null;
}
if (typeof util.isUndefined !== 'function') {
  util.isUndefined = (value) => value === undefined;
}
