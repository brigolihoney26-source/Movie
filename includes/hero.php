<?php
/**
 * Big featured title block (blurred poster background, poster, info, buttons).
 * Expects $hero (a title row). Optional: $heroIsDetail = true on the detail page.
 */
$heroIsDetail = $heroIsDetail ?? false;
?>
<section class="hero">
    <div class="hero__backdrop" style="background-image: <?= e(css_url(poster_src($hero))) ?>"></div>
    <div class="hero__shade"></div>

    <div class="container hero__content">
        <div class="hero__poster">
            <?= poster_img($hero) ?>
        </div>

        <div class="hero__info">
            <h1 class="hero__title">
                <?php if ($heroIsDetail): ?>
                    <?= e($hero['title']) ?>
                <?php else: ?>
                    <a href="movie.php?id=<?= (int) $hero['id'] ?>"><?= e($hero['title']) ?></a>
                <?php endif; ?>
            </h1>
            <p class="hero__year"><?= (int) $hero['year'] ?></p>
            <p class="hero__meta"><?= e(format_length($hero)) ?> | <strong><?= e($hero['genres']) ?></strong></p>

            <div class="hero__rating">
                <span class="hero__star">&#9733;</span>
                <span class="hero__score"><?= e(format_rating($hero['rating'])) ?></span><small>/10</small>
                <span class="imdb-badge">IMDb</span>
            </div>

            <p class="hero__synopsis"><?= e($hero['synopsis']) ?></p>

            <div class="hero__actions">
                <a class="btn btn--dark" href="<?= e(trailer_link($hero)) ?>" target="_blank" rel="noopener">Watch Trailer</a>
                <a class="btn btn--red" href="watch.php?id=<?= (int) $hero['id'] ?>">Watch Now</a>
            </div>
        </div>
    </div>

    <?php if (!empty($premieres)): ?>
        <div class="container hero__premieres">
            <p class="hero__premieres-label">New Releases</p>
            <div class="premieres">
                <?php foreach ($premieres as $item): ?>
                    <a href="<?= e(build_url($pageFile, ['f' => (int) $item['id']])) ?>"
                       class="premiere<?= (int) $item['id'] === (int) $hero['id'] ? ' is-active' : '' ?>"
                       style="background-image: <?= e(css_url(poster_src($item))) ?>"
                       title="<?= e($item['title']) ?>">
                        <span class="premiere__name"><?= e($item['title']) ?></span>
                        <span class="premiere__year"><?= (int) $item['year'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
