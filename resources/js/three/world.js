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
 * One renderer, one scene, one object, shared by every chapter that wants
 * it — a canvas per chapter would mean unrelated 3D objects fighting for
 * attention. This build is intentionally selective about where the canvas
 * shows at all: most chapters now sit on an opaque paper ground, and the
 * frame only shows through the dark anchors (hero, services, close) where
 * it is doing real narrative work rather than filling space.
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
    renderer.toneMappingExposure = 1.0;
    renderer.setClearColor(0x0d0d0c, 1);

    const scene = new Scene();
    scene.fog = new FogExp2(0x0d0d0c, 0.055);

    const camera = new PerspectiveCamera(36, window.innerWidth / window.innerHeight, 0.1, 60);
    camera.position.set(0, 0, 6);

    /* ── Light ───────────────────────────────────────────────────────────────
       One warm key from high right, one cool rim from low left, a dim
       hemisphere to keep undersides off pure black. Three lights, no bloom,
       no post-processing pass. */

    const key = new DirectionalLight(0xfaf3e6, 2.5);
    key.position.set(4.2, 6.5, 4.8);
    scene.add(key);

    const rim = new DirectionalLight(0x9db9d6, 1.2);
    rim.position.set(-5.5, 1.4, -4.2);
    scene.add(rim);

    const ambient = new HemisphereLight(0x6f7a88, 0x0a0a0c, 0.4);
    scene.add(ambient);

    /* ── Environment ─────────────────────────────────────────────────────────
       Loaded after first paint so it never blocks it; the object simply
       gains reflection a moment later. Skipped entirely on low-power. */

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
            // No environment map — the three lights already carry the form.
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
    // per state change, no loop.
    const still = !env.animate;

    function requestRender() {
        if (still && !raf && !disposed) {
            raf = requestAnimationFrame(renderOnce);
        }
    }

    /**
     * Recomputing every bar's target is the most expensive thing per frame,
     * and during a hold — which is most of the time — it produces exactly
     * the values it produced last frame. Only redo it when the blend has
     * actually moved.
     */
    let lastFrom = null;
    let lastTo = null;
    let lastBlend = -1;

    function syncTargets() {
        if (fromState === lastFrom && toState === lastTo && blend === lastBlend) return;

        lastFrom = fromState;
        lastTo = toState;
        lastBlend = blend;

        strata.setTarget(fromState, toState, blend);
    }

    function renderOnce() {
        raf = null;
        syncTargets();
        strata.update(1, 1000);
        applyCamera(1, 1);
        renderer.render(scene, camera);
    }

    /** @returns the largest remaining easing distance, for the settle check. */
    function applyCamera(dt, snap = 0) {
        const wanted = blendCamera(fromState, toState, blend);
        const rate = snap ? 1 : 1 - Math.exp(-2.4 * dt);

        const residual = Math.max(
            Math.abs(wanted.dist - cam.dist),
            Math.abs(wanted.height - cam.height),
            Math.abs(wanted.tilt - cam.tilt),
            Math.abs(wanted.roll - cam.roll),
            Math.abs(wanted.x - cam.x),
            Math.abs(wanted.targetX - cam.targetX),
            Math.abs(wanted.light - cam.light)
        );

        cam.dist += (wanted.dist - cam.dist) * rate;
        cam.height += (wanted.height - cam.height) * rate;
        cam.tilt += (wanted.tilt - cam.tilt) * rate;
        cam.roll += (wanted.roll - cam.roll) * rate;
        cam.x += (wanted.x - cam.x) * rate;
        cam.targetX += (wanted.targetX - cam.targetX) * rate;
        cam.light += (wanted.light - cam.light) * rate;

        const px = pointerEased.x * 0.5;
        const py = pointerEased.y * 0.32;

        camera.position.set(cam.x + px, cam.height + py + Math.sin(cam.tilt) * 0.6, cam.dist);
        camera.lookAt(cam.targetX, cam.height * 0.4, 0);
        camera.rotation.z = cam.roll + pointerEased.x * 0.01;

        key.intensity = 2.2 + cam.light * 1.0;
        rim.intensity = 0.75 + (1 - cam.light) * 0.7;
        ambient.intensity = 0.3 + cam.light * 0.2;
        renderer.toneMappingExposure = 0.96 + cam.light * 0.14;

        return residual;
    }

    function frame(now) {
        raf = null;
        if (disposed) return;

        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;

        pointerEased.x = damp(pointerEased.x, pointer.x, 3.4, dt);
        pointerEased.y = damp(pointerEased.y, pointer.y, 3.4, dt);

        syncTargets();

        const settling = Math.max(
            strata.update(dt, 3.6),
            applyCamera(dt),
            Math.abs(pointer.x - pointerEased.x),
            Math.abs(pointer.y - pointerEased.y)
        );

        renderer.render(scene, camera);

        if (!running) return;

        // Nothing is idling any more — once the object has arrived, further
        // frames would be pixel-for-pixel identical. Stop, and let any of
        // the wake paths below restart the loop.
        if (settling < 0.0004) {
            running = false;
            return;
        }

        raf = requestAnimationFrame(frame);
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

    /* Wake paths. The loop parks itself once the object settles, so anything
       that changes what should be on screen has to restart it. */

    function blendTo(from, to, t) {
        fromState = from;
        toState = to;
        blend = clamp(t, 0, 1);

        if (still) requestRender();
        else if (active) start();
    }

    function setState(name) {
        blendTo(name, name, 1);
    }

    /**
     * Rendering pauses whenever the canvas is fully occluded by an opaque
     * chapter — which is most of the page now that only a few anchors show
     * the frame at all. This is the single largest performance decision in
     * the project, more so than before: the canvas spends most of a typical
     * scroll paused.
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

        if (active && !still) start();
    }

    function resize() {
        const width = window.innerWidth;
        const height = window.innerHeight;

        camera.aspect = width / height;
        camera.updateProjectionMatrix();

        renderer.setPixelRatio(env.dpr);
        renderer.setSize(width, height, false);

        if (still) renderOnce();
        else if (active) start();
    }

    /* ── Failure handling ────────────────────────────────────────────────── */

    const onContextLost = (event) => {
        event.preventDefault();
        stop();
        if (onFail) onFail('lost');
    };

    const onVisibility = () => {
        if (document.hidden) stop();
        else if (active) start();
    };

    canvas.addEventListener('webglcontextlost', onContextLost);
    document.addEventListener('visibilitychange', onVisibility);

    function dispose() {
        disposed = true;
        stop();

        // Listeners outlive the renderer unless they are taken off by hand;
        // the document one in particular would otherwise keep a reference to
        // this whole closure alive.
        canvas.removeEventListener('webglcontextlost', onContextLost);
        document.removeEventListener('visibilitychange', onVisibility);

        strata.dispose();
        pmrem?.dispose();
        scene.environment = null;
        scene.clear();
        renderer.dispose();
        renderer.forceContextLoss?.();
    }

    // First frame before anything else is scheduled, so the object is
    // already composed when the page is first painted.
    try {
        strata.setTarget(DEFAULT_STATE, DEFAULT_STATE, 0);
        strata.update(1, 1000);
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
