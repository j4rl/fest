<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();
if ((int) ($user['is_admin'] ?? 0) !== 1) {
    http_response_code(403);
    render_header(__('Forbidden'));
    echo '<div class="container"><div class="card"><p class="danger-block">' . h(__('You do not have access to this page.')) . '</p><a class="btn secondary" href="dashboard.php">' . h(__('Back')) . '</a></div></div>';
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $targetId = (int) ($_POST['user_id'] ?? 0);
    if ($targetId > 0) {
        if ($action === 'approve') {
            $approve = db_prepare('UPDATE users SET is_approved = 1 WHERE id = ? AND is_admin = 0');
            db_execute($approve, [$targetId]);
        }
        if ($action === 'delete') {
            $delete = db_prepare('DELETE FROM users WHERE id = ? AND is_admin = 0');
            db_execute($delete, [$targetId]);
        }
    }
    header('Location: admin_users.php');
    exit;
}

$pendingStmt = db_prepare('SELECT id, username, created_at FROM users WHERE is_approved = 0 AND is_admin = 0 ORDER BY created_at DESC');
db_execute($pendingStmt);
$pending = db_fetch_all($pendingStmt);

$activeStmt = db_prepare('SELECT id, username, created_at FROM users WHERE is_approved = 1 AND is_admin = 0 ORDER BY created_at DESC');
db_execute($activeStmt);
$active = db_fetch_all($activeStmt);

$adminStmt = db_prepare('SELECT id, username, created_at FROM users WHERE is_admin = 1 ORDER BY created_at DESC');
db_execute($adminStmt);
$admins = db_fetch_all($adminStmt);

render_header(__('User approvals'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <a class="btn secondary" href="dashboard.php"><?= h(__('Dashboard')) ?></a>
        <a class="btn secondary" href="logout.php"><?= h(__('Log out')) ?></a>
        <?= lang_switcher() ?>
    </div>
</header>
<div class="container">
    <div class="card">
        <h2 style="margin-top:0;"><?= h(__('User approvals')) ?></h2>
        <p class="muted"><?= h(__('Approve new accounts before they can manage parties.')) ?></p>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Pending requests')) ?> (<?= count($pending) ?>)</h3>
        <?php if (!$pending): ?>
            <p class="muted"><?= h(__('No pending users.')) ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th><?= h(__('Username')) ?></th>
                        <th><?= h(__('Requested')) ?></th>
                        <th><?= h(__('Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $pendingUser): ?>
                        <tr>
                            <td><?= h($pendingUser['username']) ?></td>
                            <td class="muted"><?= h($pendingUser['created_at']) ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="user_id" value="<?= (int) $pendingUser['id'] ?>">
                                    <button class="btn secondary" type="submit"><?= h(__('Approve')) ?></button>
                                </form>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user request?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= (int) $pendingUser['id'] ?>">
                                    <button class="btn secondary" type="submit" style="border-color: rgba(239, 68, 68, 0.6); color:#fecdd3;">
                                        <?= h(__('Delete')) ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;"><?= h(__('Active users')) ?> (<?= count($active) ?>)</h3>
            <?php if (!$active): ?>
                <p class="muted"><?= h(__('No active users yet.')) ?></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($active as $activeUser): ?>
                        <li><?= h($activeUser['username']) ?> <span class="muted">(<?= h($activeUser['created_at']) ?>)</span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3 style="margin-top:0;"><?= h(__('Admins')) ?> (<?= count($admins) ?>)</h3>
            <ul>
                <?php foreach ($admins as $admin): ?>
                    <li><?= h($admin['username']) ?> <span class="muted">(<?= h($admin['created_at']) ?>)</span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php render_footer(); ?>
