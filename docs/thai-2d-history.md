# Automatic Thai 2D history

The Laravel scheduler records Thai 2D history at **09:30** and **14:00**, Monday
through Friday, in `Asia/Yangon` (Myanmar time). These snapshots use the existing
`https://api.thaistock2d.com/live` endpoint's `live.twod` value.

Records go into `lotteries` with `lottery_type_id = 2` and the matching existing
clock (09:30 AM or 02:00 PM). They appear in the existing admin 2D calendar and
2D records API. No migration or additional betting-draw configuration is needed.
Recording runs independently of automatic/manual release mode.

These commands only insert history. They do not change vouchers, balances,
number demand, disabled flags, hot numbers, or the 12:01/16:30 release commands.
One record is kept per Myanmar date and clock; repeated captures preserve the
first saved number. A transaction locks the lottery-type row to serialize
concurrent captures.

## Admin history corrections

Admins can use the pencil beside a calendar date or the pencil in the existing
dashboard **Results** sections to correct MM 2D or MM 3D history in a
modal. The modal loads the selected day's records, shows field validation
errors without closing, and supports Cancel and Escape. Saving refreshes the
same page to update the results and totals while keeping the selected date/month.
The dashboard's existing 2D Results table displays saved records for the day;
its 3D table displays the month's 1st and 16th results, with a pencil for each
completed draw. The live number remains separate. Result refreshes use saved
history so manual corrections remain visible. Other dates can be edited in the calendar.
Save returns to the selected calendar month or dashboard date and report year.

The editor updates the latest saved row for each date/clock or creates a missing
history row at the draw time. Enter exactly two or three digits, including any
leading zeroes. Blank fields keep existing results; future draw times and
unconfigured clocks cannot be filled. Each correction logs the admin, date,
draw time, and old/new numbers. Only `lotteries` rows are written: this does not
release results, recalculate voucher wins, pay vouchers, or reset numbers.
The history editor does not invoke the 12:01 or 16:30 saving and settlement commands.

## Official release results

The 12:01 automatic release uses the live endpoint's `result` entry with
`open_time = 12:01:00`; the 16:30 release uses `open_time = 16:30:00`.
Both require today's Myanmar `stock_date`, a two-digit string in `twod`, and a
nonempty `history_id` indicating a completed result. They select by time/date,
independent of array ordering, and never fall back to `live.twod`.
The scheduler checks every minute from **12:01 through 12:16** and **16:30 through
16:45**, inclusive, in `Asia/Yangon`. Each tick makes one API request until the
draw is saved. Missing, unfinished, stale, malformed, or failed API responses
leave vouchers, number demand, and hot numbers untouched. Pending attempts log
at info level; a failed final attempt logs a warning requiring admin review.
There are no automatic attempts after the retry window. Cron must continue to
run `php artisan schedule:run` every minute; no queue worker is required.

Result saving, voucher win-status updates, and number resets run in one database
transaction, locking the lottery-type row and checking again for an existing
result for that Myanmar date/clock. Retries and concurrent releases preserve an
existing result and do not repeat settlement. This also preserves legacy and
admin-entered history: a saved history row is **not** automatically settled or
overwritten. If an admin entered history before release, voucher settlement
requires separate admin review. Failed database work rolls back so the next
attempt can retry. Saved result timestamps use the draw time, while logs contain
the actual recording time. Balances and betting cutoffs retain their existing
behavior. The 09:30 and 14:00 captures continue to use API server time.

For an unresolved draw, after checking the API and confirming there is no saved
result for that draw, an operator can retry manually later on the **same Myanmar
date** with `php artisan command:runat1201pm` or `php artisan command:runat430pm`.
The scheduler passes `--scheduled` to enforce its retry window; manual commands
without that flag allow same-day recovery. Manual release mode still prevents
automatic and command-line release.

The source must return the exact target `server_time`, exactly two digits,
today's date, and a live timestamp no more than five minutes behind the API
clock and never ahead of it. Leading zeroes are kept.
If the market/API has no fresh value at either time, no record is fabricated:
the command exits unsuccessfully and logs the reason in the Laravel log.
There is no later backfill with a different minute's live value.

## Client records and calendar API

`GET /api/lotteries/records?type=2&year=2026&month=11` keeps the existing flat
array and its original fields (`id`, `lottery_type_id`, `clock_id`, `number`,
`created_at`, `updated_at`, `day`, `d`). Every row now also includes:

```json
{
  "date": "2026-11-09",
  "draw_time": "09:30",
  "draw_label": "09:30 AM",
  "record_only": true,
  "clock": { "id": 1, "hour": 9, "minute": 30, "second": 0, "morning": 1 }
}
```

