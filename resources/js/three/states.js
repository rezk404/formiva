import { GOLDEN_ANGLE, hash, hashSigned, lerp } from '../lib/math.js';

/**
 * THE STRATA — states
 *
 * One object runs the length of the site: a stack of machined slabs. It is
 * never replaced, only rearranged, and each arrangement is an argument the
 * chapter beneath it is making.
 *
 *   monolith   a single finished form            — the idea, whole
 *   breathe    the same form, barely moving      — the quiet chapters
 *   fan        the stack opens into a system     — services
 *   disperse   the system gains complexity       — the work
 *   stair      complexity resolves into order    — the process
 *   resolve    closed again, but not as it began — the ending
 *
 * A state is a pure function of slab index. Given the same index it returns
 * the same transform every time, on every device, which is what lets two
 * states be blended by interpolating between their outputs rather than by
 * animating anything.
 *
 * Writing into a supplied array keeps this allocation-free — it is called
 * once per slab per frame.
 *
 * ── The vertical constraint ──────────────────────────────────────────────
 *
 * Every state's Y half-extent must exceed `count * SLAB_THICKNESS / 2`, or
 * the slabs occupy the same space and the object renders as an
 * interpenetrating lump with z-fighting along every seam. At the highest
 * slab count (34) that floor is 1.394, so no state below sets a half-extent
 * under about 1.45. `monolith` and `resolve` sit deliberately close to it —
 * a ten-thousandth of a unit of daylight per seam is what makes a stack read
 * as one machined block rather than as a pile of plates.
 */

const TAU = Math.PI * 2;

/**
 * Slab thickness. Declared here rather than in strata.js because the state
 * functions are what depend on it: the geometry can change size freely, but
 * the moment this and the Y extents disagree, the object breaks.
 */
export const SLAB_THICKNESS = 0.082;

// Transform slots in the output array.
export const PX = 0;
export const PY = 1;
export const PZ = 2;
export const RX = 3;
export const RY = 4;
export const RZ = 5;
export const SX = 6;
export const SY = 7;
export const SZ = 8;

export const STRIDE = 9;

/**
 * Writes a full transform.
 *
 * `s` scales width and depth together; thickness is left alone, so a slab
 * that narrows stays the same plate rather than becoming a different object.
 */
function write(out, px, py, pz, rx, ry, rz, s) {
    out[PX] = px;
    out[PY] = py;
    out[PZ] = pz;
    out[RX] = rx;
    out[RY] = ry;
    out[RZ] = rz;
    out[SX] = s;
    out[SY] = 1;
    out[SZ] = s;
    return out;
}

/** Index → -1..1, centred on the middle slab. */
const centred = (i, n) => (n > 1 ? (i / (n - 1)) * 2 - 1 : 0);

/** Index → 0..1. */
const normal = (i, n) => (n > 1 ? i / (n - 1) : 0.5);

/* ── The states ─────────────────────────────────────────────────────────── */

function monolith(i, n, out) {
    const c = centred(i, n);
    // A barrel taper. Identical plates would read as a stack; a form that
    // swells at the waist reads as one machined object.
    const s = 1 - Math.abs(c) ** 1.7 * 0.3;

    return write(out, 0, c * 1.52, 0, 0, c * 0.1, 0, s);
}

function breathe(i, n, out) {
    const c = centred(i, n);
    const s = 1 - Math.abs(c) ** 1.7 * 0.26;

    return write(out, Math.sin(c * Math.PI) * 0.04, c * 1.66, 0, 0, c * 0.16, 0, s);
}

function fan(i, n, out) {
    const t = normal(i, n);
    const c = centred(i, n);

    return write(
        out,
        Math.sin(t * Math.PI * 1.5) * 0.26,
        c * 2.15,
        Math.cos(t * Math.PI * 1.2) * 0.18,
        0,
        c * 1.5,
        0,
        0.7 + Math.sin(t * Math.PI) * 0.34
    );
}

