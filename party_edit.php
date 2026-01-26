<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isAdmin = (int) ($user['is_admin'] ?? 0) === 1;

$partySql = 'SELECT * FROM parties WHERE id = ?';
$partyParams = [$id];
if (!$isAdmin) {
    $partySql .= ' AND user_id = ?';
    $partyParams[] = $user['id'];
}
$partyStmt = db_prepare($partySql);
db_execute($partyStmt, $partyParams);
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
$applyDeadline = $party['apply_deadline'] ?? '';
$isActive = (int) ($party['is_active'] ?? 1);
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
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete_party') {
        if ($isAdmin) {
            $delete = db_prepare('DELETE FROM parties WHERE id = ?');
            db_execute($delete, [$party['id']]);
        } else {
            $delete = db_prepare('DELETE FROM parties WHERE id = ? AND user_id = ?');
            db_execute($delete, [$party['id'], $user['id']]);
        }
        header('Location: dashboard.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $nextStatus = (int) ($_POST['is_active'] ?? 1);
        if ($isAdmin) {
            $updateStatus = db_prepare('UPDATE parties SET is_active = ? WHERE id = ?');
            db_execute($updateStatus, [$nextStatus, $party['id']]);
        } else {
            $updateStatus = db_prepare('UPDATE parties SET is_active = ? WHERE id = ? AND user_id = ?');
            db_execute($updateStatus, [$nextStatus, $party['id'], $user['id']]);
        }
        $notice = $nextStatus === 1 ? __('Responses enabled.') : __('Responses disabled.');
    }

    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $applyDeadlineInput = trim($_POST['apply_deadline'] ?? '');
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
            if ($isAdmin) {
                $update = db_prepare('UPDATE parties SET title = ?, description = ?, event_date = ?, event_time = ?, apply_deadline = ?, location = ?, max_guests = ?, theme_accent = ?, header_image = ? WHERE id = ?');
                db_execute($update, [
                    $title,
                    $description,
                    $eventDate ?: null,
                    $eventTime ?: null,
                    $applyDeadlineInput ?: null,
                    $location ?: null,
                    $maxGuestsInput,
                    $accentInput ?: null,
                    ($uploadedHeader ?: $headerImgInput) ?: null,
                    $party['id'],
                ]);
            } else {
                $update = db_prepare('UPDATE parties SET title = ?, description = ?, event_date = ?, event_time = ?, apply_deadline = ?, location = ?, max_guests = ?, theme_accent = ?, header_image = ? WHERE id = ? AND user_id = ?');
                db_execute($update, [
                    $title,
                    $description,
                    $eventDate ?: null,
                    $eventTime ?: null,
                    $applyDeadlineInput ?: null,
                    $location ?: null,
                    $maxGuestsInput,
                    $accentInput ?: null,
                    ($uploadedHeader ?: $headerImgInput) ?: null,
                    $party['id'],
                    $user['id'],
                ]);
            }
            $notice = __('Party updated.');
        }
    }

    if (!$errors) {
        // Refresh data
        db_execute($partyStmt, $partyParams);
        $party = db_fetch_one($partyStmt);
        $maxGuests = max(1, (int) ($party['max_guests'] ?? 1));
        $accent = $party['theme_accent'] ?: '#f59e0b';
        $headerImg = $party['header_image'] ?? '';
        $applyDeadline = $party['apply_deadline'] ?? '';
        $isActive = (int) ($party['is_active'] ?? 1);
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
                <?php if ($applyDeadline): ?>
                    <div class="muted" style="margin-top:6px;"><?= h(__('Last date to apply')) ?>: <?= h($applyDeadline) ?></div>
                <?php endif; ?>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Max attendees per response:')) ?> <?= h((string)$maxGuests) ?></div>
                <div class="muted" style="margin-top:6px;"> <?= h(__('Accent color')) ?>: <?= h($accent ?: '#f59e0b') ?></div>
            </div>
            <div style="text-align:right;">
                <div class="pill" style="background:#334155; color:#e5e7eb;"><?= h(__('Share link')) ?> &amp; QR</div>
            </div>
        </div>
    </div>

    <div class="card">
        <form method="post" enctype="multipart/form-data" id="party-edit-form">
            <input type="hidden" name="action" value="save">
            <div class="form-header">
                <h3 style="margin:0;"><?= h(__('Edit party details')) ?></h3>
                <button class="btn" type="submit"><?= h(__('Save changes')) ?></button>
            </div>
            <?php if ($notice): ?>
                <div class="notice"><?= h($notice) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="danger-block"><?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>
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
                <div>
                    <label for="apply_deadline"><?= h(__('Last date to apply')) ?></label>
                    <input type="date" id="apply_deadline" name="apply_deadline" value="<?= h($_POST['apply_deadline'] ?? $applyDeadline) ?>">
                </div>
            </div>

            <label for="location"><?= h(__('Location')) ?></label>
            <input type="text" id="location" name="location" placeholder="<?= h(__('Address or venue')) ?>" value="<?= h($_POST['location'] ?? $party['location']) ?>">

            <label for="max_guests"><?= h(__('Max attendees per submission')) ?></label>
            <input type="number" id="max_guests" name="max_guests" min="1" value="<?= h($_POST['max_guests'] ?? $party['max_guests']) ?>">

            <label for="theme_accent"><?= h(__('Accent color')) ?></label>
            <div class="color-field">
                <input type="color" id="theme_accent" name="theme_accent" value="<?= h($_POST['theme_accent'] ?? $accent) ?>">
                <input type="text" id="theme_accent_text" value="<?= h($_POST['theme_accent'] ?? $accent) ?>" placeholder="#f59e0b" maxlength="7" spellcheck="false" inputmode="text" aria-label="Accent color hex">
            </div>
            <div class="field-help"><?= h(__('Used for buttons and highlights.')) ?></div>

            <label for="header_image"><?= h(__('Header image URL (optional)')) ?></label>
            <input type="text" id="header_image" name="header_image" placeholder="https://example.com/banner.jpg" value="<?= h($_POST['header_image'] ?? $headerImg) ?>">
            <div class="field-help"><?= h(__('Paste a URL or upload a file below.')) ?></div>

            <label for="header_upload"><?= h(__('Upload header image')) ?></label>
            <input type="file" id="header_upload" name="header_upload" accept="image/*">
            <div class="field-help"><?= h(__('Uploads are stored on this server and override the URL.')) ?></div>

            <div class="form-actions">
                <button class="btn" type="submit"><?= h(__('Save changes')) ?></button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><?= h(__('Responses')) ?></h3>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
            <?php if ($isActive === 1): ?>
                <div class="pill success"><?= h(__('Accepting responses')) ?></div>
            <?php else: ?>
                <div class="pill danger"><?= h(__('Responses disabled')) ?></div>
            <?php endif; ?>
            <?php if ($applyDeadline): ?>
                <div class="muted"><?= h(__('Last date to apply')) ?>: <?= h($applyDeadline) ?></div>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <form method="post">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="is_active" value="<?= $isActive === 1 ? 0 : 1 ?>">
                <button class="btn secondary" type="submit">
                    <?= $isActive === 1 ? h(__('Disable responses')) : h(__('Enable responses')) ?>
                </button>
            </form>
            <form method="post" onsubmit="return confirm('Delete this party? This will remove all submissions.');">
                <input type="hidden" name="action" value="delete_party">
                <button class="btn secondary" type="submit" style="border-color: rgba(239, 68, 68, 0.6); color:#fecdd3;">
                    <?= h(__('Delete party')) ?>
                </button>
            </form>
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
</div>
<script>
(() => {
    const colorInput = document.getElementById('theme_accent');
    const textInput = document.getElementById('theme_accent_text');
    if (!colorInput || !textInput) {
        return;
    }

    const normalize = (value) => {
        const trimmed = value.trim();
        if (!/^#?[0-9a-fA-F]{3,6}$/.test(trimmed)) {
            return null;
        }
        return trimmed[0] === '#' ? trimmed : `#${trimmed}`;
    };

    const syncFromColor = () => {
        textInput.value = colorInput.value;
    };

    const syncFromText = () => {
        const normalized = normalize(textInput.value);
        if (normalized) {
            colorInput.value = normalized;
        }
    };

    colorInput.addEventListener('input', syncFromColor);
    textInput.addEventListener('input', syncFromText);
    syncFromColor();
})();
</script>
<?php render_footer(); ?>
