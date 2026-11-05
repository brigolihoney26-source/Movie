<?php
/**
 * One-click tool: downloads the real poster image address of every title
 * from Wikipedia and saves it in titles.poster_url.
 *
 * Run it once while logged in as admin:  http://localhost/AbsoluteSineMatik/tools/fetch_posters.php
 *   ?redo=1      also replace posters that are already set
 *   ?insecure=1  skip SSL certificate check (only if you get an SSL error on XAMPP)
 */
require dirname(__DIR__) . '/includes/bootstrap.php';

if (!is_admin()) {
    flash_set('info', 'Log in as admin to fetch posters.');
    redirect('../login.php?next=' . urlencode('tools/fetch_posters.php'));
}

set_time_limit(0);

$redo     = isset($_GET['redo']);
$insecure = isset($_GET['insecure']);

/** Calls the Wikipedia API and returns the decoded JSON (or an error string). */
function wiki_request(string $search, bool $insecure): array
{
    $url = 'https://en.wikipedia.org/w/api.php?' . http_build_query([
        'action'       => 'query',
        'format'       => 'json',
        'generator'    => 'search',
        'gsrsearch'    => $search,
        'gsrlimit'     => 4,
        'prop'         => 'pageimages',
        'piprop'       => 'thumbnail',
        'pithumbsize'  => 500,
    ]);

    if (!function_exists('curl_init')) {
        return ['error' => 'The PHP curl extension is not enabled (enable extension=curl in php.ini).'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'AbsoluteSineMatik/1.0 (school project; poster fetcher)',
        CURLOPT_SSL_VERIFYPEER => !$insecure,
        CURLOPT_SSL_VERIFYHOST => $insecure ? 0 : 2,
    ]);
    $body  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['error' => $error ?: 'Request failed'];
    }

    $json = json_decode($body, true);

    return is_array($json) ? $json : ['error' => 'Unexpected answer from Wikipedia'];
}

/** Picks the poster image address for one title, or null. */
function find_poster(array $title, bool $insecure, ?string &$error): ?string
{
    $search = $title['type'] === 'series'
        ? $title['title'] . ' ' . $title['year'] . ' television series'
        : $title['title'] . ' ' . $title['year'] . ' film';

    $json = wiki_request($search, $insecure);
    if (isset($json['error'])) {
        $error = $json['error'];
        return null;
    }

    $pages = $json['query']['pages'] ?? [];
    uasort($pages, fn ($a, $b) => ($a['index'] ?? 99) <=> ($b['index'] ?? 99));

    foreach ($pages as $page) {
        $src = $page['thumbnail']['source'] ?? '';
        if ($src !== '' && preg_match('/\.(jpe?g|png)(\/|$|\?)/i', $src)) {
            return $src;
        }
    }

    return null;
}

$where = $redo ? '' : 'WHERE poster_url IS NULL';
$rows  = db()->query("SELECT id, type, title, year FROM titles $where ORDER BY id")->fetchAll();
$update = db()->prepare('UPDATE titles SET poster_url = :url WHERE id = :id');

$pageTitle = SITE_NAME . ' - Fetch posters';
$activeTab = null;
$showBack  = true;
$overlay   = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tool { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .tool li { display: flex; align-items: center; gap: 12px; padding: 6px 0; border-bottom: 1px solid var(--line); list-style: none; }
        .tool ul { padding: 0; }
        .tool img { width: 34px; height: 51px; object-fit: cover; border-radius: 2px; background: #222; }
        .ok { color: #6fd675; } .miss { color: #ff8a8a; }
    </style>
</head>
<body>
<div class="tool">
    <a href="../index.php" class="back-btn">&lsaquo; Back</a>
    <h1>Fetching posters from Wikipedia…</h1>
    <ul>
<?php
$found = 0;
$failed = 0;
$stop  = null;

foreach ($rows as $row) {
    $error = null;
    $url   = find_poster($row, $insecure, $error);

    if ($error !== null) {
        $stop = $error;
        break;
    }

    if ($url !== null) {
        $update->execute([':url' => $url, ':id' => $row['id']]);
        $found++;
        echo '<li><img src="' . e($url) . '" alt=""><span class="ok">&#10003;</span> ' . e($row['title']) . ' (' . (int) $row['year'] . ')</li>';
    } else {
        $failed++;
        echo '<li><span class="miss">&#10007;</span> ' . e($row['title']) . ' - no poster found (generated poster will be used)</li>';
    }

    echo str_repeat(' ', 1024); // push output to the browser as we go
    @ob_flush();
    flush();
}
?>
    </ul>

<?php if ($stop !== null): ?>
    <p class="alert alert--error">Stopped: <?= e($stop) ?></p>
    <?php if (stripos($stop, 'ssl') !== false || stripos($stop, 'certificate') !== false): ?>
        <p>XAMPP could not verify Wikipedia's SSL certificate. If you only want to fetch public poster links, you can run it once without the check:</p>
        <p><a class="btn btn--red" href="fetch_posters.php?insecure=1">Run without SSL check</a></p>
    <?php else: ?>
        <p>Make sure this computer is online, then <a href="fetch_posters.php" style="color:#ff5252">try again</a>.</p>
    <?php endif; ?>
<?php else: ?>
    <h2>Done: <?= $found ?> posters saved, <?= $failed ?> not found.</h2>
    <p><a class="btn btn--red" href="../index.php">Go to the site</a></p>
<?php endif; ?>
</div>
</body>
</html>
