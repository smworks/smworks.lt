var visualScene = new THREE.Scene();
var camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
var renderer = new THREE.WebGLRenderer();
var clock = new THREE.Clock();
var physicalScene = new CANNON.World();
var fixedTimeStep = 1.0 / 60.0; // seconds
var maxSubSteps = 3;
var person;
var controls;
var menu = document.getElementById('menu');
var action = document.getElementById('action');
var actionCallback = null;
var sceneObjects = [];
var locations = [];
var ambientSound = document.createElement('audio');

if (!Modernizr.canvas) {
    alert('Your browser doesn\'t support HTML5 canvas :(');
    throw new Error('Your browser doesn\'t support HTML5 canvas');
}

THREE.DefaultLoadingManager.onProgress = function (item, loaded, total) {
    console.log(item, loaded, total);
    if (loaded === total) {
        setupLockPoint();
    }
};

var textureLoader = new THREE.TextureLoader();
function repeat(texture, u, v) {
    texture.wrapS = texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(u, v);
    return texture;
}

var materials = {
    green: new THREE.MeshBasicMaterial({color: 0x00ff00}),
    grey: new THREE.MeshBasicMaterial({color: 0x444444}),
    white: new THREE.MeshPhongMaterial({color: 0xffffff}),
    wall: new THREE.MeshBasicMaterial({map: repeat(textureLoader.load('assets/about/wall.jpg'), 6, 1)}),
    sand: new THREE.MeshBasicMaterial({map: repeat(textureLoader.load('assets/about/sand.jpg'), 200, 200)}),
    garage: new THREE.MeshBasicMaterial({map: textureLoader.load('assets/about/garage.jpg')}),
    logo: new THREE.MeshPhongMaterial({map: textureLoader.load('assets/about/smworks.png')}),
    me: new THREE.MeshPhongMaterial({map: textureLoader.load('assets/about/me.jpg')})
};

var keyPressAction = function (key) {
    if (key === 70 && actionCallback != null) {
        actionCallback();
    }
};

function prepareAction(message, callback) {
    action.innerHTML = message;
    action.style.display = 'block';
    actionCallback = callback;
}

function clearAction() {
    action.style.display = 'none';
    actionCallback = null;
}

function addLocation(x, z, xLength, zLength, entered, quit) {
    locations.push({x: x, z: z, maxX: x + xLength, maxZ: z + zLength, entered: entered, quit: quit, e: false, q: true});
}

function updateLocations(posX, posZ) {
    locations.forEach(function (i) {
        if (posX >= i.x && posZ >= i.z && posX <= i.maxX && posZ <= i.maxZ) {
            if (!i.e) {
                i.entered();
                i.e = true;
                i.q = false;
            }
        } else if (!i.q) {
            i.quit();
            i.e = false;
            i.q = true;
        }
    });
}

function addSceneObject(visual, physical, logical) {
    if (visual) {
        visualScene.add(visual);
    }
    if (physical) {
        physicalScene.add(physical);
    }
    sceneObjects.push({visual: visual, physical: physical, logical: logical});
}

function update(visual, physical) {
    visual.position.x = physical.position.x;
    visual.position.y = physical.position.y;
    visual.position.z = physical.position.z;
    visual.quaternion.x = physical.quaternion.x;
    visual.quaternion.y = physical.quaternion.y;
    visual.quaternion.z = physical.quaternion.z;
    visual.quaternion.w = physical.quaternion.w;
}

function updateSceneObjects(delta) {
    sceneObjects.forEach(function (item) {
        if (item.visual && item.physical && !item.logical) {
            update(item.visual, item.physical);
        }
        if (item.logical) {
            item.logical(delta);
        }
    });
    updateLocations(person.position.x, person.position.z);
}

function addSkyBox() {
    var textureLoader = new THREE.TextureLoader();

    var texture0 = textureLoader.load('assets/about/posx.jpg');
    var texture1 = textureLoader.load('assets/about/negx.jpg');
    var texture2 = textureLoader.load('assets/about/posy.jpg');
    var texture3 = textureLoader.load('assets/about/negy.jpg');
    var texture4 = textureLoader.load('assets/about/posz.jpg');
    var texture5 = textureLoader.load('assets/about/negz.jpg');

    var materials = [
        new THREE.MeshBasicMaterial({map: texture0, side: THREE.BackSide, fog: false}),
        new THREE.MeshBasicMaterial({map: texture1, side: THREE.BackSide, fog: false}),
        new THREE.MeshBasicMaterial({map: texture2, side: THREE.BackSide, fog: false}),
        new THREE.MeshBasicMaterial({map: texture3, side: THREE.BackSide, fog: false}),
        new THREE.MeshBasicMaterial({map: texture4, side: THREE.BackSide, fog: false}),
        new THREE.MeshBasicMaterial({map: texture5, side: THREE.BackSide, fog: false})
    ];
    var faceMaterial = new THREE.MultiMaterial(materials);

    var geometry = new THREE.BoxGeometry(999, 999, 999, 1, 1, 1);
    var boxMesh = new THREE.Mesh(geometry, faceMaterial);
    addSceneObject(boxMesh, null, null);
}

