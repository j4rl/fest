# Fest Planner

Simple PHP + MySQL (MySQLi) app to manage parties, collect RSVPs, and track food preferences.

## Features
- Login for existing users (seeded admin: `admin` / `admin123`)
- Create parties with title, description, date/time, and location
- Shareable invite link and QR code for each party
- Guests submit attendance (yes/no), guest count, food preferences, and a note
- Host view shows total responses, attending count, and a food-preference-only list
- Admin sets the max attendees allowed per submission; guest form enforces it
- Duplicate submissions blocked by matching name + email; submissions land on a thank-you page
- Built-in i18n (English/Swedish) with a language switcher on each page
- Hosts can set an accent color and header image (URL or upload) per invitation

## Getting started (XAMPP/MySQL)
1. Place the files in your web root (e.g., `htdocs/fest` for XAMPP).
2. Ensure MySQL is running and PHP has `mysqli`, `mysqlnd`, and `fileinfo` enabled (default in XAMPP).
3. Configure DB credentials via env vars if needed: `DB_HOST` (default `127.0.0.1`), `DB_PORT` (default `3306`), `DB_NAME` (default `fest`), `DB_USER` (default `root`), `DB_PASS` (default empty).
4. Make sure `uploads/` is writable by PHP (for header image uploads).
5. Load the site in your browser; the app will create the database/tables if missing and seed `admin/admin123`.
6. If you already had an older database, run the migrations to add new columns:  
   `mysql -u <user> -p <database> < migrations/001_add_max_guests.sql`  
   `mysql -u <user> -p <database> < migrations/002_add_theme_fields.sql`
7. Log in, create a party, choose an accent color/header image, and share the generated invite link or QR. Switch language with the EN/SV toggle in the header.

> Change the default admin password after first login by updating the `users` table in your MySQL database.
> QR codes are generated via `https://api.qrserver.com`, so outbound access is required to display them.
