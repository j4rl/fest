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
            '+ New party' => '+ Nytt evenemang',
            'Accepting responses' => 'Tar emot svar',
            'Accent color' => 'Accentfärg',
            'Actions' => 'Åtgärder',
            'Active users' => 'Aktiva användare',
            'Add anything else' => 'Lägg till annat',
            'Address or venue' => 'Adress eller plats',
            'Admins' => 'Administratörer',
            'An admin must approve your account before you can log in.' => 'En administratör måste godkänna ditt konto innan du kan logga in.',
            'Approve' => 'Godkänn',
            'Approve new accounts before they can manage parties.' => 'Godkänn nya konton innan de kan hantera evenemang.',
            'Attending' => 'Kommer',
            'Back' => 'Tillbaka',
            'Back to login' => 'Tillbaka till inloggning',
            'Change it in the database after first login.' => 'Byt lösenord i databasen efter första inloggningen.',
            'Confirm password' => 'Bekräfta lösenord',
            'Create a new party' => 'Skapa nytt evenemang',
            'Create events and track submissions.' => 'Skapa evenemang och följ svar.',
            'Create party' => 'Skapa evenemang',
            'Dashboard' => 'Översikt',
            'Date' => 'Datum',
            'Date TBD' => 'Datum ej bestämt',
            'Default admin login:' => 'Standardinloggning för admin:',
            'Delete' => 'Ta bort',
            'Delete party' => 'Ta bort evenemang',
            'Description' => 'Beskrivning',
            'Details & QR' => 'Detaljer & QR',
            'Disable responses' => 'Inaktivera svar',
            'Edit party' => 'Redigera evenemang',
            'Edit party details' => 'Redigera evenemanget',
            'Email' => 'E-post',
            'Enable responses' => 'Aktivera svar',
            'Fest Planner' => 'Festplanerare',
            'Food preference' => 'Matpreferens',
            'Food preferences / allergies' => 'Matpreferenser / allergier',
            'Food preferences only' => 'Endast matpreferenser',
            'Forbidden' => 'Åtkomst nekad',
            'Guest count must be at least 1.' => 'Antalet gäster måste vara minst 1.',
            'Guests' => 'Gäster',
            'Header image URL (optional)' => 'URL till headerbild (valfritt)',
            'Header image preview' => 'Förhandsvisning av headerbild',
            'How many besides you are coming?' => 'Hur många förutom dig kommer?',
            'If you need to make changes, contact the host directly.' => 'Kontakta värden direkt om du behöver ändra något.',
            'Invalid credentials.' => 'Ogiltiga inloggningsuppgifter.',
            'It looks like you already responded with this name and email.' => 'Det verkar som att du redan svarat med detta namn och denna e-post.',
            'Language' => 'Språk',
            'Last date to apply' => 'Sista svarsdatum',
            'Location' => 'Plats',
            'Log in' => 'Logga in',
            'Log in to manage parties' => 'Logga in för att hantera evenemang',
            'Log out' => 'Logga ut',
            'Max attendees per response:' => 'Max antal per svar:',
            'Max attendees per submission' => 'Max antal per svar',
            'Message' => 'Meddelande',
            'Message to host' => 'Meddelande till värden',
            'Missing link' => 'Saknar länk',
            'Missing or invalid invite link.' => 'Inbjudningslänk saknas eller är ogiltig.',
            'Name' => 'Namn',
            'Need an account?' => 'Behöver du ett konto?',
            'No' => 'Nej',
            'No active users yet.' => 'Inga aktiva användare ännu.',
            'No food preferences submitted yet.' => 'Inga matpreferenser har skickats ännu.',
            'No one has replied yet.' => 'Ingen har svarat ännu.',
            'No participants yet.' => 'Inga deltagare ännu.',
            'No parties yet. Create one to start collecting RSVPs.' => 'Inga evenemang ännu. Skapa ett för att börja ta emot svar.',
            'No pending users.' => 'Inga väntande användare.',
            'No responses yet.' => 'Inga svar ännu.',
            'None' => 'Ingen',
            'Not attending' => 'Kommer inte',
            'Not found' => 'Hittades inte',
            'Open' => 'Öppna',
            'Open (edit)' => 'Öppna (redigera)',
            'Participant list' => 'Deltagarlista',
            'Party details' => 'Evenemangsdetaljer',
            'Party not found' => 'Evenemanget kunde inte hittas',
            'Party not found or you do not have access.' => 'Evenemanget hittades inte eller så saknar du behörighet.',
            'Party updated.' => 'Evenemanget uppdaterat.',
            'Password' => 'Lösenord',
            'Password is required.' => 'Lösenord krävs.',
            'Passwords do not match.' => 'Lösenorden matchar inte.',
            'Paste a URL or upload a file below.' => 'Klistra in en URL eller ladda upp en fil nedan.',
            'Pending requests' => 'Väntande förfrågningar',
            'People' => 'Personer',
            'Please add your name.' => 'Ange ditt namn.',
            'Please enter both username and password.' => 'Ange både användarnamn och lösenord.',
            'Print participant list' => 'Skriv ut deltagarlista',
            'RSVP received' => 'Svar mottaget',
            'Request access' => 'Begär åtkomst',
            'Request access to manage parties' => 'Begär åtkomst för att hantera evenemang',
            'Requested' => 'Begärt',
            'Responses' => 'Svar',
            'Responses are closed.' => 'Svarstiden är stängd.',
            'Responses closed' => 'Svar stängda',
            'Responses closed on %s.' => 'Svar stängdes %s.',
            'Responses disabled' => 'Svar inaktiverade',
            'Responses disabled.' => 'Svar inaktiverade.',
            'Responses enabled.' => 'Svar aktiverade.',
            'Save changes' => 'Spara ändringar',
            'Send request' => 'Skicka begäran',
            'Send response' => 'Skicka svar',
            'Send this link to guests or print the QR code.' => 'Skicka länken till gäster eller skriv ut QR-koden.',
            'Set the basics. You can share the link immediately after saving.' => 'Ställ in grunderna. Du kan dela länken direkt efter att du sparat.',
            'Share link' => 'Delningslänk',
            'Signed in' => 'Inloggad',
            'Signed in as' => 'Inloggad som',
            'Submit your response' => 'Skicka ditt svar',
            'Submitted' => 'Skickat',
            'Text' => 'Text',
            'Thank you!' => 'Tack!',
            'Thanks! Your account request was sent for approval.' => 'Tack! Din kontoförfrågan skickades för godkännande.',
            'That username is already taken.' => 'Det användarnamnet är redan taget.',
            'This invite link is no longer valid.' => 'Den här inbjudningslänken är inte längre giltig.',
            'This party allows up to %d people per submission.' => 'Det här evenemanget tillåter upp till %d personer per svar.',
            'This party is not accepting responses.' => 'Det här evenemanget tar inte emot svar.',
            'Time' => 'Tid',
            'Time TBD' => 'Tid ej bestämd',
            'Title' => 'Titel',
            'Title is required.' => 'Titel krävs.',
            'Upload header image' => 'Ladda upp headerbild',
            'Uploads are stored on this server and override the URL.' => 'Uppladdade bilder lagras på denna server och ersätter URL:en.',
            'Use your account to create parties and collect responses.' => 'Använd ditt konto för att skapa evenemang och samla svar.',
            'Used for buttons and highlights.' => 'Används för knappar och markeringar.',
            'User approvals' => 'Användargodkännanden',
            'Username' => 'Användarnamn',
            'Username is required.' => 'Användarnamn krävs.',
            'Welcome back' => 'Välkommen tillbaka',
            'What should guests know?' => 'Vad ska gästerna veta?',
            'Will you attend?' => 'Kommer du?',
            'Yes' => 'Ja',
            'You are responding to' => 'Du svarar på',
            'You do not have access to this page.' => 'Du har inte åtkomst till den här sidan.',
            'Your account is pending approval.' => 'Ditt konto väntar på godkännande.',
            'Your parties' => 'Dina evenemang',
            'Your response has been recorded.' => 'Ditt svar har registrerats.',
            'attending' => 'kommer',
            'guest(s)' => 'gäst(er)',
            'responses' => 'svar',
            'to' => 'till',
        ],
    ];
}

function current_lang(): string
{
    $available = ['en', 'sv'];
    if (isset($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'sv';
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
    return '<div class="muted">' . htmlspecialchars(__('Language'), ENT_QUOTES, 'UTF-8') . ': ' . implode(' | ', $parts) . '</div>';
}
