import { GOLDEN_ANGLE, hash, hashSigned, lerp, clamp } from '../lib/math.js';

/**
 * THE FRAME — states
 *
 * FORMIVA's own story is IDEA → FORM → SYSTEM. This object is that story,
 * not a decoration next to it: a lattice of structural bars that begins as
 * scattered fragments, connects into a drawn wireframe, organises into a
 * rack of solid modules, explodes into an axonometric diagram, resolves
 * into an elevation, and finally stands complete.
 *
 * The lattice is a grid of nodes; every bar is the edge between two
 * adjacent nodes, horizontal or vertical. A state is a pure function of a
 * bar's index — given the same index it returns the same transform every
 * time, on every device — which is what lets two states be blended by
 * interpolating their outputs rather than animating anything.
 *
 * `n` (the instance count) always equals 2·N·(N-1) for a grid resolution
 * N, so N is recovered from `n` rather than threaded through every call —
 * see `gridSize`. This keeps the (i, n, out) contract identical to the
 * geometry layer and the director above it.
 */

const HALF_W = 1.7;
const HALF_H = 1.9;

export const BEAM_THICKNESS = 0.05;

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

/** Recovers the grid resolution from a bar count: n = 2N(N-1). */
export function gridSize(n) {
    return Math.max(2, Math.round((1 + Math.sqrt(1 + 2 * n)) / 2));
}

/** Node count for a grid resolution — geometry.js uses this to size the mesh. */
export function beamCount(N) {
    return 2 * N * (N - 1);
}

function nodeX(col, N) {
    return lerp(-HALF_W, HALF_W, N > 1 ? col / (N - 1) : 0.5);
}

function nodeY(row, N) {
    return lerp(-HALF_H, HALF_H, N > 1 ? row / (N - 1) : 0.5);
}

/**
 * A bar's fixed identity: which grid edge it is. Computed from the index
 * alone, so geometry setup and every state function agree on the same
 * mapping without sharing mutable state.
 */
function identify(i, n) {
    const N = gridSize(n);
    const perRow = N - 1;
    const half = N * perRow;

    if (i < half) {
        return { horizontal: true, N, col: i % perRow, row: Math.floor(i / perRow) };
    }

    const j = i - half;
    return { horizontal: false, N, col: Math.floor(j / perRow), row: j % perRow };
}

/** The bar's resting pose on the flat, connected grid. */
function gridPose(i, n) {
    const { horizontal, N, col, row } = identify(i, n);

    if (horizontal) {
        const x0 = nodeX(col, N);
        const x1 = nodeX(col + 1, N);
        return { cx: (x0 + x1) / 2, cy: nodeY(row, N), length: x1 - x0, rz: 0, col, row, N, horizontal };
    }

    const y0 = nodeY(row, N);
    const y1 = nodeY(row + 1, N);
    return { cx: nodeX(col, N), cy: (y0 + y1) / 2, length: y1 - y0, rz: Math.PI / 2, col, row, N, horizontal };
}

/**
 * Writes a full transform. `sx` is the bar's length in world units (the
 * base geometry is one unit long); `sThick` scales its cross-section —
 * thinner reads as a drawn line, thicker reads as a built module.
 */
function write(out, px, py, pz, rx, ry, rz, sx, sThick = 1) {
    out[PX] = px;
    out[PY] = py;
    out[PZ] = pz;
    out[RX] = rx;
    out[RY] = ry;
    out[RZ] = rz;
    out[SX] = sx;
    out[SY] = sThick;
    out[SZ] = sThick;
    return out;
}

/* ── The states ─────────────────────────────────────────────────────────── */

/** IDEA — mostly scattered stubs; a fifth of the bars have already found
 *  their place, a hint of the form the rest are about to take. */
function fragments(i, n, out) {
    const pose = gridPose(i, n);
    const settled = hash(i, 9) < 0.22;

    if (settled) {
        return write(out, pose.cx, pose.cy, 0, 0, 0, pose.rz, pose.length, 0.7);
    }

    const spread = 1.15;
    const px = pose.cx + hashSigned(i, 1) * spread;
    const py = pose.cy + hashSigned(i, 2) * spread;
    const pz = hashSigned(i, 3) * 0.85;
    const stub = pose.length * (0.15 + hash(i, 4) * 0.2);

    return write(
        out,
        px, py, pz,
        hashSigned(i, 5) * 0.7,
        hashSigned(i, 6) * 0.7,
        pose.rz + hashSigned(i, 7) * 0.7,
        stub,
        0.55
    );
}

