<?php
/**
 * Detail page for one movie or series.
 */
require __DIR__ . '/includes/bootstrap.php';

$id    = (int) ($_GET['id'] ?? 0);
$title = $id > 0 ? find_title($id) : null;

$showBack = true;

if ($title === null) {
    http_response_code(404);
    $pageTitle = SITE_NAME . ' - Not found';
    $activeTab = null;
    $overlay   = false;

    require BASE_PATH . '/includes/header.php';
    echo '<section class="page-message container"><h1>Title not found</h1>'
       . '<p>The title you are looking for does not exist.</p>'
       . '<a class="btn btn--red" href="index.php">Go to home</a></section>';
    require BASE_PATH . '/includes/footer.php';
    exit;
}

$hero         = $title;
$heroIsDetail = true;
$pageFile     = $title['type'] === 'series' ? 'series.php' : 'index.php';
$premieres    = [];
$related      = get_related($title, 10);

$pageTitle = $title['title'] . ' (' . $title['year'] . ') - ' . SITE_NAME;
$activeTab = $title['type'];
$overlay   = true;

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/hero.php';
?>

<section class="section">
    <div class="container details">
        <h2 class="section__title">Information</h2>
        <dl class="details__list">
            <div><dt>Type</dt><dd><?= $title['type'] === 'series' ? 'Series' : 'Movie' ?></dd></div>
            <div><dt>Year</dt><dd><?= (int) $title['year'] ?></dd></div>
            <div><dt><?= $title['type'] === 'series' ? 'Seasons' : 'Runtime' ?></dt><dd><?= e(format_length($title)) ?></dd></div>
            <div><dt>Genres</dt><dd><?= e($title['genres']) ?></dd></div>
            <div><dt>Rating</dt><dd><?= e(format_rating($title['rating'])) ?> / 10</dd></div>
            <div><dt>Views</dt><dd><?= number_format((int) $title['popularity']) ?></dd></div>
        </dl>
    </div>
</section>

<?php if ($related): ?>
    <section class="section section--carousel">
        <div class="container">
            <h2 class="section__title">More Like This</h2>
        </div>
        <div class="carousel">
            <button type="button" class="carousel__arrow carousel__arrow--prev" data-carousel-prev aria-label="Previous">
                <svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="carousel__track" data-carousel-track>
                <?php foreach ($related as $card): ?>
                    <?php require BASE_PATH . '/includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="carousel__arrow carousel__arrow--next" data-carousel-next aria-label="Next">
                <svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </section>
<?php endif; ?>

<?php require BASE_PATH . '/includes/footer.php'; ?>
