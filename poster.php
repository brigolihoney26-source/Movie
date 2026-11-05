<?php
/**
 * Generates a poster image (SVG) from a title's name, year and genre.
 * Used when a title has no poster_url, or when the poster_url fails to load.
 * Usage: poster.php?id=5
 */
define('SKIP_SESSION', true);
require __DIR__ . '/includes/bootstrap.php';

$id    = (int) ($_GET['id'] ?? 0);
$title = $id > 0 ? find_title($id) : null;

$name  = $title['title'] ?? SITE_NAME;
$year  = $title['year'] ?? '';
$genre = $title ? explode(', ', (string) $title['genres'])[0] : 'Cine';

// Colors come from the title so each poster looks different but stays the same every time
$hue1 = crc32($name) % 360;
$hue2 = ($hue1 + 45) % 360;

// Wrap the title into lines of up to 11 characters (max 5 lines)
$lines = explode("\n", wordwrap(mb_strtoupper($name), 11, "\n", true));
$lines = array_slice($lines, 0, 5);

$longest  = max(array_map('mb_strlen', $lines));
$fontSize = $longest <= 7 ? 46 : ($longest <= 9 ? 38 : 32);
$lineGap  = $fontSize + 8;
$startY   = 225 - (count($lines) - 1) * $lineGap / 2;

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 450" width="300" height="450">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="hsl(<?= $hue1 ?>, 65%, 38%)"/>
            <stop offset="1" stop-color="hsl(<?= $hue2 ?>, 70%, 12%)"/>
        </linearGradient>
        <radialGradient id="glow" cx="0.5" cy="0.35" r="0.6">
            <stop offset="0" stop-color="#fff" stop-opacity="0.22"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </radialGradient>
    </defs>

    <rect width="300" height="450" fill="url(#bg)"/>
    <rect width="300" height="450" fill="url(#glow)"/>
    <circle cx="240" cy="70" r="110" fill="#fff" fill-opacity="0.05"/>
    <circle cx="40" cy="400" r="130" fill="#000" fill-opacity="0.18"/>
    <rect x="14" y="14" width="272" height="422" fill="none" stroke="#fff" stroke-opacity="0.35" stroke-width="1.5"/>

    <text x="150" y="58" text-anchor="middle" fill="#fff" fill-opacity="0.8" font-family="Arial, Helvetica, sans-serif" font-size="13" letter-spacing="5"><?= e(mb_strtoupper($genre)) ?></text>
    <line x1="110" y1="72" x2="190" y2="72" stroke="#fff" stroke-opacity="0.5"/>

    <?php foreach ($lines as $i => $line): ?>
        <text x="150" y="<?= round($startY + $i * $lineGap) ?>" text-anchor="middle" fill="#fff" font-family="Arial Black, Arial, Helvetica, sans-serif" font-weight="900" font-size="<?= $fontSize ?>"><?= e($line) ?></text>
    <?php endforeach; ?>

    <text x="150" y="396" text-anchor="middle" fill="#fff" fill-opacity="0.9" font-family="Arial, Helvetica, sans-serif" font-size="20" letter-spacing="4"><?= e($year) ?></text>
    <text x="150" y="420" text-anchor="middle" fill="#ff5252" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="700" letter-spacing="2"><?= e(mb_strtoupper(SITE_NAME)) ?></text>
</svg>
