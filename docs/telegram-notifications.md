# Telegram admin notifications

Open **Admin → Settings → Telegram Alerts**. Create a bot with Telegram’s @BotFather, press Start in that bot from the receiving admin account, and enter the bot token and that account’s numeric Telegram ID. Save, send a test message, then enable the desired alerts. All bot configuration is managed in this page; no Telegram environment variables are needed.

The token is encrypted using Laravel’s APP_KEY. Keep that key when moving the database to another server. Blank token submissions retain the existing token. A positive numeric chat ID restricts delivery to a private account, rather than a group or channel. Only existing admin accounts can access configuration and delivery history.

New successful top-up and withdrawal submissions create an outbox entry in the same database transaction. The first delivery is attempted when the response terminates. Retries use the existing Laravel scheduler, with delays of 1, 2, 4 and 8 minutes, stopping after five total attempts. The admin can retry unsent entries. Changing the recipient holds entries addressed to the original chat. Disabling notifications pauses pending delivery and stops recording new alerts.

On deployment run `php artisan migrate --force`. Ensure the existing scheduler runs `php artisan schedule:run` every minute (Task Scheduler on Windows or cron on Linux). No separate queue worker is needed. `php artisan telegram:send-pending` can also process due alerts manually. The server needs outbound HTTPS access to api.telegram.org.

Messages are plain text with Burmese labels, request type, user name, registered phone, amount, payment provider, request ID, and Myanmar submission time. Top-ups include the submitted transaction reference, padded to six digits to preserve leading zeros in the existing integer column. Withdrawals include the recipient account name and number. Only new submissions are notified; approval remains in the admin dashboard.

Delivery locks and the unique request key prevent ordinary concurrent/repeated sends. Telegram has no idempotency key for sendMessage: if it accepts a message but its response is lost, a retry can produce a duplicate. Request references make these identifiable. Delivery errors shown in the panel exclude raw HTTP exceptions and bot tokens.
