<?php
/**
 * MAIN PAGE - Movies. (The only page without a Back button.)
 */
require __DIR__ . '/includes/bootstrap.php';

$type      = 'movie';
$pageFile  = 'index.php';
$pageTitle = SITE_NAME . ' - Movies';
$activeTab = 'movie';
$showBack  = false;
$overlay   = true;

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/catalog.php';
require BASE_PATH . '/includes/footer.php';
