<?php
declare(strict_types=1);

// Lightweight i18n helper with session-based language selection.
// Usage: __('Text') returns a translated string if available; otherwise the original.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function translations(): array
{
    return [
        'sv' => [
            'Fest Planner' => 'Festplanerare',
            'Log in to manage parties' => 'Logga in för att hantera evenemang',
            'Welcome back' => 'Välkommen tillbaka',
            'Use your account to create parties and collect responses.' => 'Använd ditt konto för att skapa evenemang och samla svar.',
            'Please enter both username and password.' => 'Ange både användarnamn och lösenord.',
            'Invalid credentials.' => 'Ogiltiga inloggningsuppgifter.',
            'Username' => 'Användarnamn',
            'Password' => 'Lösenord',
            'Log in' => 'Logga in',
            'Default admin login:' => 'Standardinloggning för admin:',
            'Change it in the database after first login.' => 'Byt lösenord i databasen efter första inloggningen.',
            'Signed in as' => 'Inloggad som',
            'Log out' => 'Logga ut',
            'Your parties' => 'Dina evenemang',
            'Create events and track submissions.' => 'Skapa evenemang och följ svar.',
            'No parties yet. Create one to start collecting RSVPs.' => 'Inga evenemang ännu. Skapa ett för att börja ta emot svar.',
            '+ New party' => '+ Nytt evenemang',
            'Open' => 'Öppna',
            'Details & QR' => 'Detaljer & QR',
            'Share link' => 'Delningslänk',
            'attending' => 'kommer',
            'responses' => 'svar',
            'Back' => 'Tillbaka',
            'Party not found or you do not have access.' => 'Evenemanget hittades inte eller så saknar du behörighet.',
            'Party not found' => 'Evenemanget kunde inte hittas',
            'Party details' => 'Evenemangsdetaljer',
            'Edit party details' => 'Redigera evenemanget',
            'Edit party' => 'Redigera evenemang',
            'Party updated.' => 'Evenemanget uppdaterat.',
            'Title' => 'Titel',
            'Description' => 'Beskrivning',
            'Date' => 'Datum',
            'Time' => 'Tid',
            'Location' => 'Plats',
            'Address or venue' => 'Adress eller plats',
            'Max attendees per submission' => 'Max antal per svar',
            'Max attendees per response:' => 'Max antal per svar:',
            'Save changes' => 'Spara ändringar',
            'Create a new party' => 'Skapa nytt evenemang',
            'Create party' => 'Skapa evenemang',
            'Set the basics. You can share the link immediately after saving.' => 'Ställ in grunderna. Du kan dela länken direkt efter att du sparat.',
            'What should guests know?' => 'Vad ska gästerna veta?',
            'Submit your response' => 'Skicka ditt svar',
            'Name' => 'Namn',
            'Email' => 'E-post',
            'Will you attend?' => 'Kommer du?',
            'Yes' => 'Ja',
            'No' => 'Nej',
            'How many people (including you)?' => 'Hur många (inklusive dig)?',
            'Food preferences / allergies' => 'Matpreferenser / allergier',
            'Message to host' => 'Meddelande till värden',
            'Send response' => 'Skicka svar',
            'Thank you!' => 'Tack!',
            'Your response has been recorded.' => 'Ditt svar har registrerats.',
            'No responses yet.' => 'Inga svar ännu.',
            'Food preferences only' => 'Endast matpreferenser',
            'People' => 'Personer',
            'Responses' => 'Svar',
            'Open (edit)' => 'Öppna (redigera)',
            'Dashboard' => 'Översikt',
            'Signed in' => 'Inloggad',
            'Title is required.' => 'Titel krävs.',
            'Thanks! Your response has been recorded.' => 'Tack! Ditt svar har registrerats.',
            'This party allows up to %d people per submission.' => 'Det här evenemanget tillåter upp till %d personer per svar.',
            'It looks like you already responded with this name and email.' => 'Det verkar som att du redan svarat med detta namn och denna e-post.',
            'Send this link to guests or print the QR code.' => 'Skicka länken till gäster eller skriv ut QR-koden.',
            'Attending' => 'Kommer',
            'Not attending' => 'Kommer inte',
            'guest(s)' => 'gäst(er)',
            'No one has replied yet.' => 'Ingen har svarat ännu.',
            'Food preference' => 'Matpreferens',
            'Submitted' => 'Skickat',
            'None' => 'Ingen',
            'No food preferences submitted yet.' => 'Inga matpreferenser har skickats ännu.',
            'Missing link' => 'Saknar länk',
            'Missing or invalid invite link.' => 'Inbjudningslänk saknas eller är ogiltig.',
            'Not found' => 'Hittades inte',
            'This invite link is no longer valid.' => 'Den här inbjudningslänken är inte längre giltig.',
            'Please add your name.' => 'Ange ditt namn.',
            'Guest count must be at least 1.' => 'Antalet gäster måste vara minst 1.',
            'You are responding to' => 'Du svarar på',
            'Add anything else' => 'Lägg till annat',
            'RSVP received' => 'Svar mottaget',
            'to' => 'till',
            'If you need to make changes, contact the host directly.' => 'Kontakta värden direkt om du behöver ändra något.',
        ],
    ];
}

function current_lang(): string
{
    $available = ['en', 'sv'];
    if (isset($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'en';
}

function __(string $text, mixed ...$params): string
{
    $lang = current_lang();
    $map = translations();
    $translated = $map[$lang][$text] ?? $text;
    if ($params) {
        $translated = vsprintf($translated, $params);
    }
    return $translated;
}

function _e(string $text, mixed ...$params): void
{
    echo __($text, ...$params);
}

function lang_switcher(): string
{
    $available = ['en' => 'EN', 'sv' => 'SV'];
    $current = current_lang();
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed = parse_url($path);
    parse_str($parsed['query'] ?? '', $params);

    $parts = [];
    foreach ($available as $code => $label) {
        $params['lang'] = $code;
        $query = http_build_query($params);
        $url = ($parsed['path'] ?? '/') . ($query ? '?' . $query : '');
        if ($code === $current) {
            $parts[] = '<strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>';
        } else {
            $parts[] = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="muted">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        }
    }
    return '<div class="muted">Language: ' . implode(' | ', $parts) . '</div>';
}
