<?php
/**
 * General helper functions: output, redirects, sessions, CSRF, history log and display helpers.
 */

/* ---------- Output & navigation ---------- */

/** Escape text for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Build "file.php?a=1&b=2", skipping empty values and page 1. */
function build_url(string $file, array $params = []): string
{
    $clean = [];
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 0 || ($key === 'page' && (int) $value === 1)) {
            continue;
        }
        $clean[$key] = $value;
    }

    return $file . ($clean ? '?' . http_build_query($clean) : '');
}

/** Only allow redirects to local pages such as "movie.php?id=3". */
function safe_next(?string $next): string
{
    if ($next !== null && preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9=&_%.\-]*)?$/i', $next)) {
        return $next;
    }

    return 'index.php';
}

/* ---------- Flash messages ---------- */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/* ---------- CSRF protection ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

/* ---------- Authentication state ---------- */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

/** Send guests to the login page and bring them back afterwards. */
function require_login(): void
{
    if (is_logged_in()) {
        return;
    }

    $target = basename($_SERVER['SCRIPT_NAME']);
    if (!empty($_SERVER['QUERY_STRING'])) {
        $target .= '?' . $_SERVER['QUERY_STRING'];
    }

    flash_set('info', 'Please log in to continue.');
    redirect('login.php?next=' . urlencode($target));
}

/* ---------- Login / register history ---------- */

/**
 * Save one entry in the login_history table.
 * Passwords are never stored as plain text: only a bcrypt hash of what was typed.
 */
function log_history(?int $userId, string $email, string $action, string $status, ?string $password = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO login_history (user_id, email, action, status, password_hash, ip_address, user_agent)
         VALUES (:user_id, :email, :action, :status, :password_hash, :ip, :agent)'
    );

    $stmt->execute([
        ':user_id'       => $userId,
        ':email'         => substr($email, 0, 190),
        ':action'        => $action,
        ':status'        => $status,
        ':password_hash' => $password !== null && $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
        ':ip'            => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
        ':agent'         => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
}

/* ---------- Display helpers ---------- */

/**
 * Image address for a title. Order of priority:
 *   1. your own file  assets/posters/<id>.jpg  (.jpeg, .png or .webp)
 *   2. the poster_url saved in the database
 *   3. the generated placeholder poster
 */
function poster_src(array $title): string
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $relative = 'assets/posters/' . (int) $title['id'] . '.' . $ext;
        if (is_file(BASE_PATH . '/' . $relative)) {
            return $relative;
        }
    }

    if (!empty($title['poster_url'])) {
        return $title['poster_url'];
    }

    return generated_poster_src($title);
}

function generated_poster_src(array $title): string
{
    return 'poster.php?id=' . (int) $title['id'];
}

/** <img> tag that falls back to the generated poster if the image fails to load. */
function poster_img(array $title, string $class = ''): string
{
    return '<img src="' . e(poster_src($title)) . '"'
        . ' data-fallback="' . e(generated_poster_src($title)) . '"'
        . ' alt="' . e($title['title']) . '"'
        . ($class !== '' ? ' class="' . e($class) . '"' : '')
        . ' loading="lazy">';
}

/** Value for an inline CSS background-image. */
function css_url(string $url): string
{
    $safe = str_replace(["'", '"', '(', ')', ' ', '\\'], ['%27', '%22', '%28', '%29', '%20', '%5C'], $url);

    return "url('" . $safe . "')";
}

/** "2hr 24 min" for movies, "3 temporadas" for series. */
function format_length(array $title): string
{
    if ($title['type'] === 'series') {
        $seasons = (int) $title['seasons'];
        return $seasons . ($seasons === 1 ? ' season' : ' seasons');
    }

    $minutes = (int) $title['runtime_min'];

    return intdiv($minutes, 60) . 'hr ' . ($minutes % 60) . ' min';
}

/** Rating with one decimal: 8.1 */
function format_rating($rating): string
{
    return number_format((float) $rating, 1, '.', ',');
}

function trailer_link(array $title): string
{
    if (!empty($title['trailer_url'])) {
        return $title['trailer_url'];
    }

    return 'https://www.youtube.com/results?search_query='
        . urlencode($title['title'] . ' ' . $title['year'] . ' official trailer');
}

/** Catalog categories. "tab" = shown in the tab bar, others live in the Filter menu. */
function categories(): array
{
    return [
        'popular'     => ['label' => 'Popular',         'genre' => null,          'tab' => true],
        'score'       => ['label' => 'Top Rated',       'genre' => null,          'tab' => true],
        'action'      => ['label' => 'Action',          'genre' => 'Action',      'tab' => true],
        'adventure'   => ['label' => 'Adventure',       'genre' => 'Adventure',   'tab' => true],
        'comedy'      => ['label' => 'Comedy',         'genre' => 'Comedy',      'tab' => true],
        'crime'       => ['label' => 'Crime',          'genre' => 'Crime',       'tab' => true],
        'fantasy'     => ['label' => 'Fantasy',        'genre' => 'Fantasy',     'tab' => true],
        'family'      => ['label' => 'Family',         'genre' => 'Family',      'tab' => true],
        'animation'   => ['label' => 'Animation',      'genre' => 'Animation',   'tab' => false],
        'documentary' => ['label' => 'Documentary',     'genre' => 'Documentary', 'tab' => false],
        'drama'       => ['label' => 'Drama',           'genre' => 'Drama',       'tab' => false],
        'horror'      => ['label' => 'Horror',          'genre' => 'Horror',      'tab' => false],
        'mystery'     => ['label' => 'Mystery',        'genre' => 'Mystery',     'tab' => false],
        'romance'     => ['label' => 'Romance',         'genre' => 'Romance',     'tab' => false],
        'sci-fi'      => ['label' => 'Sci-Fi', 'genre' => 'Sci-Fi',      'tab' => false],
        'thriller'    => ['label' => 'Thriller',       'genre' => 'Thriller',    'tab' => false],
    ];
}
