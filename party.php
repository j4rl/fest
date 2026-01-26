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

$submissionsStmt = db_prepare('SELECT * FROM submissions WHERE party_id = ? ORDER BY created_at DESC');
db_execute($submissionsStmt, [$party['id']]);
$submissions = db_fetch_all($submissionsStmt);

$shareLink = base_url() . 'submit.php?code=' . urlencode($party['share_code']);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($shareLink);
$maxGuests = max(1, (int) ($party['max_guests'] ?? 1));
$accent = $party['theme_accent'] ?: '#f59e0b';
$headerImg = $party['header_image'] ?? '';
if (!preg_match('/^#?[0-9a-fA-F]{3,6}$/', $accent)) {
    $accent = '#f59e0b';
}
if ($accent[0] !== '#') {
    $accent = '#' . $accent;
}

render_header(__('Party details') . ' - ' . $party['title']);
echo '<style>:root{--accent:' . h($accent) . ';}</style>';
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <a class="btn secondary" href="dashboard.php"><?= h(__('Dashboard')) ?></a>
        <a class="btn secondary" href="party_edit.php?id=<?= (int)$party['id'] ?>"><?= h(__('Open (edit)')) ?></a>
        <a class="btn secondary" href="logout.php"><?= h(__('Log out')) ?></a>
        <?= lang_switcher() ?>
    </div>
</header>
<div class="container">
    <?php if ($headerImg): ?>
    <div class="hero">
        <img src="<?= h($headerImg) ?>" alt="<?= h($party['title']) ?>">
        <div class="hero-text">
            <strong><?= h($party['title']) ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;"> <?= h($party['title']) ?> </h2>
                <div class="muted" style="margin-top:4px;"> <?= h($party['event_date'] ?: __('Date TBD')) ?> | <?= h($party['event_time'] ?: __('Time TBD')) ?> </div>
                <?php if ($party['location']): ?>
                    <div style="margin-top:6px;"> <?= h($party['location']) ?> </div>
                <?php endif; ?>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Max attendees per response:')) ?> <?= h((string)$maxGuests) ?></div>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Accent color')) ?>: <?= h($accent) ?></div>
            </div>
            <div style="text-align:right;">
                <div class="pill success" style="margin-bottom:6px;"> <?= count(array_filter($submissions, fn($s) => (int)$s['attending'] === 1)) ?> <?= h(__('attending')) ?></div>
                <div class="pill" style="background:#334155; color:#e5e7eb;"> <?= count($submissions) ?> <?= h(__('responses')) ?></div>
            </div>
        </div>
        <?php if ($party['description']): ?>
            <p class="muted" style="margin-top:10px;"> <?= nl2br(h($party['description'])) ?> </p>
        <?php endif; ?>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;"><?= h(__('Share link')) ?></h3>
            <p class="muted" style="margin-top:0;"><?= h(__('Send this link to guests or print the QR code.')) ?></p>
            <code style="background:rgba(255,255,255,0.05); padding:8px 10px; border-radius:8px; display:block; margin-top:8px; user-select:all;"> <?= h($shareLink) ?> </code>
        </div>
        <div class="card">
            <h3 style="margin-top:0;">QR</h3>
            <div class="qr">
                <img src="<?= h($qrUrl) ?>" alt="QR code to submission form">
            </div>
        </div>
        <?php if ($headerImg): ?>
        <div class="card">
            <h3 style="margin-top:0;"><?= h(__('Header image preview')) ?></h3>
            <img src="<?= h($headerImg) ?>" alt="Header" style="max-width:100%; border-radius:10px; display:block;">
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('People')) ?></h3>
        <?php if (!$submissions): ?>
            <p class="muted"><?= h(__('No responses yet.')) ?></p>
        <?php else: ?>
            <ul>
                <?php foreach ($submissions as $submission): ?>
                    <li>
                        <?= h($submission['name']) ?>
                        <?php if (!empty($submission['email'])): ?>
                            <span class="muted">(<?= h($submission['email']) ?>)</span>
                        <?php endif; ?>
                        - <?= (int)$submission['attending'] === 1 ? h(__('Attending')) : h(__('Not attending')) ?>, <?= (int)$submission['guests'] ?> <?= h(__('guest(s)')) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Responses')) ?></h3>
        <?php if (!$submissions): ?>
            <p class="muted"><?= h(__('No one has replied yet.')) ?></p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th><?= h(__('Name')) ?></th>
                            <th><?= h(__('Attending')) ?></th>
                            <th><?= h(__('Guests')) ?></th>
                            <th><?= h(__('Food preference')) ?></th>
                            <th><?= h(__('Message')) ?></th>
                            <th><?= h(__('Submitted')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?= h($submission['name']) ?><br><span class="muted" style="font-size:12px;"> <?= h($submission['email']) ?> </span></td>
                                <td><?= (int)$submission['attending'] === 1 ? '<span class="pill success">' . h(__('Yes')) . '</span>' : '<span class="pill danger">' . h(__('No')) . '</span>' ?></td>
                                <td><?= (int) $submission['guests'] ?></td>
                                <td><?= $submission['food_pref'] ? h($submission['food_pref']) : '<span class="muted">' . h(__('None')) . '</span>' ?></td>
                                <?php $msg = trim((string) ($submission['message'] ?? '')); ?>
                                <td><?= $msg !== '' ? h($msg) : '' ?></td>
                                <td class="muted"><?= h($submission['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Food preferences only')) ?></h3>
        <?php
        $foodOnly = array_filter($submissions, fn($s) => trim((string)$s['food_pref']) !== '');
        if (!$foodOnly): ?>
            <p class="muted"><?= h(__('No food preferences submitted yet.')) ?></p>
        <?php else: ?>
            <ul>
                <?php foreach ($foodOnly as $submission): ?>
                    <li><?= h($submission['name']) ?> - <?= h($submission['food_pref']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php render_footer(); ?>
