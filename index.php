<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = __('Please enter both username and password.');
    } elseif (attempt_login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = __('Invalid credentials.');
    }
}

render_header(__('Fest Planner') . ' - ' . __('Log in'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div class="muted"><?= h(__('Log in to manage parties')) ?></div>
    <?= lang_switcher() ?>
</header>
<div class="container" style="max-width: 520px;">
    <div class="card">
        <h2><?= h(__('Welcome back')) ?></h2>
        <p class="muted"><?= h(__('Use your account to create parties and collect responses.')) ?></p>
        <?php if ($error): ?>
            <div class="danger-block"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="username"><?= h(__('Username')) ?></label>
            <input type="text" name="username" id="username" autocomplete="username" required>

            <label for="password"><?= h(__('Password')) ?></label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>

            <button class="btn" type="submit"><?= h(__('Log in')) ?></button>
        </form>
        <p class="muted" style="margin-top: 12px;"><?= h(__('Default admin login:')) ?> <strong>admin / admin123</strong>. <?= h(__('Change it in the database after first login.')) ?></p>
    </div>
</div>
<?php render_footer(); ?>
