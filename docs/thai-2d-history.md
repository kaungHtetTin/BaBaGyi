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

The source must return exactly two digits, today's date, and a live timestamp
no more than five minutes old (or one minute ahead). Leading zeroes are kept.
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
lottery:record-thai-2d 14:00`; each accepts captures only during its matching
Myanmar minute to prevent accidental records under the wrong clock.
