const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);

// Target the container div to insert the 3D canvas
document.querySelector('.dashboardDeviderLeft').appendChild(renderer.domElement);

// Load a 3D model (e.g., .glb or .gltf)
const loader = new THREE.GLTFLoader();
loader.load('..assets/models/rivanMainBuilding.glb', (gltf) => {
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
