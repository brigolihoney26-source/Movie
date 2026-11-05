<?php
/**
 * Login page. Every attempt (success or failed) is saved in login_history.
 */
require __DIR__ . '/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);

if (is_logged_in()) {
    redirect($next);
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $errors[] = 'Enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];

            db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);
            log_history((int) $user['id'], $email, 'login', 'success', $password);

            flash_set('success', 'Welcome back, ' . $user['name'] . '.');
            redirect($next);
        }

        log_history($user ? (int) $user['id'] : null, $email, 'login', 'failed', $password);
        $errors[] = 'Incorrect email or password.';
    }
}

$pageTitle = SITE_NAME . ' - Login';
$activeTab = null;
$showBack  = true;
$overlay   = false;

require BASE_PATH . '/includes/header.php';
?>

<section class="auth">
    <div class="auth__card">
        <h1>Log in</h1>
        <p class="auth__subtitle">Sign in to watch movies and series.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert--error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" action="login.php" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required autofocus>

            <label for="password">Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <button type="button" class="password-field__toggle" data-toggle-password="password">Show</button>
            </div>

            <button type="submit" class="btn btn--red btn--block">Log in</button>
        </form>

        <p class="auth__switch">Don't have an account? <a href="register.php<?= $next !== 'index.php' ? '?next=' . urlencode($next) : '' ?>">Sign up</a></p>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
