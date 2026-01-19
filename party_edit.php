<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$partyStmt = db_prepare('SELECT * FROM parties WHERE id = ? AND user_id = ?');
db_execute($partyStmt, [$id, $user['id']]);
$party = db_fetch_one($partyStmt);

if (!$party) {
    http_response_code(404);
    render_header(__('Party not found'));
    echo '<div class="container"><div class="card"><p class="danger-block">' . h(__('Party not found or you do not have access.')) . '</p><a class="btn secondary" href="dashboard.php">' . h(__('Back')) . '</a></div></div>';
    render_footer();
    exit;
}

$errors = [];
$notice = null;
$maxGuests = max(1, (int) ($party['max_guests'] ?? 1));
$shareLink = base_url() . 'submit.php?code=' . urlencode($party['share_code']);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($shareLink);
$accent = $party['theme_accent'] ?: '#f59e0b';
$headerImg = $party['header_image'] ?? '';
if (!preg_match('/^#?[0-9a-fA-F]{3,6}$/', $accent)) {
    $accent = '#f59e0b';
}
if ($accent[0] !== '#') {
    $accent = '#' . $accent;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $eventTime = trim($_POST['event_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $maxGuestsInput = max(1, (int) ($_POST['max_guests'] ?? 1));
    $accentInput = trim($_POST['theme_accent'] ?? '');
    $headerImgInput = trim($_POST['header_image'] ?? '');
    $uploadedHeader = handle_image_upload('header_upload', $errors);

    if (!preg_match('/^#?[0-9a-fA-F]{3,6}$/', $accentInput)) {
        $accentInput = '#f59e0b';
    }
    if ($accentInput !== '' && $accentInput[0] !== '#') {
        $accentInput = '#' . $accentInput;
    }

    if ($title === '') {
        $errors[] = __('Title is required.');
    }

    if (!$errors) {
        $update = db_prepare('UPDATE parties SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, max_guests = ?, theme_accent = ?, header_image = ? WHERE id = ? AND user_id = ?');
        db_execute($update, [
            $title,
            $description,
            $eventDate ?: null,
            $eventTime ?: null,
            $location ?: null,
            $maxGuestsInput,
            $accentInput ?: null,
            ($uploadedHeader ?: $headerImgInput) ?: null,
            $party['id'],
            $user['id'],
        ]);
        $notice = __('Party updated.');
        // Refresh data
        db_execute($partyStmt, [$id, $user['id']]);
        $party = db_fetch_one($partyStmt);
        $maxGuests = max(1, (int) ($party['max_guests'] ?? 1));
        $accent = $party['theme_accent'] ?: '#f59e0b';
        $headerImg = $party['header_image'] ?? '';
    }
}

render_header(__('Edit party') . ' - ' . $party['title']);
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <a class="btn secondary" href="dashboard.php"><?= h(__('Dashboard')) ?></a>
        <a class="btn secondary" href="party.php?id=<?= (int)$party['id'] ?>"><?= h(__('Details & QR')) ?></a>
        <a class="btn secondary" href="logout.php"><?= h(__('Log out')) ?></a>
        <?= lang_switcher() ?>
    </div>
</header>
<div class="container">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;"> <?= h($party['title']) ?> </h2>
                <div class="muted" style="margin-top:4px;"> <?= h($party['event_date'] ?: __('Date TBD')) ?> | <?= h($party['event_time'] ?: __('Time TBD')) ?> </div>
                <?php if ($party['location']): ?>
                    <div style="margin-top:6px;"> <?= h($party['location']) ?> </div>
                <?php endif; ?>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Max attendees per response:')) ?> <?= h((string)$maxGuests) ?></div>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Accent color')) ?>: <?= h($accent ?: '#f59e0b') ?></div>
            </div>
            <div style="text-align:right;">
                <div class="pill" style="background:#334155; color:#e5e7eb;"><?= h(__('Share link')) ?> &amp; QR</div>
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;">QR</h3>
            <div class="qr">
                <img src="<?= h($qrUrl) ?>" alt="QR code to submission form">
            </div>
            <div class="muted" style="margin-top:8px; word-break:break-all; font-size:12px;"> <?= h($shareLink) ?> </div>
        </div>
        <?php if ($headerImg): ?>
        <div class="card">
            <h3 style="margin-top:0;"><?= h(__('Header image preview')) ?></h3>
            <img src="<?= h($headerImg) ?>" alt="Header" style="max-width:100%; border-radius:10px; display:block;">
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Edit party details')) ?></h3>
        <?php if ($notice): ?>
            <div class="notice"><?= h($notice) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="danger-block"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="title"><?= h(__('Title')) ?> *</label>
            <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? $party['title']) ?>">

            <label for="description"><?= h(__('Description')) ?></label>
            <textarea id="description" name="description" placeholder="<?= h(__('What should guests know?')) ?>"><?= h($_POST['description'] ?? $party['description'] ?? '') ?></textarea>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap:10px;">
                <div>
                    <label for="event_date"><?= h(__('Date')) ?></label>
                    <input type="date" id="event_date" name="event_date" value="<?= h($_POST['event_date'] ?? $party['event_date']) ?>">
                </div>
                <div>
                    <label for="event_time"><?= h(__('Time')) ?></label>
                    <input type="time" id="event_time" name="event_time" value="<?= h($_POST['event_time'] ?? $party['event_time']) ?>">
                </div>
            </div>

            <label for="location"><?= h(__('Location')) ?></label>
            <input type="text" id="location" name="location" placeholder="<?= h(__('Address or venue')) ?>" value="<?= h($_POST['location'] ?? $party['location']) ?>">

            <label for="max_guests"><?= h(__('Max attendees per submission')) ?></label>
            <input type="number" id="max_guests" name="max_guests" min="1" value="<?= h($_POST['max_guests'] ?? $party['max_guests']) ?>">

            <label for="theme_accent"><?= h(__('Accent color')) ?></label>
            <input type="color" id="theme_accent" name="theme_accent" value="<?= h($_POST['theme_accent'] ?? $accent) ?>">

            <label for="header_image"><?= h(__('Header image URL (optional)')) ?></label>
            <input type="text" id="header_image" name="header_image" placeholder="https://example.com/banner.jpg" value="<?= h($_POST['header_image'] ?? $headerImg) ?>">

            <label for="header_upload"><?= h(__('Upload header image')) ?></label>
            <input type="file" id="header_upload" name="header_upload" accept="image/*">

            <button class="btn" type="submit"><?= h(__('Save changes')) ?></button>
        </form>
    </div>
</div>
<?php render_footer(); ?>
