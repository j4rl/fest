<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $eventTime = trim($_POST['event_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $maxGuests = max(1, (int) ($_POST['max_guests'] ?? 1));
    $accent = trim($_POST['theme_accent'] ?? '#f59e0b');
    if (!preg_match('/^#?[0-9a-fA-F]{3,6}$/', $accent)) {
        $accent = '#f59e0b';
    }
    if ($accent[0] !== '#') {
        $accent = '#' . $accent;
    }
    $headerImg = trim($_POST['header_image'] ?? '');
    $uploadedHeader = handle_image_upload('header_upload', $errors);

    if ($title === '') {
        $errors[] = __('Title is required.');
    }

    if (!$errors) {
        $shareCode = randomShareCode();
        $check = db_prepare('SELECT 1 FROM parties WHERE share_code = ?');
        while (true) {
            db_execute($check, [$shareCode]);
            if (!db_fetch_column($check)) {
                break;
            }
            $shareCode = randomShareCode();
        }

        $stmt = db_prepare('INSERT INTO parties (user_id, title, description, event_date, event_time, location, share_code, theme_accent, header_image, max_guests) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        db_execute($stmt, [
            $user['id'],
            $title,
            $description,
            $eventDate ?: null,
            $eventTime ?: null,
            $location ?: null,
            $shareCode,
            $accent ?: null,
            ($uploadedHeader ?: $headerImg) ?: null,
            $maxGuests,
        ]);

        header('Location: party_edit.php?id=' . (int) db_last_id());
        exit;
    }
}

render_header(__('Create a new party'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div style="display:flex; align-items:center; gap:10px;">
        <div class="muted"><?= h(__('Signed in as')) ?> <?= h($user['username']) ?></div>
        <?= lang_switcher() ?>
        <a class="btn secondary" href="dashboard.php"><?= h(__('Dashboard')) ?></a>
        <a class="btn secondary" href="logout.php"><?= h(__('Log out')) ?></a>
    </div>
</header>
<div class="container" style="max-width:720px;">
    <div class="card">
        <h2><?= h(__('Create a new party')) ?></h2>
        <p class="muted" style="margin-top:4px;"> <?= h(__('Set the basics. You can share the link immediately after saving.')) ?> </p>
        <?php if ($errors): ?>
            <div class="danger-block">
                <?= h(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="title"> <?= h(__('Title')) ?> *</label>
            <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? '') ?>">

            <label for="description"><?= h(__('Description')) ?></label>
            <textarea id="description" name="description" placeholder="<?= h(__('What should guests know?')) ?>"><?= h($_POST['description'] ?? '') ?></textarea>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:10px;">
                <div>
                    <label for="event_date"><?= h(__('Date')) ?></label>
                    <input type="date" id="event_date" name="event_date" value="<?= h($_POST['event_date'] ?? '') ?>">
                </div>
                <div>
                    <label for="event_time"><?= h(__('Time')) ?></label>
                    <input type="time" id="event_time" name="event_time" value="<?= h($_POST['event_time'] ?? '') ?>">
                </div>
            </div>

            <label for="location"><?= h(__('Location')) ?></label>
            <input type="text" id="location" name="location" placeholder="<?= h(__('Address or venue')) ?>" value="<?= h($_POST['location'] ?? '') ?>">

            <label for="max_guests"><?= h(__('Max attendees per submission')) ?></label>
            <input type="number" id="max_guests" name="max_guests" min="1" value="<?= h($_POST['max_guests'] ?? '1') ?>">

            <label for="theme_accent"><?= h(__('Accent color')) ?></label>
            <input type="color" id="theme_accent" name="theme_accent" value="<?= h($_POST['theme_accent'] ?? '#f59e0b') ?>">

            <label for="header_image"><?= h(__('Header image URL (optional)')) ?></label>
            <input type="text" id="header_image" name="header_image" placeholder="https://example.com/banner.jpg" value="<?= h($_POST['header_image'] ?? '') ?>">

            <label for="header_upload"><?= h(__('Upload header image')) ?></label>
            <input type="file" id="header_upload" name="header_upload" accept="image/*">

            <button class="btn" type="submit"><?= h(__('Create party')) ?></button>
        </form>
    </div>
</div>
<?php render_footer(); ?>