/** FORM — fully connected. A clean drawn wireframe, thin and precise. */
function wireframe(i, n, out) {
    const pose = gridPose(i, n);
    return write(out, pose.cx, pose.cy, 0, 0, 0, pose.rz, pose.length, 0.68);
}

/** SYSTEM — the lattice organises into a rack: rows step back in depth,
 *  bars thicken from drawn lines into built modules. */
function rack(i, n, out) {
    const pose = gridPose(i, n);
    const rowT = pose.N > 1 ? pose.row / (pose.N - 1) : 0.5;

    const px = pose.cx + rowT * 0.5;
    const pz = rowT * 0.9 - 0.35;

    return write(out, px, pose.cy, pz, 0, 0, pose.rz, pose.length, 1.15);
}

/** WORK — an exploded axonometric diagram. Deterministic scatter, stable
 *  across reloads: a bar must not be somewhere else on a second visit. */
function exploded(i, n, out) {
    const pose = gridPose(i, n);

    const px = pose.cx + hashSigned(i, 11) * 1.35;
    const py = pose.cy + hashSigned(i, 12) * 1.1;
    const pz = hashSigned(i, 13) * 1.6;

    return write(
        out,
        px, py, pz,
        hashSigned(i, 14) * 0.5,
        hashSigned(i, 15) * 0.5,
        pose.rz + hashSigned(i, 16) * 0.45,
        pose.length * (0.85 + hash(i, 17) * 0.3),
        0.85
    );
}

/** STUDIO / PROCESS — an elevation. Every row steps along one shared
 *  diagonal; order arrived at, not imposed. */
function elevation(i, n, out) {
    const pose = gridPose(i, n);
    const rowT = pose.N > 1 ? pose.row / (pose.N - 1) : 0.5;
    const colT = pose.N > 1 ? pose.col / (pose.N - 1) : 0.5;
    const step = (rowT - 0.5) * 1.1;

    return write(out, pose.cx + step, pose.cy, (colT - 0.5) * 0.5, 0, 0, pose.rz, pose.length, 0.9);
}

/** CLOSE — complete. The full structure, fully present, nothing left
 *  scattered or exploded: the idea has become the system. */
function resolved(i, n, out) {
    const pose = gridPose(i, n);
    return write(out, pose.cx, pose.cy, 0, 0, 0, pose.rz, pose.length, 1.05);
}

/* ── Service postures ────────────────────────────────────────────────────
   Six named variations on the connected grid, one per capability — the
   same object holding a different posture rather than a different object,
   so the studio's "one system, many uses" argument is made by the object
   itself. Applied only while the ambient state is `rack` (services), and
   expires automatically the moment the reader scrolls elsewhere. */

function modular(i, n, out) {
    const pose = gridPose(i, n);
    const unit = Math.floor(pose.col / 2) % 2 === 0;
    return write(out, pose.cx, pose.cy, unit ? 0.12 : -0.12, 0, 0, pose.rz, pose.length, unit ? 1.2 : 0.7);
}

function layered(i, n, out) {
    const pose = gridPose(i, n);
    if (!pose.horizontal) {
        return write(out, pose.cx, pose.cy, 0, 0, 0, pose.rz, pose.length * 0.2, 0.25);
    }
    return write(out, pose.cx, pose.cy, 0, 0, 0, pose.rz, pose.length, 1.1);
}

function compressed(i, n, out) {
    const pose = gridPose(i, n);
    return write(out, pose.cx * 0.5, pose.cy * 0.5, 0, 0, 0, pose.rz, pose.length * 0.55, 0.8);
}

function interfaceForm(i, n, out) {
    const pose = gridPose(i, n);
    const offset = pose.row % 2 === 0 ? 0.22 : -0.22;
    return write(out, pose.cx + (pose.horizontal ? offset : 0), pose.cy, 0, 0, 0, pose.rz, pose.length, 0.95);
}

