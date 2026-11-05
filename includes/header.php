<?php
/**
 * Top of every page. Set these variables before including it:
 *   $pageTitle  - text for the browser tab
 *   $activeTab  - 'movie' | 'series' | null (which pill is highlighted)
 *   $showBack   - true on every page except the main page
 *   $overlay    - true when a hero image sits behind the header
 */
$pageTitle = $pageTitle ?? SITE_NAME;
$activeTab = $activeTab ?? null;
$showBack  = $showBack ?? false;
$overlay   = $overlay ?? false;

$searchAction = $activeTab === 'series' ? 'series.php' : 'index.php';
$user         = current_user();
$flash        = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header<?= $overlay ? ' site-header--overlay' : '' ?>" id="siteHeader">
    <div class="site-header__inner">

        <div class="site-header__left">
            <?php if ($showBack): ?>
                <a href="index.php" class="back-btn" data-back aria-label="Back">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    <span>Back</span>
                </a>
            <?php endif; ?>
            <a class="logo" href="index.php">Absolute<span>SineMatik</span></a>
        </div>

        <nav class="main-tabs" aria-label="Sections">
            <a href="series.php" class="pill<?= $activeTab === 'series' ? ' is-active' : '' ?>">Series</a>
            <a href="index.php" class="pill<?= $activeTab === 'movie' ? ' is-active' : '' ?>">Movies</a>
        </nav>

        <div class="site-header__right">
            <form class="search" action="<?= e($searchAction) ?>" method="get" id="searchForm">
                <input type="search" name="q" id="searchInput" placeholder="Search titles..." autocomplete="off"
                       value="<?= e($_GET['q'] ?? '') ?>">
                <button type="button" id="searchToggle" aria-label="Search">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                </button>
            </form>

            <a href="<?= e($searchAction) ?>#catalog" class="nav-link">Filter</a>

            <?php if ($user): ?>
                <a href="history.php" class="nav-link">History</a>
                <a href="logout.php" class="nav-link">Log out (<?= e(strtok($user['name'], ' ')) ?>)</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
            <?php endif; ?>
        </div>

    </div>
</header>

<?php if ($flash): ?>
    <div class="toast toast--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>

<?php if (is_admin() && count_missing_posters() > 0): ?>
    <div class="admin-bar">
        Some titles have no real poster yet.
        <a href="tools/fetch_posters.php">Fetch posters now</a>
    </div>
<?php endif; ?>

<main>