function disperse(i, n, out) {
    const c = centred(i, n);

    // Deterministic scatter. Stable across reloads, identical on every
    // device — a slab must not be somewhere else on a second visit.
    //
    // The vertical jitter is capped at 0.036 rather than eyeballed. Base
    // spacing here is 0.159 and thickness is 0.082, leaving 0.077 of
    // headroom; two neighbours jittering toward each other spend twice the
    // amplitude, so anything above ~0.038 lets them intersect and z-fight.
    // The scatter reads through X, Z and rotation regardless — the Y jitter
    // was only ever breaking the regularity of the seams.
    return write(
        out,
        hashSigned(i, 3) * 0.68,
        c * 2.62 + hashSigned(i, 6) * 0.036,
        hashSigned(i, 4) * 0.5,
        hashSigned(i, 7) * 0.09,
        c * 2.6 + hashSigned(i, 1) * 0.75,
        hashSigned(i, 2) * 0.17,
        0.52 + hash(i, 5) * 0.6
    );
}

function stair(i, n, out) {
    const c = centred(i, n);

    // Every slab now shares one rotation and one scale. The only variation
    // left is a single diagonal — order arrived at, not imposed.
    return write(out, c * 1.2, c * 2.1, c * -0.46, 0, 0.32, 0, 0.86);
}

function resolve(i, n, out) {
    const t = normal(i, n);
    const c = centred(i, n);

    // Closed again, and as tight as the monolith — but indexed on the golden
    // angle rather than aligned. The form has been through something.
    return write(out, 0, c * 1.48, 0, 0, i * GOLDEN_ANGLE * 0.12, 0, 0.8 + Math.sin(t * Math.PI) * 0.28);
}

/* ── Service forms ───────────────────────────────────────────────────────────
   The six services are not six objects. Each is the same stack holding a
   different posture, chosen to say something about the discipline: modular
   work stacks in units, infrastructure interlocks, mobile compresses. */

function modular(i, n, out) {
    const c = centred(i, n);
    const step = Math.PI / 6;

    // Rotation quantised to sixths of a turn: the stack reads as assembled
    // from units rather than swept through an arc.
    return write(out, ((i % 3) - 1) * 0.28, c * 2.0, 0, 0, Math.round((c * 1.4) / step) * step, 0, 0.88);
}

function layered(i, n, out) {
    const c = centred(i, n);

    // Barely any rotation and the widest separation of any state — surfaces
    // sitting above one another, which is what a web build actually is.
    return write(out, 0, c * 2.85, 0, 0, c * 0.06, 0, 1.02);
}

function compressed(i, n, out) {
    const c = centred(i, n);

    // Tight to the floor of the vertical constraint and narrow with it: the
    // same material in a smaller envelope.
    return write(out, 0, c * 1.46, 0, 0, c * 0.6, 0, 0.5);
}

function interfaceForm(i, n, out) {
    const c = centred(i, n);

    // Alternating offsets, no rotation. Reads as rows in a layout.
    return write(out, (i % 2 === 0 ? -1 : 1) * 0.36, c * 1.98, 0, 0, 0, 0, 0.74);
}

function networked(i, n, out) {
    const t = normal(i, n);
    const c = centred(i, n);
    const angle = t * TAU;

    // A helix around a shared axis: many small nodes on one circuit.
    return write(out, Math.cos(angle) * 0.62, c * 1.72, Math.sin(angle) * 0.62, 0, angle, 0, 0.46);
}

function structural(i, n, out) {
    const c = centred(i, n);

    // Alternating quarter turns: slabs read as interlocking rather than
    // stacked, which is the whole idea of a platform.
    return write(out, (i % 2 === 0 ? 0.2 : -0.2), c * 1.9, 0, 0, i % 2 === 0 ? 0 : Math.PI / 2, 0, 0.82);
}

/* ── Registry ────────────────────────────────────────────────────────────────
   Each state carries its own camera. Framing is part of the composition, and
   the distances are derived from each state's height: at 38° vertical FOV a
   camera sees 2·d·tan(19°), so a form 5.2 units tall needs about 8.2 units of
   distance to sit in frame with margin. A dispersed form given a monolith's
   camera would simply run off the top and bottom of the viewport. */

