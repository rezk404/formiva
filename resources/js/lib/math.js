/** Small numeric helpers shared by the motion and 3D layers. */

export const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

export const lerp = (a, b, t) => a + (b - a) * t;

/**
 * Frame-rate independent smoothing.
 *
 * A plain `lerp(current, target, 0.1)` per frame moves at a different speed
 * on a 60Hz screen than on a 144Hz one. This converges at the same rate in
 * seconds regardless of frame timing, which is why the world feels the same
 * weight on every machine.
 */
export const damp = (current, target, lambda, dt) =>
    lerp(current, target, 1 - Math.exp(-lambda * dt));

/**
 * Deterministic per-index noise in 0..1.
 *
 * The dispersed states need variation that is stable across reloads and
 * identical on every device — a slab must not land somewhere different on a
 * second visit. Math.random would break that, so the "randomness" is a hash
 * of the slab index and a salt.
 */
export const hash = (index, salt = 0) => {
    let h = Math.imul(index + 1, 0x27d4eb2d) ^ Math.imul(salt + 1, 0x165667b1);
    h = Math.imul(h ^ (h >>> 15), 0x2c1b3c6d);
    h = Math.imul(h ^ (h >>> 12), 0x297a2d39);
    h ^= h >>> 15;
    return (h >>> 0) / 4294967295;
};

/** Signed variant of `hash`, in -1..1. */
export const hashSigned = (index, salt = 0) => hash(index, salt) * 2 - 1;

export const GOLDEN_ANGLE = 2.399963229728653;
