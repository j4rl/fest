<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if ($username === '') {
        $errors[] = __('Username is required.');
    }
    if ($password === '') {
        $errors[] = __('Password is required.');
    }
    if ($password !== '' && $passwordConfirm !== '' && $password !== $passwordConfirm) {
        $errors[] = __('Passwords do not match.');
    }

    if (!$errors) {
        $check = db_prepare('SELECT 1 FROM users WHERE username = ?');
        db_execute($check, [$username]);
        if (db_fetch_column($check)) {
            $errors[] = __('That username is already taken.');
        }
    }

    if (!$errors) {
        $stmt = db_prepare('INSERT INTO users (username, password_hash, is_admin, is_approved) VALUES (?, ?, ?, ?)');
        db_execute($stmt, [$username, password_hash($password, PASSWORD_DEFAULT), 0, 0]);
        $notice = __('Thanks! Your account request was sent for approval.');
    }
}

render_header(__('Request access'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div class="muted"><?= h(__('Request access to manage parties')) ?></div>
    <?= lang_switcher() ?>
</header>
<div class="container" style="max-width: 520px;">
    <div class="card">
        <h2><?= h(__('Request access')) ?></h2>
        <p class="muted"><?= h(__('An admin must approve your account before you can log in.')) ?></p>
        <?php if ($notice): ?>
            <div class="notice"><?= h($notice) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="danger-block"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <?php if (!$notice): ?>
        <form method="post">
            <label for="username"><?= h(__('Username')) ?></label>
            <input type="text" name="username" id="username" autocomplete="username" required value="<?= h($_POST['username'] ?? '') ?>">

            <label for="password"><?= h(__('Password')) ?></label>
            <input type="password" name="password" id="password" autocomplete="new-password" required>

            <label for="password_confirm"><?= h(__('Confirm password')) ?></label>
            <input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password" required>

            <button class="btn" type="submit"><?= h(__('Send request')) ?></button>
        </form>
        <?php endif; ?>
        <p class="muted" style="margin-top: 12px;"><a class="muted" href="index.php"><?= h(__('Back to login')) ?></a></p>
    </div>
</div>
<?php render_footer(); ?>
