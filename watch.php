<?php
/**
 * "Watch Now" page. Requires login. Shows a demo player.
 * Put a real video file address in titles.video_url to play it here.
 */
require __DIR__ . '/includes/bootstrap.php';

require_login();

$id    = (int) ($_GET['id'] ?? 0);
$title = $id > 0 ? find_title($id) : null;

if ($title === null) {
    flash_set('error', 'Title not found.');
    redirect('index.php');
}

add_view($id);

$pageTitle = 'Watch ' . $title['title'] . ' - ' . SITE_NAME;
$activeTab = $title['type'];
$showBack  = true;
$overlay   = false;

require BASE_PATH . '/includes/header.php';
?>

<section class="watch container">
    <div class="player">
        <?php if (!empty($title['video_url'])): ?>
            <video controls preload="metadata" poster="<?= e(poster_src($title)) ?>">
                <source src="<?= e($title['video_url']) ?>">
                Your browser does not support video.
            </video>
        <?php else: ?>
            <div class="player__placeholder" style="background-image: <?= e(css_url(poster_src($title))) ?>">
                <div class="player__overlay">
                    <span class="player__play">&#9654;</span>
                    <p>Demo player</p>
                    <small>No video has been assigned to this title yet.</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="watch__info">
        <h1><?= e($title['title']) ?> <small>(<?= (int) $title['year'] ?>)</small></h1>
        <p class="hero__meta"><?= e(format_length($title)) ?> | <strong><?= e($title['genres']) ?></strong></p>
        <p class="watch__synopsis"><?= e($title['synopsis']) ?></p>
        <div class="hero__actions">
            <a class="btn btn--dark" href="<?= e(trailer_link($title)) ?>" target="_blank" rel="noopener">Watch Trailer</a>
            <a class="btn btn--red" href="movie.php?id=<?= (int) $title['id'] ?>">Details</a>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
