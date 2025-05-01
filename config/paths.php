<?php
// config/paths.php

// Make sure no output is sent before this
ob_start(); // Start output buffering

// Database connection
require_once __DIR__ . '/../db.php';

define('BASE_URL', '/FinalProject/final_Project_Web/');
define('BASE_PATH', __DIR__ . '/../');

function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function redirect($path) {
    // Clear the output buffer before redirect
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . url($path));
    exit();
}

function is_current_page($page) {
    return basename($_SERVER['SCRIPT_NAME']) === $page;
}