<?php
/**
 * Login / register history.
 * Admins see everyone (emails + password hashes). Normal users only see their own entries.
 */
require __DIR__ . '/includes/bootstrap.php';

require_login();

$admin = is_admin();

if ($admin) {
    $stmt = db()->query(
        'SELECT id, email, action, status, password_hash, ip_address, created_at
         FROM login_history ORDER BY id DESC LIMIT 300'
    );
} else {
    $stmt = db()->prepare(
        'SELECT id, email, action, status, NULL AS password_hash, ip_address, created_at
         FROM login_history WHERE user_id = :uid ORDER BY id DESC LIMIT 300'
    );
    $stmt->execute([':uid' => current_user()['id']]);
}
$rows = $stmt->fetchAll();

$actionLabels = ['login' => 'Login', 'register' => 'Register', 'logout' => 'Logout'];

$pageTitle = SITE_NAME . ' - History';
$activeTab = null;
$showBack  = true;
$overlay   = false;

require BASE_PATH . '/includes/header.php';
?>

<section class="container history">
    <h1>Access history</h1>
    <p class="history__note">
        <?= $admin ? 'Showing every login and register attempt.' : 'Showing your access activity.' ?>
        Passwords are stored hashed, never as plain text.
    </p>

    <?php if ($rows): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>Action</th>
                        <th>Status</th>
                        <?php if ($admin): ?><th>Password (hash)</th><?php endif; ?>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($actionLabels[$row['action']] ?? $row['action']) ?></td>
                            <td><span class="badge badge--<?= e($row['status']) ?>"><?= $row['status'] === 'success' ? 'Success' : 'Failed' ?></span></td>
                            <?php if ($admin): ?>
                                <td class="table__hash" title="<?= e($row['password_hash']) ?>"><?= e($row['password_hash'] ? substr($row['password_hash'], 0, 24) . '…' : '—') ?></td>
                            <?php endif; ?>
                            <td><?= e($row['ip_address']) ?></td>
                            <td><?= e($row['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No activity recorded yet.</p>
    <?php endif; ?>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
