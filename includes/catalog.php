<?php
/**
 * Catalog screen shared by index.php (movies) and series.php (series).
 * Expects $type ('movie' | 'series') and $pageFile ('index.php' | 'series.php').
 */
$categories = categories();

$catKey = $_GET['cat'] ?? 'popular';
if (!isset($categories[$catKey])) {
    $catKey = 'popular';
}

$query      = trim((string) ($_GET['q'] ?? ''));
$year       = (int) ($_GET['year'] ?? 0);
$page       = max(1, (int) ($_GET['page'] ?? 1));
$featuredId = (int) ($_GET['f'] ?? 0);

$hero      = get_featured($type, $featuredId);
$premieres = get_premieres($type, 5);

$isFiltered = $query !== '' || $catKey !== 'popular' || $year > 0;

$result = search_catalog($type, [
    'q'     => $query,
    'genre' => $categories[$catKey]['genre'],
    'year'  => $year,
    'sort'  => $catKey === 'score' ? 'score' : 'popular',
], $page, PER_PAGE);

$page   = $result['page'];
$pages  = $result['pages'];
$latest = (!$isFiltered && $page === 1) ? get_latest($type, 12) : [];
$years  = get_years($type);

// Query parameters kept when moving between tabs / pages
$pageParams = ['cat' => $catKey === 'popular' ? null : $catKey, 'q' => $query, 'year' => $year];

if ($query !== '') {
    $gridTitle = 'Results for "' . $query . '"';
} elseif ($isFiltered) {
    $gridTitle = $categories[$catKey]['label'] . ($year ? ' · ' . $year : '');
} else {
    $gridTitle = $type === 'movie' ? 'All movies' : 'All series';
}
?>

<?php if ($hero): ?>
    <?php require BASE_PATH . '/includes/hero.php'; ?>
<?php endif; ?>

<!-- ===== Category tabs + Filter menu ===== -->
<section class="category-bar" id="catalog">
    <div class="container category-bar__inner">
        <nav class="category-tabs" aria-label="Categories">
            <?php foreach ($categories as $key => $cat): ?>
                <?php if (!$cat['tab']) { continue; } ?>
                <a href="<?= e(build_url($pageFile, ['cat' => $key === 'popular' ? null : $key, 'q' => $query, 'year' => $year]) . '#catalog') ?>"
                   class="category-tabs__link<?= $key === $catKey ? ' is-active' : '' ?>"><?= e($cat['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="filter">
            <button type="button" class="filter__toggle" id="filterToggle" aria-expanded="false">
                Filter <span class="filter__arrow">&#9660;</span>
            </button>

            <form class="filter__panel" id="filterPanel" action="<?= e($pageFile) ?>#catalog" method="get" hidden>
                <?php if ($query !== ''): ?>
                    <input type="hidden" name="q" value="<?= e($query) ?>">
                <?php endif; ?>

                <label for="filterCat">Category</label>
                <select name="cat" id="filterCat">
                    <?php foreach ($categories as $key => $cat): ?>
                        <option value="<?= e($key) ?>"<?= $key === $catKey ? ' selected' : '' ?>><?= e($cat['label']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="filterYear">Year</label>
                <select name="year" id="filterYear">
                    <option value="">All</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= (int) $y ?>"<?= (int) $y === $year ? ' selected' : '' ?>><?= (int) $y ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="filter__actions">
                    <button type="submit" class="btn btn--red btn--small">Apply</button>
                    <a href="<?= e($pageFile) ?>#catalog" class="btn btn--dark btn--small">Clear</a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ===== Latest Added (carousel) ===== -->
<?php if ($latest): ?>
    <section class="section section--carousel">
        <div class="container">
            <h2 class="section__title">Latest Added</h2>
        </div>
        <div class="carousel">
            <button type="button" class="carousel__arrow carousel__arrow--prev" data-carousel-prev aria-label="Previous">
                <svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="carousel__track" data-carousel-track>
                <?php foreach ($latest as $card): ?>
                    <?php require BASE_PATH . '/includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="carousel__arrow carousel__arrow--next" data-carousel-next aria-label="Next">
                <svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </section>
<?php endif; ?>

<!-- ===== Grid ===== -->
<section class="section section--grid">
    <div class="container">
        <h2 class="section__title"><?= e($gridTitle) ?> <small>(<?= (int) $result['total'] ?>)</small></h2>

        <?php if ($result['items']): ?>
            <div class="grid">
                <?php foreach ($result['items'] as $card): ?>
                    <?php require BASE_PATH . '/includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php require BASE_PATH . '/includes/pagination.php'; ?>
        <?php else: ?>
            <p class="empty">No results found. Try another search or clear the filter.</p>
        <?php endif; ?>
    </div>
</section>