function addSpotLight() {
    var pointLight = new THREE.PointLight(0xffffff, 0.5, 100, 2);
    pointLight.position.x = 0;
    pointLight.position.y = 4;
    pointLight.position.z = 0;
    //visualScene.add(new THREE.PointLightHelper(pointLight, 3));
    //var sphereGeometry = new THREE.SphereGeometry(1, 16, 16);
    //var sphere = new THREE.Mesh(sphereGeometry, materials.wall);
    //var sphereBody = new CANNON.Body({
    //    mass: 5, // kg
    //    position: new CANNON.Vec3(0, 1, 0), // m
    //    shape: new CANNON.Sphere(1)
    //});

    addSceneObject(pointLight, null, null);
    addSceneObject(new THREE.AmbientLight(0xffffff, 0.2), null, null);

    //addSceneObject(sphere, null, function () {
    //    update(sphere, pointLight);
    //});
}

//function addCube() {
//    var boxGeometry = new THREE.BoxGeometry(1, 1, 1);
//    var cube = new THREE.Mesh(boxGeometry, materials.green);
//    var cubeBody = new CANNON.Body({
//        mass: 5, // kg
//        position: new CANNON.Vec3(0.4, 10, 0), // m
//        shape: new CANNON.Box(new CANNON.Vec3(0.5, 0.5, 0.5))
//    });
//
//    addSceneObject(cube, cubeBody, null);
//}

function addBox(material, width, height, depth, x, y, z, updateCallback) {
    var geometry = new THREE.BoxGeometry(width, height, depth);
    var visual = new THREE.Mesh(geometry, material);
    var physical = new CANNON.Body({
        mass: 0,
        position: new CANNON.Vec3(x, y, z),
        shape: new CANNON.Box(new CANNON.Vec3(width * 0.5, height * 0.5, depth * 0.5))
    });

    var callback = null;
    if (updateCallback != null) {
        callback = function (delta) {
            updateCallback(delta, visual, physical);
        }
    }

    addSceneObject(visual, physical, callback);
    return physical;
}

function addModelToScene(geometry, material, x, y, z, sx, sy, sz) {
    var mesh = new THREE.Mesh(geometry, material);
    mesh.position.x = x;
    mesh.position.y = y;
    mesh.position.z = z;
    if (sx) {
        mesh.scale.x = sx;
    }
    if (sy) {
        mesh.scale.y = sy;
    }
    if (sz) {
        mesh.scale.z = sz;
    }
    addSceneObject(mesh, null, null);
}

function loadModel() {
    var loader = new THREE.JSONLoader();
    loader.load('assets/about/portrait.json', function (geometry) {
        addModelToScene(geometry, materials.logo, -11.8, 4, -5, 2, 2, 2);
        addModelToScene(geometry, materials.me, -11.8, 4, 0, 2, 3, 2);
        addModelToScene(geometry, materials.logo, -11.8, 4, 5, 2, 2, 2);
    });
}

function createBuilding() {
    // Exterior
    addBox(materials.wall, 0.2, 8, 40, -12, 4, 0, null); // Left
    addBox(materials.wall, 0.2, 8, 40, 12, 4, 0, null); // Right
    addBox(materials.wall, 24.19, 4, 0.2, 0, 6, 19.91, null); // Front
    addBox(materials.wall, 18, 4, 0.2, -3.09, 2, 19.91, null); // Front bottom
    addBox(materials.wall, 24.19, 8, 0.2, 0, 4, -19.91, null); // Back
    addBox(materials.wall, 24.25, 0.1, 40.05, 0, 0, 0, null); // Ground
    addBox(materials.wall, 24.25, 0.2, 40.05, 0, 7.91, 0, null); // Roof
    // Interior
    addBox(materials.white, 0.1, 7.8, 39.8, -11.94, 3.9, 0, null); // Left
    addBox(materials.white, 0.1, 7.8, 39.8, 11.94, 3.9, 0, null); // Right
    addBox(materials.white, 24, 3.8, 0.1, 0, 5.89, 19.8, null); // Front
    addBox(materials.white, 17.9, 4, 0.1, -3, 2, 19.8, null); // Front bottom
    addBox(materials.white, 24, 7.9, 0.1, 0, 3.9, -19.85, null); // Back
    addBox(materials.white, 24, 0.1, 39.8, 0, 0.01, 0, null); // Ground
    addBox(materials.white, 24, 0.1, 39.8, 0, 7.8, 0, null); // Roof

    addLocation(-12, -19, 24, 39.8, function () {
        ambientSound.volume = 0.3;
    }, function () {
        ambientSound.volume = 1;
    });
}

