#!/bin/bash

# Run from cron with: /bin/bash /absolute/project/path/scripts/run-scheduler.sh
# Optional first argument: absolute path to the PHP CLI binary.
project_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)" || exit 1
cd -- "$project_dir" || exit 1

export TZ=Asia/Yangon
php_bin="${1:-/usr/bin/php}"
mkdir -p storage/logs || exit 1
log_file="storage/logs/scheduler-$(date +%F).log"

{
    printf '[%s] Scheduler started (pid=%s)\n' "$(date '+%F %T %Z')" "$$"
    "$php_bin" artisan schedule:run --no-ansi --no-interaction
    scheduler_status=$?
    printf '[%s] Scheduler finished (pid=%s, exit=%s)\n\n' "$(date '+%F %T %Z')" "$$" "$scheduler_status"
    exit "$scheduler_status"
} >> "$log_file" 2>&1
