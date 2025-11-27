<?php

//------------------------------------------------------------
//	Author: Martin
//	Since: 2011 02 19
//	Email: martynas.su@gmail.com
//------------------------------------------------------------

define('START_TIME', microtime(true));

// Start session.
session_start();

// Sets error reporting.
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define global paths and files.
define('PATH', __DIR__);
define('HTML_PATH', basename(__DIR__));
define('TEMPLATES', PATH . '/assets/templates/');
define('URL', $_SERVER['SERVER_NAME'] . ':8080' . $_SERVER['SCRIPT_NAME']);
define('CORE', PATH . '/core/');

try {
    require_once CORE . 'ini.php';
    require_once 'content.php';
    Content::getInstance()->display();
} catch (Exception $e) {
    printf('Error!<br>Message: %s<br>', $e->getMessage());
    printf('Code: %s<br>', $e->getCode());
    printf('File: %s<br>', $e->getFile());
    printf('Line: %s<br>', $e->getLine());
    printf('Check server logs for full error description');
    file_put_contents('errors.txt', '[' . date('Y-m-d H:i:s') . ']' . $e->getMessage() . "\n"
        . Utils::getExceptionTraceAsString($e) . "\n", FILE_APPEND);
    http_response_code(500);
}