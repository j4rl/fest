<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();

$isAdmin = (int) ($user['is_admin'] ?? 0) === 1;
$sql = 'SELECT p.*,
        COUNT(s.id) AS submission_count,
        SUM(CASE WHEN s.attending = 1 THEN 1 ELSE 0 END) AS attending_count
     FROM parties p
     LEFT JOIN submissions s ON s.party_id = p.id';
$params = [];
if (!$isAdmin) {
    $sql .= ' WHERE p.user_id = ?';
    $params[] = $user['id'];
}
$sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';
$stmt = db_prepare($sql);
db_execute($stmt, $params);
$parties = db_fetch_all($stmt);

$pendingCount = 0;
if ($isAdmin) {
    $pendingStmt = db_prepare('SELECT COUNT(*) FROM users WHERE is_approved = 0 AND is_admin = 0');
    db_execute($pendingStmt);
    $pendingCount = (int) db_fetch_column($pendingStmt);
}

render_header(__('Your parties'));
?>
<header>
    <h1><?= h(__('Fest Planner')) ?></h1>
    <div style="display:flex; align-items:center; gap:10px;">
        <div class="muted"><?= h(__('Signed in as')) ?> <?= h($user['username']) ?></div>
        <?= lang_switcher() ?>
        <?php if ($isAdmin): ?>
            <a class="btn secondary" href="admin_users.php">
                <?= h(__('User approvals')) ?>
                <?php if ($pendingCount > 0): ?>
                    <span class="pill" style="background:#f59e0b; color:#0f172a;"><?= (int) $pendingCount ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <a class="btn secondary" href="logout.php"><?= h(__('Log out')) ?></a>
    </div>
</header>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;"> <?= h(__('Your parties')) ?> </h2>
            <p class="muted" style="margin:4px 0 0;"> <?= h(__('Create events and track submissions.')) ?> </p>
        </div>
        <a class="btn" href="party_new.php"> <?= h(__('+ New party')) ?> </a>
    </div>

    <?php if (empty($parties)): ?>
        <div class="card">
            <p class="muted"><?= h(__('No parties yet. Create one to start collecting RSVPs.')) ?></p>
        </div>
    <?php else: ?>
        <div class="grid" style="margin-top:14px;">
            <?php foreach ($parties as $party): 
                $shareLink = base_url() . 'submit.php?code=' . urlencode($party['share_code']);
                $attending = $party['attending_count'] ?? 0;
                $submissions = $party['submission_count'] ?? 0;
                ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                        <div>
                            <h3 style="margin:0; font-size:17px;"> <?= h($party['title']) ?> </h3>
                            <div class="muted" style="font-size:13px;">
                                <?= h($party['event_date'] ?: __('Date TBD')) ?> | <?= h($party['event_time'] ?: __('Time TBD')) ?>
                            </div>
                        </div>
                        <a class="btn secondary" href="party_edit.php?id=<?= (int)$party['id'] ?>"><?= h(__('Open')) ?></a>
                    </div>
                    <div style="display:flex; gap:12px; margin:12px 0;">
                        <div class="pill success"><?= (int)$attending ?> <?= h(__('attending')) ?></div>
                        <div class="pill" style="background:#334155; color:#e5e7eb;"><?= (int)$submissions ?> <?= h(__('responses')) ?></div>
                    </div>
                    <div class="muted" style="font-size:13px; margin-bottom:8px;"> <?= h(__('Share link')) ?> </div>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <code style="background:rgba(255,255,255,0.05); padding:6px 8px; border-radius:8px; font-size:12px;"> <?= h($shareLink) ?> </code>
                        <a class="btn secondary" href="party.php?id=<?= (int)$party['id'] ?>"><?= h(__('Details & QR')) ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
