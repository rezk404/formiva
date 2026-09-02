/**
 * Capability detection.
 *
 * Read once at boot and shared. Every enhancement on the site asks this
 * module whether it is welcome before it does anything — the site's baseline
 * is a fully readable document, and each layer is opt-in from here.
 */

const query = (q) => (typeof window !== 'undefined' && window.matchMedia ? window.matchMedia(q) : null);

const reducedMotionQuery = query('(prefers-reduced-motion: reduce)');
const finePointerQuery = query('(hover: hover) and (pointer: fine)');
const coarsePointerQuery = query('(pointer: coarse)');

/**
 * A conservative guess at whether the device will struggle with a
 * continuously rendering WebGL canvas. We would rather under-promise on a
 * capable machine than melt a mid-range phone.
 */
function detectLowPower() {
    if (typeof navigator === 'undefined') return true;

    const cores = navigator.hardwareConcurrency || 4;
    const memory = navigator.deviceMemory || 4;
    const coarse = coarsePointerQuery ? coarsePointerQuery.matches : false;

    return cores <= 4 || memory <= 4 || coarse;
}

/**
 * Probing for WebGL costs a throwaway context, so the answer is cached.
 * Returns false for both a missing implementation and a blocked one
 * (headless browsers, GPU denylists, hardened privacy settings).
 */
let webglAnswer = null;

function detectWebGL() {
    if (webglAnswer !== null) return webglAnswer;

    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl2') || canvas.getContext('webgl');
        webglAnswer = Boolean(gl && typeof gl.getParameter === 'function');

        // Release the probe immediately; contexts are a limited resource and
        // the real one is about to ask for its own.
        if (gl) {
            const lose = gl.getExtension('WEBGL_lose_context');
            if (lose) lose.loseContext();
        }
    } catch {
        webglAnswer = false;
    }

    return webglAnswer;
}

export const env = {
    get reducedMotion() {
        return reducedMotionQuery ? reducedMotionQuery.matches : false;
    },

    get finePointer() {
        return finePointerQuery ? finePointerQuery.matches : false;
    },

    get touch() {
        return coarsePointerQuery ? coarsePointerQuery.matches : 'ontouchstart' in window;
    },

    get lowPower() {
        return detectLowPower();
    },

    get webgl() {
        return detectWebGL();
    },

    get dpr() {
        const raw = window.devicePixelRatio || 1;
        return Math.min(raw, detectLowPower() ? 1.25 : 1.75);
    },

    /** Motion is allowed at all. The gate every animation checks first. */
    get animate() {
        return !this.reducedMotion;
    },
};

/**
 * Reduced-motion can be toggled mid-session from the OS. Rather than trying
 * to unwind every timeline in flight, we tell the page and let it reload
 * into the correct mode. It is honest, and it is what the user asked for.
 */
export function watchMotionPreference(onChange) {
    if (!reducedMotionQuery || typeof reducedMotionQuery.addEventListener !== 'function') return;

    reducedMotionQuery.addEventListener('change', (event) => onChange(event.matches));
}
