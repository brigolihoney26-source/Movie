<?php
/**
 * One poster card. Expects $card (a title row).
 */
?>
<a class="card" href="movie.php?id=<?= (int) $card['id'] ?>">
    <div class="card__poster">
        <?= poster_img($card) ?>
        <span class="card__rating">&#9733; <?= e(format_rating($card['rating'])) ?></span>
    </div>
    <h3 class="card__title"><?= e($card['title']) ?></h3>
    <p class="card__year"><?= (int) $card['year'] ?></p>
</a>
