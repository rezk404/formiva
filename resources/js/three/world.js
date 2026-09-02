import {
    ACESFilmicToneMapping,
    DirectionalLight,
    FogExp2,
    HemisphereLight,
    PerspectiveCamera,
    PMREMGenerator,
    Scene,
    SRGBColorSpace,
    WebGLRenderer,
} from 'three';

import { createStrata } from './strata.js';
import { blendCamera, DEFAULT_STATE } from './states.js';
import { clamp, damp } from '../lib/math.js';
import { env } from '../lib/env.js';

/**
 * THE WORLD
 *
 * One renderer, one scene, one object, for the whole document. A canvas per
 * chapter would mean six WebGL contexts fighting over the GPU and six
 * unrelated objects fighting over the art direction; this is both cheaper
 * and the reason the site reads as a single place.
 *
 * Everything that can fail here is expected to. If the context is refused,
 * lost, or the constructor throws, the caller is told and the CSS fallback
 * takes over — a 3D failure must never be able to take the page with it.
 */
export function createWorld(canvas, options = {}) {
    const { onReady, onFail } = options;

    if (!canvas || !env.webgl) {
        if (onFail) onFail('unsupported');
        return null;
    }

    const quality = env.lowPower ? 'low' : env.dpr > 1.5 ? 'high' : 'medium';

    let renderer;

    try {
        renderer = new WebGLRenderer({
            canvas,
            antialias: quality !== 'low',
            alpha: false,
            powerPreference: 'high-performance',
            stencil: false,
            depth: true,
        });
    } catch (error) {
        if (onFail) onFail('context', error);
        return null;
    }

    renderer.setPixelRatio(env.dpr);
    renderer.setSize(window.innerWidth, window.innerHeight, false);
    renderer.outputColorSpace = SRGBColorSpace;
    renderer.toneMapping = ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.02;
    renderer.setClearColor(0x0b0b0c, 1);

    const scene = new Scene();
    // Fog matched to the clear colour. It is what puts air between the near
    // and far slabs when the stack disperses.
    scene.fog = new FogExp2(0x0b0b0c, 0.058);

    const camera = new PerspectiveCamera(38, window.innerWidth / window.innerHeight, 0.1, 60);
    camera.position.set(0, 0, 6);

    /* ── Light ───────────────────────────────────────────────────────────────
       One warm key from high right, one cool rim from low left, and a very
       dim hemisphere to keep the undersides from going to pure black. Three
       lights, cinematic intent, no bloom anywhere. */

    const key = new DirectionalLight(0xfff2e4, 2.6);
    key.position.set(4.2, 6.5, 4.8);
    scene.add(key);

    const rim = new DirectionalLight(0x8fb0cc, 1.15);
    rim.position.set(-5.5, 1.4, -4.2);
    scene.add(rim);

    const ambient = new HemisphereLight(0x6f7a88, 0x0a0a0c, 0.42);
    scene.add(ambient);

    /* ── Environment ─────────────────────────────────────────────────────────
       A metallic surface with no environment to reflect renders as a flat
       silhouette. RoomEnvironment is an addon and a large import, so it is
       loaded after first paint and applied when it lands — the object simply
       gains reflection a moment later rather than blocking on it. */

    let pmrem = null;

    async function loadEnvironment() {
        if (quality === 'low') return;

        try {
            const { RoomEnvironment } = await import('three/examples/jsm/environments/RoomEnvironment.js');

            pmrem = new PMREMGenerator(renderer);
            pmrem.compileEquirectangularShader();

            const room = new RoomEnvironment();
            const target = pmrem.fromScene(room, 0.04);

            scene.environment = target.texture;

            room.dispose?.();
            requestRender();
        } catch {
            // No environment map. The three lights already carry the form;
            // it is simply a less reflective object.
        }
    }

    /* ── The object ──────────────────────────────────────────────────────── */

    const strata = createStrata({ quality });
    scene.add(strata.mesh);

    /* ── Driving state ───────────────────────────────────────────────────── */

    let fromState = DEFAULT_STATE;
    let toState = DEFAULT_STATE;
    let blend = 0;

    const pointer = { x: 0, y: 0 };
    const pointerEased = { x: 0, y: 0 };
    const cam = { dist: 6, height: 0, tilt: 0.05, roll: 0, x: 0, targetX: 0, light: 0 };

    let active = true;
    let running = false;
    let disposed = false;
    let raf = null;
    let last = performance.now();

    // Reduced motion gets the object, not the animation: one composed frame
    // per state change, no loop, no idle drift.
    const still = !env.animate;

    function requestRender() {
        if (still && !raf && !disposed) {
            raf = requestAnimationFrame(renderOnce);
        }
    }

    function renderOnce() {
        raf = null;
        strata.setTarget(fromState, toState, blend);
        strata.update(1, 1000, 0);
        applyCamera(1, 1);
        renderer.render(scene, camera);
    }

    function applyCamera(dt, snap = 0) {
        const wanted = blendCamera(fromState, toState, blend);
        const rate = snap ? 1 : 1 - Math.exp(-2.4 * dt);

        cam.dist += (wanted.dist - cam.dist) * rate;
        cam.height += (wanted.height - cam.height) * rate;
        cam.tilt += (wanted.tilt - cam.tilt) * rate;
        cam.roll += (wanted.roll - cam.roll) * rate;
        cam.x += (wanted.x - cam.x) * rate;
        cam.targetX += (wanted.targetX - cam.targetX) * rate;
        cam.light += (wanted.light - cam.light) * rate;

        // Pointer moves the camera, not the object. Orbiting the viewer
        // around a still form feels like looking; spinning the form feels
        // like a product configurator.
        const px = pointerEased.x * 0.55;
        const py = pointerEased.y * 0.34;

        camera.position.set(cam.x + px, cam.height + py + Math.sin(cam.tilt) * 0.6, cam.dist);
        camera.lookAt(cam.targetX, cam.height * 0.4, 0);
        camera.rotation.z = cam.roll + pointerEased.x * 0.012;

        key.intensity = 2.25 + cam.light * 1.1;
        rim.intensity = 0.7 + (1 - cam.light) * 0.75;
        ambient.intensity = 0.32 + cam.light * 0.2;
        renderer.toneMappingExposure = 0.94 + cam.light * 0.16;
    }

    function frame(now) {
        raf = null;
        if (disposed) return;

        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;

        pointerEased.x = damp(pointerEased.x, pointer.x, 3.4, dt);
        pointerEased.y = damp(pointerEased.y, pointer.y, 3.4, dt);

        strata.setTarget(fromState, toState, blend);
        strata.update(dt, 3.6, 1);
        applyCamera(dt);

        renderer.render(scene, camera);

        if (running) raf = requestAnimationFrame(frame);
    }

    function start() {
        if (still || running || disposed) return;
        running = true;
        last = performance.now();
        raf = requestAnimationFrame(frame);
    }

    function stop() {
        running = false;
        if (raf) {
            cancelAnimationFrame(raf);
            raf = null;
        }
    }

    /* ── Public surface ──────────────────────────────────────────────────── */

    function blendTo(from, to, t) {
        fromState = from;
        toState = to;
        blend = clamp(t, 0, 1);
        if (still) requestRender();
    }

    function setState(name) {
        blendTo(name, name, 1);
    }

    /**
     * Rendering pauses whenever the canvas is fully covered by an opaque
     * chapter. Roughly two thirds of the page occludes it, so this is the
     * single largest performance decision in the project.
     */
    function setActive(value) {
        if (active === value) return;
        active = value;

        if (active && !document.hidden) {
            start();
        } else {
            stop();
        }
    }

    function setPointer(x, y) {
        pointer.x = clamp(x, -1, 1);
        pointer.y = clamp(y, -1, 1);
    }

    function resize() {
        const width = window.innerWidth;
        const height = window.innerHeight;

        camera.aspect = width / height;
        camera.updateProjectionMatrix();

        renderer.setPixelRatio(env.dpr);
        renderer.setSize(width, height, false);

        requestRender();
        if (still) renderOnce();
    }

    function dispose() {
        disposed = true;
        stop();

        strata.dispose();
        pmrem?.dispose();
        scene.environment = null;
        scene.clear();
        renderer.dispose();
        renderer.forceContextLoss?.();
    }

    /* ── Failure handling ────────────────────────────────────────────────── */

    canvas.addEventListener('webglcontextlost', (event) => {
        // Preventing the default is what allows a restore to be attempted at
        // all, but the honest move is to hand over to the fallback now.
        event.preventDefault();
        stop();
        if (onFail) onFail('lost');
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else if (active) {
            start();
        }
    });

    // First frame before anything else is scheduled, so the world is already
    // composed when the veil lifts rather than assembling in front of the
    // reader.
    try {
        strata.setTarget(DEFAULT_STATE, DEFAULT_STATE, 0);
        strata.update(1, 1000, 0);
        applyCamera(1, 1);
        renderer.render(scene, camera);
    } catch (error) {
        dispose();
        if (onFail) onFail('render', error);
        return null;
    }

    if (still) {
        renderOnce();
    } else {
        start();
    }

    loadEnvironment();

    if (onReady) onReady();

    return {
        blendTo,
        setState,
        setActive,
        setPointer,
        resize,
        dispose,
        get quality() {
            return quality;
        },
    };
}
