const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer();
const container = document.querySelector('.dashboardDeviderLeft');

// Resize renderer to fit the container size
const containerWidth = container.offsetWidth;
const containerHeight = container.offsetHeight;
renderer.setSize(containerWidth, containerHeight);
container.appendChild(renderer.domElement);

// Load a 3D model (e.g., .glb or .gltf)
const loader = new THREE.GLTFLoader();
loader.load('../assets/models/rivanMainBuilding.glb', (gltf) => {
    scene.add(gltf.scene);
});

camera.position.z = 5;

// Animation loop
function animate() {
    requestAnimationFrame(animate);

    // Rotate the model for better visualization
    scene.rotation.x += 0.01;
    scene.rotation.y += 0.01;

    renderer.render(scene, camera);
}

animate();

// Adjust the renderer size when the window is resized
window.addEventListener('resize', () => {
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    renderer.setSize(containerWidth, containerHeight);
    camera.aspect = containerWidth / containerHeight;
    camera.updateProjectionMatrix();
});