The clock ID above is illustrative; clients identify the slot by `draw_time`.
Numbers remain strings, including `"00"` and `"07"`. Rows are ordered by Myanmar
date and draw time. The `type` parameter still supports other lottery types.
Thai 2D weekend rows remain excluded, as in the original endpoint.

`GET /api/lotteries/calendar?type=2&year=2026&month=11` returns:

```json
{
  "year": 2026,
  "month": 11,
  "type": 2,
  "timezone": "Asia/Yangon",
  "draws": [
    { "clock_id": 1, "draw_time": "09:30", "draw_label": "09:30 AM", "record_only": true },
    { "clock_id": 2, "draw_time": "12:01", "draw_label": "12:01 PM", "record_only": false },
    { "clock_id": 3, "draw_time": "14:00", "draw_label": "02:00 PM", "record_only": true },
    { "clock_id": 4, "draw_time": "16:30", "draw_label": "04:30 PM", "record_only": false }
  ],
  "days": [
    {
      "date": "2026-11-02",
      "day": 2,
      "d": "Mon",
      "results": { "09:30": null, "12:01": null, "14:00": null, "16:30": null }
    }
  ]
}
```

The example abbreviates `days`; the response includes **every weekday in the
selected month**, including days with no saved results. A saved slot contains
the complete enriched records row instead of `null`. Clients render `null` as
`--`; they must not carry forward another date's result. If historical release
rows contain duplicates, the calendar uses the most recent entry for that slot.
Calendar `type` is restricted to 2. Both endpoints default to the current Myanmar
month/year and return HTTP 422 for invalid filters.

The Babagyi Android project uses the records endpoint for today's four result
cards and the calendar endpoint for its five-column weekday grid. Its screens
must be deployed together with this API update. Betting clocks still come from
the separate lottery-types endpoint.

## Demo history seeder

Run the demo seeder explicitly against a configured development database:

```powershell
php artisan db:seed --class=Thai2DDemoSeeder
```

It generates invented, repeatable two-digit results for **October 1 through today,
capped at November 30 of the current Myanmar year**, at all four history times.
Weekends and times later than the current Myanmar time are skipped. As of
November 8, 2026, an empty history receives 108 rows: 88 in October and 20 in
November, ending Friday, November 6. The API already excludes weekends.

The seeder requires Thai 2D (`lottery_type_id = 2`) and all four clocks to be
configured. It preserves existing date/clock results, including their timestamps,
and can be rerun without duplicates. It only inserts missing `lotteries` rows;
vouchers, balances, hot numbers, betting configuration, and scheduled releases
are not processed. It is not included in the default `DatabaseSeeder`.
These generated values are demo data, not historical market results.

## Scheduler setup

The application needs its database and a continuously enabled Laravel scheduler.
On a server, configure cron to run the following **every minute**, using the
deployed project's absolute path:

```sh
* * * * * cd /path/to/mmt2d && php artisan schedule:run >> /dev/null 2>&1
```

For local Windows development, keep this running from the project directory
while PHP and MySQL are available:

```powershell
php artisan schedule:work
```

For unattended Windows operation, use Task Scheduler to run `php artisan
schedule:run` every minute, with the project directory as **Start in**.
Avoid installing a second scheduler if one already runs this project.

Inspect the registered schedule with `php artisan schedule:list`. The individual
commands are `php artisan lottery:record-thai-2d 09:30` and `php artisan
lottery:record-thai-2d 14:00`. The scheduler starts these commands at **09:29**
and **13:59**, one minute early, to absorb scheduler startup delay. Manual runs
must start in the minute before or during the matching Myanmar minute.

The command polls the live API using its top-level `server_time` as the draw
clock. It saves `live.twod` only from a response whose `server_time` equals
**09:30:00** or **14:00:00** on today's Myanmar date. `live.time` is the stock
update timestamp and may lag `server_time`; it must be on today's date, no more
than five minutes behind the API clock, and never in the future. The saved
history timestamp is the target draw time. Success logs include `win_digit`,
`server_time`, `live_time`, and the local `recorded_at` time.

Polling runs once per second, increasing to five requests per second within two
seconds of the target. It stops after 90 seconds (plus any in-flight request,
with a five-second HTTP timeout). A missing or invalid API server timestamp,
HTTP failure, or skipped target second produces a warning and no history row.
There is no fallback to a later digit. Network latency and API update frequency
can still cause a missed second; the API's server clock does not guarantee
that a request will observe every second. Keep the host clock synchronized so
capture starts in the correct window. Existing settlement commands are unchanged.
