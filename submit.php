<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');

if ($code === '') {
    http_response_code(400);
    render_header(__('Missing link'));
    echo '<div class="container"><div class="card"><p class="danger-block">' . h(__('Missing or invalid invite link.')) . '</p></div></div>';
    render_footer();
    exit;
}

$stmt = db_prepare('SELECT * FROM parties WHERE share_code = ?');
db_execute($stmt, [$code]);
$party = db_fetch_one($stmt);

if (!$party) {
    http_response_code(404);
    render_header(__('Not found'));
    echo '<div class="container"><div class="card"><p class="danger-block">' . h(__('This invite link is no longer valid.')) . '</p></div></div>';
    render_footer();
    exit;
}

$errors = [];
$maxGuests = max(1, (int) ($party['max_guests'] ?? 1));
$accent = $party['theme_accent'] ?: '#f59e0b';
$headerImg = $party['header_image'] ?? '';
if (!preg_match('/^#?[0-9a-fA-F]{3,6}$/', $accent)) {
    $accent = '#f59e0b';
}
if ($accent[0] !== '#') {
    $accent = '#' . $accent;
}
$accent = $party['theme_accent'] ?: '#f59e0b';
$headerImg = $party['header_image'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $attending = (int) ($_POST['attending'] ?? 1);
    $guestsInput = (int) ($_POST['guests'] ?? 1);
    $foodPref = trim($_POST['food_pref'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = __('Please add your name.');
    }
    if ($guestsInput < 1) {
        $errors[] = __('Guest count must be at least 1.');
    }
    if ($guestsInput > $maxGuests) {
        $errors[] = __('This party allows up to %d people per submission.', $maxGuests);
    }

    if (!$errors) {
        $dupStmt = db_prepare('SELECT COUNT(*) FROM submissions WHERE party_id = ? AND name = ? AND COALESCE(email, "") = COALESCE(?, "")');
        db_execute($dupStmt, [$party['id'], $name, $email]);
        $exists = (int) db_fetch_column($dupStmt);
        if ($exists > 0) {
            $errors[] = __('It looks like you already responded with this name and email.');
        }
    }

    if (!$errors) {
        $insert = db_prepare('INSERT INTO submissions (party_id, name, email, attending, guests, food_pref, message) VALUES (?, ?, ?, ?, ?, ?, ?)');
        db_execute($insert, [
            $party['id'],
            $name,
            $email ?: null,
            $attending === 1 ? 1 : 0,
            $guestsInput,
            $foodPref ?: null,
            $message ?: null,
        ]);
        header('Location: thankyou.php?code=' . urlencode($code));
        exit;
    }
}

render_header(__('Submit your response') . ' — ' . $party['title']);
echo '<style>:root{--accent:' . h($accent) . ';}</style>';
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div class="muted"><?= h(__('You are responding to')) ?> <?= h($party['title']) ?></div>
    <?= lang_switcher() ?>
</header>
<?php if ($headerImg): ?>
    <div class="hero">
        <img src="<?= h($headerImg) ?>" alt="<?= h($party['title']) ?>">
        <div class="hero-text">
            <strong><?= h($party['title']) ?></strong>
        </div>
    </div>
<?php endif; ?>
<div class="container" style="max-width: 720px;">
    <div class="card">
        <h2 style="margin-top:0;"><?= h($party['title']) ?></h2>
        <div class="muted"><?= h($party['event_date'] ?: 'Date TBD') ?> | <?= h($party['event_time'] ?: 'Time TBD') ?></div>
        <?php if ($party['location']): ?>
            <div style="margin-top:6px;"><?= h($party['location']) ?></div>
        <?php endif; ?>
        <?php if ($party['description']): ?>
            <p style="margin-top:10px;"><?= nl2br(h($party['description'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Submit your response')) ?></h3>
        <?php if ($errors): ?>
            <div class="danger-block"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="code" value="<?= h($code) ?>">

            <label for="name"><?= h(__('Name')) ?> *</label>
            <input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>">

            <label for="email"><?= h(__('Email')) ?></label>
            <input type="email" id="email" name="email" value="<?= h($_POST['email'] ?? '') ?>">

            <label><?= h(__('Will you attend?')) ?></label>
            <div style="display:flex; gap:10px; margin-bottom:12px;">
                <label><input type="radio" name="attending" value="1" <?= (!isset($_POST['attending']) || $_POST['attending'] == '1') ? 'checked' : '' ?>> <?= h(__('Yes')) ?></label>
                <label><input type="radio" name="attending" value="0" <?= (isset($_POST['attending']) && $_POST['attending'] == '0') ? 'checked' : '' ?>> <?= h(__('No')) ?></label>
            </div>

            <label for="guests"><?= h(__('How many people (including you)?')) ?> (max <?= h((string)$maxGuests) ?>)</label>
            <input type="number" id="guests" name="guests" min="1" max="<?= h((string)$maxGuests) ?>" value="<?= h(isset($_POST['guests']) ? min($maxGuests, max(1, (int)$_POST['guests'])) : '1') ?>">

            <label for="food_pref"><?= h(__('Food preferences / allergies')) ?></label>
            <textarea id="food_pref" name="food_pref" placeholder="e.g., vegetarian, no nuts"><?= h($_POST['food_pref'] ?? '') ?></textarea>

            <label for="message"><?= h(__('Message to host')) ?></label>
            <textarea id="message" name="message" placeholder="<?= h(__('Add anything else')) ?>"><?= h($_POST['message'] ?? '') ?></textarea>

            <button class="btn" type="submit"><?= h(__('Send response')) ?></button>
        </form>
    </div>
</div>
<?php render_footer(); ?>
