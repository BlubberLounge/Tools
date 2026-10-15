/**
 * three.js renderer of the farm viewer. Loaded on demand (dynamic import from
 * farmViewer.js), so three.js ends up in its own chunk.
 *
 * Modes: 'orbit' (OrbitControls: drag rotates, scroll zooms, right-drag pans,
 * touch: one finger rotates, two fingers zoom and pan) and 'fly' (WASD,
 * Space/Shift, mouse look with pointer lock, scroll changes the speed).
 * Rendering is on demand; only fly mode keeps the loop busy while moving.
 */
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

import { createMeshContext, chunkList, meshChunk, LAYERS } from './mesher.js';

const FLY_KEYS = ['KeyW', 'KeyA', 'KeyS', 'KeyD', 'Space', 'ShiftLeft', 'ShiftRight'];

function srgbToLinear(c) {
    return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
}

function isTyping(event) {
    const t = event.target;
    return t && (t.tagName === 'INPUT' || t.tagName === 'SELECT' || t.tagName === 'TEXTAREA' || t.isContentEditable);
}

/**
 * @param {HTMLElement} container  element the canvas is put into
 * @param {object} options  {table, atlas: {meta, image} | null, background, onSpeed, onModeChange, onPointerLock}
 */
export function createViewer(container, options) {
    const { table, atlas } = options;

    const renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.domElement.classList.add('mc-viewer-canvas');
    renderer.domElement.tabIndex = 0;
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(options.background ?? '#9ec5f8');

    const camera = new THREE.PerspectiveCamera(70, 1, 0.05, 4000);
    camera.rotation.order = 'YXZ';

    const orbit = new OrbitControls(camera, renderer.domElement);
    orbit.enableDamping = false;
    orbit.screenSpacePanning = true;
    orbit.zoomSpeed = 1.2;

    // materials
    let texture = null;
    if (atlas) {
        texture = new THREE.Texture(atlas.image);
        texture.magFilter = THREE.NearestFilter;
        texture.minFilter = THREE.NearestFilter;
        texture.generateMipmaps = false;
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.needsUpdate = true;
    }
    const materials = {
        solid: new THREE.MeshBasicMaterial({ map: texture, vertexColors: true }),
        // plants already get a back face from the mesher, so front faces are enough
        cutout: new THREE.MeshBasicMaterial({ map: texture, vertexColors: true, alphaTest: 0.5 }),
        translucent: new THREE.MeshBasicMaterial({ map: texture, vertexColors: true, transparent: true, depthWrite: false, side: THREE.DoubleSide }),
    };

    let modelGroup = null;
    let model = null;
    let center = new THREE.Vector3();
    let radius = 10;
    let mode = 'orbit';
    let dirty = true;
    let disposed = false;
    let buildToken = 0;

    // fly state
    const keys = new Set();
    let speed = 8; // blocks per second
    let yaw = 0, pitch = 0;
    let lastTime = performance.now();

    const requestRender = () => { dirty = true; };
    orbit.addEventListener('change', requestRender);

    function resize() {
        const w = container.clientWidth || 1;
        const h = container.clientHeight || 1;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        requestRender();
    }
    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);

    function geometryFrom(buf) {
        const g = new THREE.BufferGeometry();
        g.setAttribute('position', new THREE.Float32BufferAttribute(buf.positions, 3));
        g.setAttribute('uv', new THREE.Float32BufferAttribute(buf.uvs, 2));
        const colors = new Float32Array(buf.colors.length);
        for (let i = 0; i < colors.length; i++)
            colors[i] = (i % 4 === 3) ? buf.colors[i] : srgbToLinear(buf.colors[i]);
        g.setAttribute('color', new THREE.BufferAttribute(colors, 4));
        g.setIndex(buf.positions.length / 3 > 65535
            ? new THREE.Uint32BufferAttribute(buf.indices, 1)
            : new THREE.Uint16BufferAttribute(buf.indices, 1));
        g.computeBoundingSphere();
        return g;
    }

    function clearModel() {
        if (!modelGroup)
            return;
        modelGroup.traverse(o => {
            if (o.geometry)
                o.geometry.dispose();
        });
        scene.remove(modelGroup);
        modelGroup = null;
    }

    /**
     * Show a model (loadModel() result). Chunks are meshed a few at a time so
     * the page stays responsive; onProgress(done, total) reports the progress.
     */
    async function setModel(newModel, onProgress = () => {}) {
        const token = ++buildToken;
        clearModel();
        model = newModel;

        const group = new THREE.Group();
        modelGroup = group;
        scene.add(group);

        const [sx, sy, sz] = model.size;
        center = new THREE.Vector3(sx / 2, sy / 2, sz / 2);
        radius = Math.max(sx, sy, sz);

        // bounding box outline
        const outline = new THREE.LineSegments(
            new THREE.EdgesGeometry(new THREE.BoxGeometry(sx, sy, sz)),
            new THREE.LineBasicMaterial({ color: 0x000000, transparent: true, opacity: 0.25 })
        );
        outline.position.copy(center);
        group.add(outline);

        resetView();

        const ctx = createMeshContext(model, table, atlas ? atlas.meta : null);
        const chunks = chunkList(model.size);
        let faces = 0;
        let lastYield = performance.now();
        for (let i = 0; i < chunks.length; i++) {
            if (token !== buildToken || disposed)
                return null;
            const result = meshChunk(ctx, ...chunks[i]);
            for (const layer of LAYERS) {
                const buf = result[layer];
                if (buf.faces === 0)
                    continue;
                faces += buf.faces;
                const mesh = new THREE.Mesh(geometryFrom(buf), materials[layer]);
                mesh.renderOrder = layer === 'translucent' ? 2 : layer === 'cutout' ? 1 : 0;
                group.add(mesh);
            }
            if (performance.now() - lastYield > 30) {
                onProgress(i + 1, chunks.length);
                requestRender();
                await new Promise(r => setTimeout(r, 0));
                lastYield = performance.now();
            }
        }
        onProgress(chunks.length, chunks.length);
        requestRender();
        return { faces, chunks: chunks.length };
    }

    function lookFromAngles() {
        camera.rotation.set(pitch, yaw, 0, 'YXZ');
    }

    function anglesFromCamera() {
        const dir = new THREE.Vector3();
        camera.getWorldDirection(dir);
        yaw = Math.atan2(-dir.x, -dir.z);
        pitch = Math.asin(Math.max(-1, Math.min(1, dir.y)));
    }

    /** Diagonal view on the whole model. */
    function resetView() {
        setMode('orbit');
        const d = radius * 1.4 + 4;
        camera.position.set(center.x + d * 0.7, center.y + d * 0.6, center.z + d * 0.9);
        orbit.target.copy(center);
        orbit.update();
        requestRender();
    }

    /** Camera straight above the model, looking down (north at the top). */
    function topDown() {
        setMode('orbit');
        const [sx, , sz] = model ? model.size : [16, 16, 16];
        const fovFactor = 1 / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        const h = Math.max(sx / camera.aspect, sz) / 2 * fovFactor + (model ? model.size[1] : 0) / 2 + 2;
        camera.position.set(center.x, center.y + h, center.z + 0.001);
        orbit.target.copy(center);
        orbit.update();
        requestRender();
    }

    function setMode(newMode) {
        if (newMode === mode)
            return;
        mode = newMode;
        if (mode === 'fly') {
            orbit.enabled = false;
            anglesFromCamera();
            renderer.domElement.focus();
            lockPointer();
        } else {
            keys.clear();
            if (document.pointerLockElement === renderer.domElement)
                document.exitPointerLock();
            // keep looking where the camera points
            const dir = new THREE.Vector3();
            camera.getWorldDirection(dir);
            const dist = Math.max(4, camera.position.distanceTo(center));
            orbit.target.copy(camera.position).addScaledVector(dir, dist);
            orbit.enabled = true;
            orbit.update();
        }
        options.onModeChange?.(mode);
        requestRender();
    }

    function lockPointer() {
        const el = renderer.domElement;
        if (document.pointerLockElement !== el && el.requestPointerLock) {
            try {
                const p = el.requestPointerLock();
                if (p && typeof p.catch === 'function')
                    p.catch(() => {});
            } catch {
                // pointer lock needs a user gesture; a click on the canvas retries
            }
        }
    }

    // input: fly mode
    function onKeyDown(e) {
        if (mode !== 'fly' || isTyping(e) || !FLY_KEYS.includes(e.code))
            return;
        keys.add(e.code);
        e.preventDefault();
    }
    function onKeyUp(e) {
        keys.delete(e.code);
    }
    function onMouseMove(e) {
        if (mode !== 'fly' || document.pointerLockElement !== renderer.domElement)
            return;
        yaw -= e.movementX * 0.0025;
        pitch -= e.movementY * 0.0025;
        pitch = Math.max(-Math.PI / 2 + 0.01, Math.min(Math.PI / 2 - 0.01, pitch));
        lookFromAngles();
        requestRender();
    }
    function onWheel(e) {
        if (mode !== 'fly')
            return;
        e.preventDefault();
        speed = Math.max(1, Math.min(128, speed * (e.deltaY > 0 ? 1 / 1.25 : 1.25)));
        options.onSpeed?.(Math.round(speed));
    }
    function onCanvasClick() {
        if (mode === 'fly')
            lockPointer();
    }
    function onPointerLockChange() {
        options.onPointerLock?.(document.pointerLockElement === renderer.domElement);
    }
    function onBlur() {
        keys.clear();
    }

    window.addEventListener('keydown', onKeyDown);
    window.addEventListener('keyup', onKeyUp);
    window.addEventListener('blur', onBlur);
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('pointerlockchange', onPointerLockChange);
    renderer.domElement.addEventListener('wheel', onWheel, { passive: false });
    renderer.domElement.addEventListener('click', onCanvasClick);
    renderer.domElement.addEventListener('contextmenu', e => e.preventDefault());

    const forward = new THREE.Vector3();
    const right = new THREE.Vector3();

    function tick(now) {
        if (disposed)
            return;
        const dt = Math.min(0.1, (now - lastTime) / 1000);
        lastTime = now;

        if (mode === 'fly' && keys.size) {
            forward.set(-Math.sin(yaw), 0, -Math.cos(yaw));
            right.set(Math.cos(yaw), 0, -Math.sin(yaw));
            const step = speed * dt;
            if (keys.has('KeyW')) camera.position.addScaledVector(forward, step);
            if (keys.has('KeyS')) camera.position.addScaledVector(forward, -step);
            if (keys.has('KeyD')) camera.position.addScaledVector(right, step);
            if (keys.has('KeyA')) camera.position.addScaledVector(right, -step);
            if (keys.has('Space')) camera.position.y += step;
            if (keys.has('ShiftLeft') || keys.has('ShiftRight')) camera.position.y -= step;
            dirty = true;
        }

        if (dirty) {
            dirty = false;
            renderer.render(scene, camera);
        }
        requestAnimationFrame(tick);
    }

    resize();
    requestAnimationFrame(tick);

    function dispose() {
        disposed = true;
        clearModel();
        resizeObserver.disconnect();
        orbit.dispose();
        window.removeEventListener('keydown', onKeyDown);
        window.removeEventListener('keyup', onKeyUp);
        window.removeEventListener('blur', onBlur);
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('pointerlockchange', onPointerLockChange);
        Object.values(materials).forEach(m => m.dispose());
        texture?.dispose();
        renderer.dispose();
        renderer.domElement.remove();
    }

    return {
        setModel,
        setMode,
        getMode: () => mode,
        topDown,
        resetView,
        lockPointer,
        getSpeed: () => Math.round(speed),
        setBackground: color => { scene.background = new THREE.Color(color); requestRender(); },
        dispose,
    };
}
