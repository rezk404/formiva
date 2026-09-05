import {
    BoxGeometry,
    Color,
    DynamicDrawUsage,
    Euler,
    InstancedMesh,
    Matrix4,
    MeshStandardMaterial,
    Quaternion,
    Vector3,
} from 'three';

import {
    beamCount,
    blendInto,
    gridSize,
    BEAM_THICKNESS,
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
    zoneOf,
} from './states.js';
import { damp } from '../lib/math.js';

/**
 * THE FRAME — geometry
 *
 * A single bar, instanced. The base shape is a plain unit box — no custom
 * extrusion, no bevel pass — because a structural bar reads as precise
 * from proportion and material, not from a chamfer. Every bar is scaled
 * to its own length along local X and reoriented by the state function
 * that owns the current frame; the geometry itself never changes.
 *
 * Colour is assigned once, by which third of the grid a bar's column
 * falls in, and never touched again — it is how the object carries the
 * three-pillar structure of the studio's own services into the render,
 * without a shader or a second material.
 */

function gridResolution(quality) {
    if (quality === 'low') return 5;
    if (quality === 'medium') return 6;
    return 7;
}

export function createStrata({ quality = 'high', zoneColors = [0xc9c4b6, 0x5c87ac, 0xcb4b24] } = {}) {
    const N = gridResolution(quality);
    const count = beamCount(N);

    const geometry = new BoxGeometry(1, BEAM_THICKNESS, BEAM_THICKNESS);

    const material = new MeshStandardMaterial({
        color: 0xffffff,
        // Matte and precise rather than jewelled: a machined extrusion, not
        // polished chrome. The three-light rig this sits under is unchanged.
        metalness: 0.68,
        roughness: 0.36,
        envMapIntensity: 0.7,
    });

    const mesh = new InstancedMesh(geometry, material, count);
    mesh.instanceMatrix.setUsage(DynamicDrawUsage);
    mesh.frustumCulled = false;

    const tone = new Color();

    for (let i = 0; i < count; i += 1) {
        const zone = zoneOf(i, count);
        tone.setHex(zoneColors[zone] ?? zoneColors[0]);
        mesh.setColorAt(i, tone);
    }

    if (mesh.instanceColor) mesh.instanceColor.needsUpdate = true;

    /* ── Per-bar transform state ──────────────────────────────────────────
       Two flat arrays: where each bar is, and where it is being asked to
       be. Damping between them every frame is what gives the object
       weight — it arrives at a new state rather than cutting to it. */

    const current = new Float32Array(count * STRIDE);
    const target = new Float32Array(count * STRIDE);
    const scratch = new Float32Array(STRIDE);

    const matrix = new Matrix4();
    const position = new Vector3();
    const quaternion = new Quaternion();
    const scale = new Vector3();
    const euler = new Euler(0, 0, 0, 'XYZ');

    // Seed both arrays from the opening state so the first frame is already
    // composed — no snap from origin on load.
    for (let i = 0; i < count; i += 1) {
        blendInto(scratch, 'monolith', 'monolith', 0, i, count);
        current.set(scratch, i * STRIDE);
        target.set(scratch, i * STRIDE);
    }

    function setTarget(fromState, toState, t) {
        for (let i = 0; i < count; i += 1) {
            blendInto(scratch, fromState, toState, t, i, count);
            target.set(scratch, i * STRIDE);
        }
    }

    /**
     * @param dt     seconds since last frame
     * @param lambda convergence rate; higher is tighter
     * @returns the largest remaining distance to the target, so the caller
     *          can stop rendering once the object has actually settled
     *          rather than redrawing an identical frame forever.
     */
    function update(dt, lambda) {
        let residual = 0;

        for (let i = 0; i < count; i += 1) {
            const o = i * STRIDE;

            for (let k = 0; k < STRIDE; k += 1) {
                current[o + k] = damp(current[o + k], target[o + k], lambda, dt);

                const gap = Math.abs(target[o + k] - current[o + k]);
                if (gap > residual) residual = gap;
            }

            position.set(current[o + PX], current[o + PY], current[o + PZ]);
            euler.set(current[o + RX], current[o + RY], current[o + RZ]);
            quaternion.setFromEuler(euler);
            scale.set(current[o + SX], current[o + SY], current[o + SZ]);

            matrix.compose(position, quaternion, scale);
            mesh.setMatrixAt(i, matrix);
        }

        mesh.instanceMatrix.needsUpdate = true;

        return residual;
    }

    function dispose() {
        geometry.dispose();
        material.dispose();
        mesh.dispose();
    }

    return { mesh, count, gridResolution: N, setTarget, update, dispose };
}
