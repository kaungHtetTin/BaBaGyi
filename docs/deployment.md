# Deployment to mastergyi.com

The intended production base URL is **https://www.mastergyi.com**. The client
opens at **https://www.mastergyi.com/app**, and the admin login is
**https://www.mastergyi.com/admin/login**.

## Server setup

Serve this Laravel project's `public/` directory as the domain's document root.
The public URL must not include `/public`. Keep `.env`, `vendor`, application
source, and database backups outside the document root. Enable Laravel's URL
rewriting (the supplied `public/.htaccess` on Apache, or an equivalent Nginx
front-controller configuration).

Use a PHP version and extensions compatible with `composer.lock`, Composer,
and MySQL. Check the installed PHP with `composer check-platform-reqs --no-dev`.
The web server user needs write access to `storage/` and `bootstrap/cache/`.

Point the domain's DNS at the chosen server, install a valid TLS certificate,
and configure redirects from HTTP and the apex domain `mastergyi.com` to
`https://www.mastergyi.com`, preserving paths and query strings. If TLS terminates
at a proxy, configure Laravel to trust the actual proxy addresses so it detects
HTTPS correctly and generates secure URLs and client cookies.

## Production environment

Use [.env.production.example](../.env.production.example) as the reference for
the server's `.env`. Set the real database and SMTP credentials there. Leave
the development `.env` on the local machine unchanged.

Retain the existing `APP_KEY` when updating or moving an existing installation.
Only run `php artisan key:generate --force` for a new installation that has no
existing key or encrypted data. Keep `APP_ENV=production`, `APP_DEBUG=false`,
`APP_URL=https://www.mastergyi.com`, and `SESSION_SECURE_COOKIE=true`.

## Deploying the application

Back up the existing production database, `.env`, and uploaded files before an
update. Review pending migrations before running them against the live database.
Preserve existing `storage/app/public` uploads and any uploads stored directly
under `public/`. Do not replace or reseed the production database.

Build the Vite assets locally or in a build environment:

```bash
npm ci
npm run build
```

Upload the application, `composer.lock`, the resulting `public/build/`, and all
of `public/client/`. Exclude the local `.env`, `node_modules/`, and `public/hot`.
The client assets in `public/client/` are served directly; a running Node process
is not required in production.

From the project directory on the server, after configuring its `.env`:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan optimize:clear
php artisan migrate --force
```

Create the public storage link with `php artisan storage:link` if it is missing.
Then cache the configuration and compiled views:

```bash
php artisan config:cache
php artisan view:cache
```

The current routes include closures; route caching is not part of this procedure.

## Scheduler

Install one cron entry for this project, using its actual absolute path and PHP
binary:

```cron
* * * * * cd /absolute/path/to/mmt2d && php artisan schedule:run >> /dev/null 2>&1
```

Check `php artisan schedule:list`. The application uses `Asia/Yangon` for its
scheduled lottery tasks. Remove any duplicate scheduler for the same installation.

## Verification after deployment

- Confirm `/`, `/app`, and `/admin/login` load over HTTPS.
- Confirm `/app/manifest.webmanifest`, `/app/sw.js`, and client assets load from
  `www.mastergyi.com` without an old host or `/public` in their URLs.
- Use a test account to check login, session persistence, and logout.
- Check existing uploaded media and read-only results/history pages.
- Check `storage/logs/laravel.log` for deployment errors and confirm the scheduler
  runs under the correct PHP binary.

Server access and hosting details are still needed to perform and verify the
actual deployment.
