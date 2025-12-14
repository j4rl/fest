<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$code = trim($_GET['code'] ?? '');
$partyTitle = null;

if ($code !== '') {
    $stmt = db_prepare('SELECT title FROM parties WHERE share_code = ?');
    db_execute($stmt, [$code]);
    $row = db_fetch_one($stmt);
    if ($row) {
        $partyTitle = $row['title'];
    }
}

render_header(__('Thank you!'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div class="muted"><?= h(__('RSVP received')) ?></div>
    <?= lang_switcher() ?>
</header>
<div class="container" style="max-width:640px;">
    <div class="card">
        <h2 style="margin-top:0;"><?= h(__('Thank you!')) ?></h2>
        <p><?= h(__('Your response has been recorded.')) ?><?php if ($partyTitle): ?> <?= h(__('to')) ?> <?= h($partyTitle) ?><?php endif; ?>.</p>
        <p class="muted"><?= h(__('If you need to make changes, contact the host directly.')) ?></p>
    </div>
</div>
<?php render_footer(); ?>
