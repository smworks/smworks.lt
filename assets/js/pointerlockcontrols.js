/**
 * @author mrdoob / http://mrdoob.com/
 * @author schteppe / https://github.com/schteppe
 */
var PointerLockControls = function (camera, cannonBody, keyPressAction) {

    var velocityFactor = 15;
    var jumpVelocity = 20;
    var scope = this;

    var pitchObject = new THREE.Object3D();
    pitchObject.add(camera);

    var yawObject = new THREE.Object3D();
    //yawObject.position.y = 1.5;
    yawObject.add(pitchObject);

    var quat = new THREE.Quaternion();

    var moveForward = false;
    var moveBackward = false;
    var moveLeft = false;
    var moveRight = false;

    var canJump = false;

    var running = false;

    var startx = 0;
    //var dist = 0;

    //var contactNormal = new CANNON.Vec3(); // Normal in the contact, pointing *out* of whatever the player touched
    //var upAxis = new CANNON.Vec3(0, 1, 0);
    //cannonBody.addEventListener("collide", function (e) {
    //    var contact = e.contact;
    //
    //    // contact.bi and contact.bj are the colliding bodies, and contact.ni is the collision normal.
    //    // We do not yet know which one is which! Let's check.
    //    if (contact.bi.id == cannonBody.id)  // bi is the player body, flip the contact normal
    //        contact.ni.negate(contactNormal);
    //    else
    //        contactNormal.copy(contact.ni); // bi is something else. Keep the normal as it is
    //
    //    // If contactNormal.dot(upAxis) is between 0 and 1, we know that the contact normal is somewhat in the up direction.
    //    if (contactNormal.dot(upAxis) > 0.5) // Use a "good" threshold value between 0 and 1 here!
    //        canJump = true;
    //});

    var velocity = cannonBody.velocity;

    var PI_2 = Math.PI / 2;

    var onMouseMove = function (event) {

        if (scope.enabled === false) return;

        var movementX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
        var movementY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

        yawObject.rotation.y -= movementX / window.innerWidth * 10;
        pitchObject.rotation.x -= movementY / window.innerHeight * 10;

        pitchObject.rotation.x = Math.max(-PI_2, Math.min(PI_2, pitchObject.rotation.x));
    };

    var onKeyDown = function (event) {
        keyPressAction(event.keyCode);
        switch (event.keyCode) {

            case 38: // up
            case 87: // w
                running = event.shiftKey;
                moveForward = true;
                break;

            case 37: // left
            case 65: // a
                moveLeft = true;
                break;

            case 40: // down
            case 83: // s
                moveBackward = true;
                break;

            case 39: // right
            case 68: // d
                moveRight = true;
                break;

            case 32: // space
                if (canJump === true) {
                    velocity.y = jumpVelocity;
                }
                canJump = false;
                break;
        }

    };

    var onKeyUp = function (event) {

        switch (event.keyCode) {

            case 38: // up
            case 87: // w
                running = false;
                moveForward = false;
                break;

            case 37: // left
            case 65: // a
                moveLeft = false;
                break;

            case 40: // down
            case 83: // a
                moveBackward = false;
                break;

            case 39: // right
            case 68: // d
                moveRight = false;
                break;

        }

    };

    var touchStart = function (e) {
        var touchobj = e.changedTouches[0]; // reference first touch point (ie: first finger)
        startx = parseInt(touchobj.clientX); // get x position of touch point relative to left edge of browser
        //statusdiv.innerHTML = 'Status: touchstart<br> ClientX: ' + startx + 'px'
        e.preventDefault();
    };

    var touchMove = function (e) {
        var touchobj = e.changedTouches[0]; // reference first touch point for this event
        var lastX = parseInt(touchobj.clientX);
        var delta = lastX - startx;

        yawObject.rotation.y -= delta / window.innerWidth ;
        //pitchObject.rotation.x -= delta / window.innerHeight * 10;

        //pitchObject.rotation.x = Math.max(-PI_2, Math.min(PI_2, pitchObject.rotation.x));

        //statusdiv.innerHTML = 'Status: touchmove<br> Horizontal distance traveled: ' + dist + 'px';
        e.preventDefault();
    };

    var touchEnd = function (e) {
        //var touchobj = e.changedTouches[0]; // reference first touch point for this event
        //statusdiv.innerHTML = 'Status: touchend<br> Resting x coordinate: ' + touchobj.clientX + 'px'
        e.preventDefault();
        //alert('touchEnd');
    };

    document.addEventListener('mousemove', onMouseMove, false);
    document.addEventListener('keydown', onKeyDown, false);
    document.addEventListener('keyup', onKeyUp, false);
    document.addEventListener('touchstart', touchStart, false);
    document.addEventListener('touchmove', touchMove, false);
    document.addEventListener('touchend', touchEnd, false);

    this.enabled = false;

    this.getObject = function () {
        return yawObject;
    };

    //this.getDirection = function (targetVec) {
    //    targetVec.set(0, 0, -1);
    //    quat.multiplyVector3(targetVec);
    //};

    // Moves the camera to the Cannon.js object position and adds velocity to the object if the run key is down
    var inputVelocity = new THREE.Vector3();
    var euler = new THREE.Euler();
    this.update = function (delta) {
        if (scope.enabled === false) {
            return;
        }
        inputVelocity.set(0, 0, 0);
        if (moveForward) {
            inputVelocity.z = -velocityFactor * delta * (running ? 5 : 1);
        }
        if (moveBackward) {
            inputVelocity.z = velocityFactor * delta;
        }
        if (moveLeft) {
            inputVelocity.x = -velocityFactor * delta;
        }
        if (moveRight) {
            inputVelocity.x = velocityFactor * delta;
        }

        // Convert velocity to world coordinates
        euler.x = pitchObject.rotation.x;
        euler.y = yawObject.rotation.y;
        euler.order = "XYZ";
        quat.setFromEuler(euler);
        inputVelocity.applyQuaternion(quat);
        //quat.multiplyVector3(inputVelocity);

        // Add to the object
        velocity.x += inputVelocity.x;
        velocity.z += inputVelocity.z;

        yawObject.position.copy(cannonBody.position);
    };
};