function networked(i, n, out) {
    const pose = gridPose(i, n);
    const wave = Math.cos((pose.col / Math.max(pose.N - 1, 1)) * Math.PI * 2) * 0.55;
    return write(out, pose.cx, pose.cy, wave, 0, wave * 0.3, pose.rz, pose.length, 0.75);
}

function structural(i, n, out) {
    const pose = gridPose(i, n);
    const swap = (pose.col + pose.row) % 2 === 0;
    const rz = swap ? pose.rz : pose.rz + Math.PI / 2;
    return write(out, pose.cx, pose.cy, swap ? 0.15 : -0.15, 0, 0, rz, pose.length * 0.92, 1.2);
}

/* ── Registry ────────────────────────────────────────────────────────────
   Each state carries its own camera. The lattice spans roughly the same
   footprint in every state (nothing here ever needs a wildly different
   frame), so the cameras vary mainly in angle and distance rather than
   reach — the axonometric tilt of `rack` and the flown-apart `exploded`
   are the two that need to sit further back. */

export const STATES = {
    monolith: { slab: fragments, camera: { dist: 6.4, height: 0.15, tilt: 0.06, roll: 0, x: 0.5, targetX: 0 }, light: 0.15 },
    breathe: { slab: wireframe, camera: { dist: 6.6, height: 0.1, tilt: 0.05, roll: 0, x: -0.6, targetX: 0.1 }, light: 0.3 },
    fan: { slab: rack, camera: { dist: 7.8, height: 0.32, tilt: 0.22, roll: 0.02, x: 1.1, targetX: -0.3 }, light: 0.6 },
    disperse: { slab: exploded, camera: { dist: 9.2, height: 0.35, tilt: 0.18, roll: -0.03, x: -0.9, targetX: 0.25 }, light: 0.85 },
    stair: { slab: elevation, camera: { dist: 7.4, height: 0.1, tilt: 0.08, roll: 0.03, x: 0.65, targetX: -0.1 }, light: 0.4 },
    resolve: { slab: resolved, camera: { dist: 6.2, height: 0.02, tilt: 0.04, roll: 0, x: 0, targetX: 0 }, light: 1 },

    modular: { slab: modular, camera: { dist: 7.4, height: 0.2, tilt: 0.16, roll: 0, x: 1.0, targetX: -0.15 }, light: 0.6 },
    layered: { slab: layered, camera: { dist: 8.2, height: 0.22, tilt: 0.24, roll: 0, x: 0.75, targetX: 0 }, light: 0.68 },
    compressed: { slab: compressed, camera: { dist: 5.2, height: 0.08, tilt: 0.1, roll: 0, x: 0.55, targetX: 0 }, light: 0.5 },
    interface: { slab: interfaceForm, camera: { dist: 7.6, height: 0.18, tilt: 0.14, roll: 0, x: 0.9, targetX: 0 }, light: 0.65 },
    networked: { slab: networked, camera: { dist: 7.0, height: 0.28, tilt: 0.26, roll: 0, x: 0.6, targetX: 0 }, light: 0.72 },
    structural: { slab: structural, camera: { dist: 7.6, height: 0.14, tilt: 0.2, roll: 0, x: 0.8, targetX: 0 }, light: 0.55 },
};

export const DEFAULT_STATE = 'monolith';

const bufferA = new Float32Array(STRIDE);
const bufferB = new Float32Array(STRIDE);

/**
 * Blends two named states for one bar, writing the result into `out`.
 * Because states are pure functions, transitioning between any two of
 * them is a straight interpolation — nothing to build, nothing to kill
 * when the reader scrolls back, no possibility of the object being
 * stranded mid-transition.
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

/** Which third of the grid a bar sits in, 0/1/2 — geometry.js uses this to
 *  tint bars by pillar: Digital Products / Business Systems / Experience. */
export function zoneOf(i, n) {
    const { N, col } = identify(i, n);
    const t = N > 1 ? col / (N - 1) : 0.5;
    return clamp(Math.floor(t * 3), 0, 2);
}