export const STATES = {
    monolith: { slab: monolith, camera: { dist: 6.0, height: 0.05, tilt: 0.05, roll: 0, x: 0.55, targetX: 0 }, light: 0 },
    breathe: { slab: breathe, camera: { dist: 6.4, height: 0.1, tilt: 0.08, roll: 0, x: -0.65, targetX: 0.12 }, light: 0.25 },
    fan: { slab: fan, camera: { dist: 7.4, height: 0.22, tilt: 0.16, roll: 0.02, x: 0.92, targetX: -0.18 }, light: 0.55 },
    disperse: { slab: disperse, camera: { dist: 8.2, height: 0.3, tilt: 0.2, roll: -0.03, x: -0.85, targetX: 0.22 }, light: 0.82 },
    stair: { slab: stair, camera: { dist: 7.6, height: 0.12, tilt: 0.1, roll: 0.04, x: 0.72, targetX: -0.1 }, light: 0.38 },
    resolve: { slab: resolve, camera: { dist: 5.6, height: 0.0, tilt: 0.04, roll: 0, x: 0, targetX: 0 }, light: 1 },

    modular: { slab: modular, camera: { dist: 7.0, height: 0.16, tilt: 0.12, roll: 0, x: 0.9, targetX: -0.12 }, light: 0.55 },
    layered: { slab: layered, camera: { dist: 8.6, height: 0.2, tilt: 0.22, roll: 0, x: 0.7, targetX: 0 }, light: 0.65 },
    compressed: { slab: compressed, camera: { dist: 4.8, height: 0.06, tilt: 0.1, roll: 0, x: 0.5, targetX: 0 }, light: 0.45 },
    interface: { slab: interfaceForm, camera: { dist: 7.0, height: 0.14, tilt: 0.05, roll: 0, x: 0.85, targetX: 0 }, light: 0.62 },
    networked: { slab: networked, camera: { dist: 6.6, height: 0.26, tilt: 0.24, roll: 0, x: 0.55, targetX: 0 }, light: 0.72 },
    structural: { slab: structural, camera: { dist: 6.9, height: 0.12, tilt: 0.14, roll: 0, x: 0.7, targetX: 0 }, light: 0.5 },
};

export const DEFAULT_STATE = 'monolith';

const bufferA = new Float32Array(STRIDE);
const bufferB = new Float32Array(STRIDE);

/**
 * Blends two named states for one slab, writing the result into `out`.
 *
 * Because states are pure functions, transitioning between any two of them
 * is a straight interpolation — there is no timeline to build, nothing to
 * kill when the reader scrolls back, and no possibility of the object being
 * stranded between chapters.
 */
export function blendInto(out, fromName, toName, t, i, n) {
    const from = STATES[fromName] || STATES[DEFAULT_STATE];
    const to = STATES[toName] || STATES[DEFAULT_STATE];

    if (t <= 0) {
        from.slab(i, n, out);
        return out;
    }

    if (t >= 1) {
        to.slab(i, n, out);
        return out;
    }

    from.slab(i, n, bufferA);
    to.slab(i, n, bufferB);

    for (let k = 0; k < STRIDE; k += 1) {
        out[k] = lerp(bufferA[k], bufferB[k], t);
    }

    return out;
}

/** The same blend, for the camera hint attached to each state. */
export function blendCamera(fromName, toName, t) {
    const from = (STATES[fromName] || STATES[DEFAULT_STATE]).camera;
    const to = (STATES[toName] || STATES[DEFAULT_STATE]).camera;

    return {
        dist: lerp(from.dist, to.dist, t),
        height: lerp(from.height, to.height, t),
        tilt: lerp(from.tilt, to.tilt, t),
        roll: lerp(from.roll, to.roll, t),
        x: lerp(from.x ?? 0, to.x ?? 0, t),
        targetX: lerp(from.targetX ?? 0, to.targetX ?? 0, t),
        light: lerp((STATES[fromName] || STATES[DEFAULT_STATE]).light ?? 0, (STATES[toName] || STATES[DEFAULT_STATE]).light ?? 0, t),
    };
}
