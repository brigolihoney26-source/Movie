<?php
/**
 * Series catalog (same layout as the main page, with a Back button).
 */
require __DIR__ . '/includes/bootstrap.php';

$type      = 'series';
$pageFile  = 'series.php';
$pageTitle = SITE_NAME . ' - Series';
$activeTab = 'series';
$showBack  = true;
$overlay   = true;

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/catalog.php';
require BASE_PATH . '/includes/footer.php';
