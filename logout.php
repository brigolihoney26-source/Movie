<?php
/**
 * Logs the user out (and saves it in the history).
 */
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();

if ($user) {
    log_history($user['id'], $user['email'], 'logout', 'success');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

session_start();
flash_set('info', 'You have been logged out.');
redirect('index.php');
