import {
    Color,
    DynamicDrawUsage,
    Euler,
    ExtrudeGeometry,
    InstancedMesh,
    Matrix4,
    MeshStandardMaterial,
    Quaternion,
    Shape,
    Vector3,
} from 'three';

import {
    blendInto,
    SLAB_THICKNESS,
    STRIDE,
    PX,
    PY,
    PZ,
    RX,
    RY,
    RZ,
    SX,
    SY,
    SZ,
} from './states.js';
import { damp, hash } from '../lib/math.js';

/**
 * THE STRATA — geometry
 *
 * A single slab, instanced. Named imports rather than `import * as THREE`
 * so the bundler can drop the two thirds of the library this file never
 * touches.
 *
 * The slab is an extruded rounded rectangle with a small bevel. The bevel is
 * the reason the object reads as machined metal: a raw box has one hard
 * highlight per edge, a chamfered one has a soft band that travels as the
 * form turns. It is four hundred extra triangles for most of the material
 * quality on the page.
 */

const SLAB_WIDTH = 1.72;
const SLAB_DEPTH = 1.72;
// Thickness lives in states.js: the state functions' vertical extents are
// derived from it, and the two must not be able to drift apart.
const SLAB_HEIGHT = SLAB_THICKNESS;
const CORNER = 0.14;

function roundedRect(width, depth, radius) {
    const shape = new Shape();
    const x = -width / 2;
    const y = -depth / 2;

    shape.moveTo(x + radius, y);
    shape.lineTo(x + width - radius, y);
    shape.quadraticCurveTo(x + width, y, x + width, y + radius);
    shape.lineTo(x + width, y + depth - radius);
    shape.quadraticCurveTo(x + width, y + depth, x + width - radius, y + depth);
    shape.lineTo(x + radius, y + depth);
    shape.quadraticCurveTo(x, y + depth, x, y + depth - radius);
    shape.lineTo(x, y + radius);
    shape.quadraticCurveTo(x, y, x + radius, y);

    return shape;
}

function slabGeometry(detail) {
    const geometry = new ExtrudeGeometry(roundedRect(SLAB_WIDTH, SLAB_DEPTH, CORNER), {
        depth: SLAB_HEIGHT,
        bevelEnabled: true,
        bevelThickness: 0.012,
        bevelSize: 0.012,
        bevelOffset: 0,
        bevelSegments: detail >= 2 ? 2 : 1,
        curveSegments: detail >= 2 ? 6 : 3,
    });

    // Extrusion runs along +Z; the stack runs along +Y.
    geometry.rotateX(-Math.PI / 2);
    geometry.center();
    geometry.computeVertexNormals();

    return geometry;
}

/**
 * Slab count scales with what the device can carry. The silhouette survives
 * the reduction because the states are functions of a normalised index —
 * eighteen slabs describe the same form as thirty-four, at lower resolution.
 */
function slabCount(quality) {
    if (quality === 'low') return 18;
    if (quality === 'medium') return 26;
    return 34;
}

export function createStrata({ quality = 'high', accentColor = 0xe5502a } = {}) {
    const count = slabCount(quality);
    const detail = quality === 'low' ? 1 : 2;

    const geometry = slabGeometry(detail);

    const material = new MeshStandardMaterial({
        color: 0xb9b6b1,
        // High metalness with mid roughness: the surface takes the key light
        // as a broad sheen rather than a specular dot, which is what stops
        // the object looking like plastic.
        metalness: 0.92,
        roughness: 0.38,
        envMapIntensity: 0.85,
        flatShading: false,
    });

    const mesh = new InstancedMesh(geometry, material, count);
    mesh.instanceMatrix.setUsage(DynamicDrawUsage);
    mesh.frustumCulled = false;

    // Per-slab tone. A stack in one flat colour looks printed; small
    // variations in tone make it read as material with a history.
    const accent = new Color(accentColor);
    const base = new Color(0xffffff);
    const tone = new Color();
    const accentIndex = Math.floor(count * 0.36);

    for (let i = 0; i < count; i += 1) {
        if (i === accentIndex) {
            mesh.setColorAt(i, accent);
        } else {
            const shade = 0.72 + hash(i, 11) * 0.34;
            tone.copy(base).multiplyScalar(shade);
            mesh.setColorAt(i, tone);
        }
    }

    if (mesh.instanceColor) mesh.instanceColor.needsUpdate = true;

    /* ── Per-slab transform state ────────────────────────────────────────────
       Two flat arrays: where each slab is, and where it is being asked to
       be. Damping between them every frame is what gives the object weight —
       it arrives at a new state rather than cutting to it. */

    const current = new Float32Array(count * STRIDE);
    const target = new Float32Array(count * STRIDE);
    const scratch = new Float32Array(STRIDE);

    const matrix = new Matrix4();
    const position = new Vector3();
    const quaternion = new Quaternion();
    const scale = new Vector3();
    // A real Euler, not a plain object: Quaternion.setFromEuler reads the
    // class's private fields and would silently produce identity rotations.
    const euler = new Euler(0, 0, 0, 'XYZ');

    // Seed both arrays from the opening state so the first frame is already
    // composed — no snap from origin on load.
    for (let i = 0; i < count; i += 1) {
        blendInto(scratch, 'monolith', 'monolith', 0, i, count);
        current.set(scratch, i * STRIDE);
        target.set(scratch, i * STRIDE);
    }

    let idlePhase = 0;

    function setTarget(fromState, toState, t) {
        for (let i = 0; i < count; i += 1) {
            blendInto(scratch, fromState, toState, t, i, count);
            target.set(scratch, i * STRIDE);
        }
    }

    /**
     * @param dt        seconds since last frame
     * @param lambda    convergence rate; higher is tighter
     * @param idle      amplitude of the resting drift, 0 disables it
     */
    function update(dt, lambda, idle = 1) {
        idlePhase += dt * 0.32;

        for (let i = 0; i < count; i += 1) {
            const o = i * STRIDE;

            for (let k = 0; k < STRIDE; k += 1) {
                current[o + k] = damp(current[o + k], target[o + k], lambda, dt);
            }

            // Idle drift. Each slab breathes on its own offset phase, so the
            // object is never still and never looks like it is spinning.
            const drift = idle
                ? Math.sin(idlePhase + i * 0.42) * 0.012 * idle
                : 0;
            const sway = idle
                ? Math.sin(idlePhase * 0.7 + i * 0.28) * 0.02 * idle
                : 0;

            position.set(current[o + PX], current[o + PY] + drift, current[o + PZ]);

            euler.set(current[o + RX], current[o + RY] + sway, current[o + RZ]);
            quaternion.setFromEuler(euler);

            scale.set(current[o + SX], current[o + SY], current[o + SZ]);

            matrix.compose(position, quaternion, scale);
            mesh.setMatrixAt(i, matrix);
        }

        mesh.instanceMatrix.needsUpdate = true;
    }

    function dispose() {
        geometry.dispose();
        material.dispose();
        mesh.dispose();
    }

    return { mesh, count, setTarget, update, dispose };
}
