<?php
/**
 * Page number links. Expects:
 *   $page, $pages   - current page and total pages
 *   $pageFile       - e.g. "index.php"
 *   $pageParams     - the other query parameters to keep (cat, q, year...)
 */
if ($pages <= 1) {
    return;
}
?>
<nav class="pagination" aria-label="Pages">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="<?= e(build_url($pageFile, $pageParams + ['page' => $i]) . '#catalog') ?>"
           class="pagination__link<?= $i === $page ? ' is-active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</nav>
