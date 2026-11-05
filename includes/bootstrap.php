<?php
/**
 * Loaded first by every page: settings, database, helpers and the session.
 * Define SKIP_SESSION before including this file to avoid starting a session.
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/movies.php';

if (!defined('SKIP_SESSION') && session_status() === PHP_SESSION_NONE) {
    session_start();
}