function playGarageSound(begin, end) {
    var sound = document.createElement('audio');
    sound.setAttribute('preload', 'none');
    sound.setAttribute('src', 'assets/about/garage_door.mp3');
    sound.volume = 0.5;
    sound.play();
    sound.addEventListener('ended', function () {
        begin();
    }, false);
    sound.addEventListener('play', function () {
        end();
    }, false);
}

function addGarageDoor() {
    var openGates = false;
    var closeGates = false;
    var transition = false;
    addBox(materials.garage, 6.5, 4, 0.1, 8.75, 2, 19.9, function (delta, visual, physical) {
        update(visual, physical);
        if (physical.position.y < 5.9 && openGates) {
            physical.position.y += delta * 0.3;
        }
        if (physical.position.y > 2 && closeGates) {
            physical.position.y -= delta * 0.3;
        }
    });
    addLocation(6.2, 18, 6, 3.5, function () {
        prepareAction('Press F to open/close garage gates', function () {
            if (transition) {
                return;
            }
            if (!openGates) {
                transition = true;
                playGarageSound(function () {
                    transition = false;
                }, function () {
                    closeGates = false;
                    openGates = true;
                });
            } else if (!closeGates) {
                transition = true;
                playGarageSound(function () {
                    transition = false;
                }, function () {
                    closeGates = true;
                    openGates = false;
                });
            }
        });

    }, function () {
        clearAction();
    });
}

function addAmbientMusic() {
    ambientSound.setAttribute('preload', 'none');
    ambientSound.setAttribute('src', 'assets/about/desert_howling_wind.mp3');
    ambientSound.addEventListener('ended', function () {
        this.currentTime = 0;
        this.play();
    }, false);
    ambientSound.play();
}

function addGround() {
    var planeGeometry = new THREE.PlaneGeometry(1000, 1000, 1, 1);
    var plane = new THREE.Mesh(planeGeometry, materials.sand);
    plane.material.side = THREE.DoubleSide;

    var groundBody = new CANNON.Body({
        mass: 0 // mass == 0 makes the body static
    });
    var groundShape = new CANNON.Plane();
    groundBody.addShape(groundShape);
    groundBody.quaternion.setFromAxisAngle(new CANNON.Vec3(1, 0, 0), -Math.PI / 2);

    addSceneObject(plane, groundBody, null);
}

function addCamera() {

    person = new CANNON.Body({
        mass: 5, // kg
        position: new CANNON.Vec3(0, 1.5, 30), // m
        shape: new CANNON.Sphere(1),
        linearDamping: 0.95,
        angularDamping: 0.9
    });

    camera.position.x = person.position.x;
    camera.position.y = person.position.y;
    camera.position.z = person.position.z;


    addSceneObject(null, person, function (delta) {
        if (controls.enabled) {
            controls.update(delta);
        }
    });

    controls = new PointerLockControls(camera, person, keyPressAction);
    visualScene.add(controls.getObject());
}

function setupLockPoint() {
    if (!Modernizr.pointerlock || Modernizr.touchevents) {
        return;
    }

    menu.style.display = 'block';

    menu.addEventListener('click', function () {
        document.body.requestPointerLock = document.body.requestPointerLock
            || document.body.mozRequestPointerLock
            || document.body.webkitRequestPointerLock;
        document.body.requestPointerLock();
    }, false);

    var pointerLockChange = function () {
        camera.position.x = 0;
        camera.position.y = 0.5;
        camera.position.z = 0;
        controls.enabled = document.pointerLockElement === document.body
            || document.mozPointerLockElement === document.body
            || document.webkitointerLockElement === document.body;
        menu.style.display = controls.enabled ? 'none' : 'block';
    };

    var pointerLockError = function (event) {
        // We have an error
        alert('Error with PointerLock API:' + JSON.stringify(event));
    };

    document.addEventListener('pointerlockchange', pointerLockChange, false);
    document.addEventListener('mozpointerlockchange', pointerLockChange, false);
    document.addEventListener('webkitpointerlockchange', pointerLockChange, false);
    document.addEventListener('pointerlockerror', pointerLockError, false);
    document.addEventListener('mozpointerlockerror', pointerLockError, false);
    document.addEventListener('webkitpointerlockerror', pointerLockError, false);
}

function prepareScene() {
    physicalScene.gravity.set(0, -9.82, 0);
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.body.appendChild(renderer.domElement);
    visualScene.fog = new THREE.Fog(0x000000, 10, 100);
    addCamera();
    addSkyBox();
    //addCube();
    addSpotLight();
    addGround();
    createBuilding();
    addGarageDoor();
    loadModel();
    addAmbientMusic();
}

function render() {
    requestAnimationFrame(render);
    var delta = clock.getDelta();
    physicalScene.step(fixedTimeStep, delta, maxSubSteps);
    updateSceneObjects(delta);
    renderer.render(visualScene, camera);
}

function onWindowResize() {
    var w = window.innerWidth;
    var h = window.innerHeight;
    renderer.setSize(w, h);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
}

(function () {
    window.addEventListener('resize', onWindowResize, false);
    prepareScene();
    render();
})();

