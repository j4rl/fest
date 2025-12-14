<?php
declare(strict_types=1);

require_once __DIR__ . '/i18n.php';

function render_header(string $title = 'Fest Planner'): void
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    $escaped = htmlspecialchars(__($title), ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$escaped}</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
HTML;
}

function render_footer(): void
{
    echo "\n</body>\n</html>";
}
