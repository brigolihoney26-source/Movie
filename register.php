<?php
/**
 * Register page. Every attempt (success or failed) is saved in login_history.
 */
require __DIR__ . '/includes/bootstrap.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);

if (is_logged_in()) {
    redirect($next);
}

$errors = [];
$name   = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim((string) ($_POST['name'] ?? ''));
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($name === '' || mb_strlen($name) > 80) {
        $errors[] = 'Enter your name (max 80 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if ($errors) {
        if ($email !== '') {
            log_history(null, $email, 'register', 'failed', $password);
        }
    } else {
        $insert = db()->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, \'user\')'
        );
        $insert->execute([
            ':name'  => $name,
            ':email' => $email,
            ':hash'  => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) db()->lastInsertId();

        log_history($userId, $email, 'register', 'success', $password);

        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'user'];

        flash_set('success', 'Account created. Welcome, ' . $name . '!');
        redirect($next);
    }
}

$pageTitle = SITE_NAME . ' - Register';
$activeTab = null;
$showBack  = true;
$overlay   = false;

require BASE_PATH . '/includes/header.php';
?>

<section class="auth">
    <div class="auth__card">
        <h1>Create account</h1>
        <p class="auth__subtitle">Join <?= e(SITE_NAME) ?>.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert--error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" action="register.php" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">

            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= e($name) ?>" placeholder="Your name" required autofocus>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required>

            <label for="password">Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" placeholder="At least <?= MIN_PASSWORD_LENGTH ?> characters" required>
                <button type="button" class="password-field__toggle" data-toggle-password="password">Show</button>
            </div>

            <label for="confirm">Confirm password</label>
            <div class="password-field">
                <input type="password" id="confirm" name="confirm" placeholder="Repeat password" required>
                <button type="button" class="password-field__toggle" data-toggle-password="confirm">Show</button>
            </div>

            <button type="submit" class="btn btn--red btn--block">Sign up</button>
        </form>

        <p class="auth__switch">Already have an account? <a href="login.php<?= $next !== 'index.php' ? '?next=' . urlencode($next) : '' ?>">Log in</a></p>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
