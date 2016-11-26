<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

/**
 * File is used to launch CMS load sequence.
 * @since 2011 02 19
 */

// Collects main core files.
require_once CORE . 'utils.php';
require_once CORE . 'singleton.php';
require_once CORE . 'strings.php';
require_once CORE . 'config.php';
require_once CORE . 'db.php';
require_once CORE . 'user.php';

// Remove possible threats from GET and POST arrays.
Utils::secureGetAndPost();