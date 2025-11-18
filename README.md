# Project L: Magic Link & Password Reset Demo

A small PHP 8 web application that demonstrates passwordless authentication (magic links) and classic password resets with a Mailpit-backed email workflow. The project uses SQLite for persistence, Composer autoloading, and locally served Bootstrap assets.

## Features
- Email-based magic link login flow with CSRF protection
- Traditional signup form and session-backed dashboard
- Password reset request and token validation
- Mailpit integration for local email testing with a mail() fallback
- Lightweight SQLite database with simple migration scripts

## Prerequisites
- PHP 8.1 or newer with the SQLite extension enabled
- Composer 2
- Optional: Docker and Docker Compose v2 (for containerized setup)

## Getting Started (Local Host)
1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Create the database and tables:
   ```bash
   php database/migrate.php
   php database/migrate_magic_links.php
   php database/migrate_password_resets.php
   ```
3. Copy `.env.example` to `.env` if you keep one, or export the required variables manually:
   ```bash
   cp .env.example .env # if available
   ```
   Required variables:
   ```dotenv
   APP_URL=http://localhost:8000
   EMAIL_FROM=no-reply@example.test
   MAILPIT_HTTP_URL=http://localhost:8025/api/v1/send
   ```
   If `MAILPIT_HTTP_URL` is omitted the app will fall back to PHP's `mail()`.
4. Run the PHP development server from the project root:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
5. Browse to <http://localhost:8000/> to use the app.

## Docker Compose Workflow
1. Ensure Docker and Docker Compose v2 are installed.
2. Install dependencies inside the container (first-time only):
   ```bash
   docker compose run --rm composer install
   ```
3. Start the application stack:
   ```bash
   docker compose up --build
   ```
   Exposed ports:
   - `8080`: PHP development server (`APP_URL=http://localhost:8080`)
   - `1025`: Mailpit SMTP capture
   - `8025`: Mailpit web UI (<http://localhost:8025/>)
4. Run database migrations (inside the app container):
   ```bash
   docker compose exec app php database/migrate.php
   docker compose exec app php database/migrate_magic_links.php
   docker compose exec app php database/migrate_password_resets.php
   ```

## Testing
Run the PHPUnit suite:
```bash
./vendor/bin/phpunit
```
Or, via Docker:
```bash
docker compose exec app ./vendor/bin/phpunit
```

## Useful Notes
- Bootstrap CSS/JS and imagery are served from `src/assets`; no CDN access is required.
- Mailpit is optional but recommended for local email testing. The reset and magic link emails appear instantly in the Mailpit UI.
- Password reset tokens expire in 30 minutes and are single-use. Magic links expire in 15 minutes.

## Troubleshooting
- **`no such table` errors:** make sure you ran all migration scripts or deleted the `database/app.db` file before re-running them.
- **Emails not appearing:** confirm `MAILPIT_HTTP_URL` is set and the Mailpit container is running. Without Mailpit the app sends through `mail()`.
- **Missing extensions:** check `php -m` for `pdo_sqlite`. Install it if absent (`sudo apt install php8.2-sqlite3`, etc.).
