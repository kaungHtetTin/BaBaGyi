# MasterGyi client PWA

The client is a single-page application at `/app`, served by the existing Laravel installation. The public welcome page remains at `/`, and its **Web app အသုံးပြုရန်** link opens the client. Admin routes and Android API URLs retain their existing paths.

## Android source and UI parity

Reference project: `C:/Users/Hp/AndroidStudioProjects/Babagyi/app/src/main`.

The implementation follows the active XML layouts and Java event handlers. `public/client/android-resources.json` records all 58 layouts, 133 resolved strings, default/night colors, and dimension resources. Sixty native vector icons are converted to SVG; the original MasterGyi logo, avatar, Poppins fonts, and matching Android Roboto/Myanmar fonts are reused. Fonts are compressed to WOFF2 and include their license notices.

| Web screen/flow | Native reference |
| --- | --- |
| Login, four-step registration | `activity_login.xml`, `activity_register.xml`, `LoginActivity.java`, `RegisterActivity.java` |
| Home and four bottom tabs | `activity_main.xml`, `app_bar_home.xml`, `fragment_explore_main.xml`, `MainActivity.java`, `FragmentExplore.java` |
| Wallet, service hours, guides | `fragment_pocket.xml`, `FragmentPocket.java` |
| Bank selection, top-up, withdrawal | `activity_top_up.xml`, `activity_withdraw.xml`, corresponding Java activities |
| Pending and completed wallet history | `activity_wallet_history.xml`, `item_wallet_history.xml`, `WalletHistoryActivity.java` |
| Support and password recovery | `fragment_contact.xml`, `activity_forget_password.xml`, `item_contact.xml` |
| Settings, profile, avatars, password, deletion | `fragment_setting.xml`, `activity_edit_profile.xml`, `activity_reset_password.xml`, `activity_delete_account.xml` |
| Recovery hint and disabled account | `dialog_recovery_hint.xml`, `activity_disable.xml` |
| 2D/3D live results and saved draws | `activity_thai2_dactivity.xml`, `activity_thai3_dactivity.xml`, `view_thai_2d_results.xml` |
| Five-column weekday calendar, 3D year calendar | `activity_thai2_dcalendar.xml`, `item_thai_2d_day.xml`, `activity_thai3_dcalendar.xml` |
| 2D/3D number selection | **Both use `activity_buy2_dlottery.xml`**, as in the Java source; `item_number.xml` |
| Quick pick, reverse/permutations, hot-number pairs | `dialog_quick_pick_up.xml`, `dialog_quick_pick_up_3d.xml`, corresponding buy activities/adapters |
| Editable selection and final purchase review | `activity_confirm.xml`, `activity_buy_now.xml`, `ConfirmActivity.java`, `BuyNowActivity.java` |
| Partial purchase failures and voucher filters | `activity_lottery_buy_error.xml`, `activity_voucher.xml`, adapters/activities |

Key preserved details:

- Signed-out clients start on login. The main tabs are Home, Wallet, Contact, Settings.
- Canvas `#F5F6FA`, ink `#18243C`, gold `#AE701A`, primary `#203756`, wallet gradient `#182A49` to `#30486F`, and corresponding native night resources.
- Main/secondary toolbars are 80/88 CSS pixels; controls are 56 pixels, cards/controls use the native radii, and inputs use the native spacing. Font metrics are loaded before scroll restoration. Android dp is mapped to CSS pixels at normal font scale.
- The 2D result cards show **09:30, 12:01, 14:00, 16:30** from saved history. Live results and saved results load independently every eight seconds while the screen is active.
- Number grids have five columns; the controls remain fixed while the grid scrolls. R reverses 2D numbers or selects unique 3D permutations. Disabled numbers are excluded. Hot-number pairs mirror amounts and remain separate voucher rows.
- Amount starts empty with the native `100ks min` hint. Selection editing and final review remain separate screens. Proceeding to final review replaces the editable screen in history, matching native `finish()` behavior.
- Browser/app-bar Back restores source selection, input values, list/table scroll, and selected calendar month. Calendar month and scroll also survive reload. Dialogs close on Back before navigating away.
- Voucher filters are Pending, Win, Loss, All. Requests use native API status values and paginator contracts.
- Contact rows copy their value. Forgotten password opens support contacts; it does not create an unsupported email/OTP reset flow. Sign-out acts directly, as in the Android implementation.
- The native 3D calendar's `Thai 2D` toolbar title is retained because the supplied Java source uses that label.

Browser-specific adaptations are the PWA installation action, browser share sheet/clipboard fallback, and service-worker updates in the native update-screen structure. Browser/system bars, keyboard behavior, accessibility font scaling, and font rasterization are platform controlled; screenshots verify the reproduced layout, not an assertion that every device renders identical physical pixels.

Unused legacy Explore fragments, the unused `activity_buy3_dlottery.xml`, empty holiday/test layouts, and APK-specific installation activities are retained in the source inventory and are not additional client screens.

## Implementation

- `resources/views/client.blade.php`: account-independent app shell.
- `public/client/core.js`: same-document routing, UI helpers, Back/dialog handling, session bootstrap, API calls, scroll restoration.
- `public/client/screens.js`: authentication, home, wallet, profile, support, settings.
- `public/client/betting.js`: results, calendars, selection, review, vouchers.
- `public/client/logic.js`: number rules, capacity aggregation for validation, draw closing, date/calendar helpers.
- `public/client/app.css`: XML-derived layout and light/night styles.
- `ClientAppController`: shell, scoped manifest and generated service worker.
- `ClientApiController`: allowlisted same-origin bridge to the existing native API controllers.

The frontend has no JavaScript build step or external CDN dependency. Npm packages are development/testing tools only. Existing bank/avatar/ad images are served through the configured public storage link.

The resource exporter can be rerun against an updated Android checkout:

```powershell
php scripts/export-android-client.php "C:/Users/Hp/AndroidStudioProjects/Babagyi/app/src/main/res"
npm run client:fonts
```

The exporter refreshes resource metadata, icons and bundled Android assets; changes to layouts, colors or Java behavior still require corresponding screen/CSS review. The compressed system Roboto/Myanmar fonts are checked in and are not replaced by the exporter.

## Authentication and requests

The browser receives no bearer token in JSON, localStorage or sessionStorage. Laravel keeps the token in its server session and an encrypted, app-path-scoped, HTTP-only remember cookie. The remember cookie lasts 90 days; logout revokes that client's token. Admin authentication does not authenticate the client account. Mutating requests require Laravel CSRF protection.

Financial requests have UUID request IDs. `client_requests` stores the result in the same database transaction as the operation. Repeating a request returns its original response, including after a draw closes; reusing the UUID for different data returns 409. User and number rows are locked. The shared native purchase/withdraw controllers also lock and transact their writes, so Android and web clients use the same wallet/capacity locks. Hot-number rows are not merged into different voucher records.

Partial purchase failures display the native error list. The client does not retry the full payload automatically. Request IDs are reused for explicit retries of an unchanged request after an uncertain response. Offline writes are rejected, never queued or synchronized later.

The bridge validates positive amounts, selected number/type/clock ownership, availability and Myanmar draw closing rules. The existing native response formats remain compatible. The shared 3D live endpoint's draw-day string/array bug is fixed, payment accounts use a supported random query with an unavailable-account response, and top-up counters increment atomically.

## PWA behavior

Manifest: `/app/manifest.webmanifest`. Worker: `/app/sw.js`. Scope/start URL: `/app` (without requiring a trailing slash, compatible with the existing Apache rewrite rule). URLs are generated relative to the installed Laravel base, including installations under `/babagyi/public`.

Only the app shell, its static assets and fonts are cached. API responses, account records, CSRF responses, admin pages and POST requests are excluded. Hash fragments are removed when matching the shell cache so an installed app can reload a screen offline. Account data requires an online session check. New workers wait until the user chooses the update action.

The app uses 192/512 pixel icons, a separate maskable icon, theme metadata, safe-area insets, and standalone display. HTTPS is required on a live domain. Localhost is suitable for development.

## Deployment

Deploy the changed Laravel code, Blade views, migration, and **all of `public/client`**, along with the welcome-page link/style changes. Existing Composer dependencies are sufficient. No Node process or additional scheduled command is required on the live server.

From the Laravel project directory on the live server:

```bash
php artisan migrate --force
php artisan optimize:clear
```

Set `APP_URL` to the existing public base URL, retain `APP_KEY`, use production settings and HTTPS, and keep the database/session storage available. Ensure `public/storage` points to `storage/app/public` (`php artisan storage:link` if it is missing). Do not reseed or replace the existing production database.

For the currently referenced live installation, the client URL after deployment is:

```text
https://www.margomanpower.com/babagyi/public/app
```

The deployment has **not** been performed from this workspace. No live purchase, transfer or account deletion was used during verification.

## Verification

Tests use in-memory SQLite or intercepted browser fixtures, not the production database:

```powershell
php artisan test --filter="ClientAppTest|WelcomePageTest|Thai2DHistoryApiTest|RecordThai2DSnapshotTest|AdminResultCalendarTest|MidnightLotteryResetTest|Thai2DDemoSeederTest"
npm run test:client
npm run test:client-ui
```

Browser tests use installed Microsoft Edge, launch an isolated PHP server on port 8098, and cover login/signup, main tabs, wallet payloads, four saved slots, calendar reload/scroll, five-column number selection, R/quick pick, separate reviews, duplicate-click protection, partial completion, compact/night layouts, profile/password navigation, native retry recovery and offline shell launch. Browser fixture accounts are test data only.

The Android debug app was built, and the existing five `HomeScreenTest` / `Thai2DHistoryTest` instrumentation tests passed on the local Pixel_10a emulator. Native light/night screenshots and browser screenshots are saved under `storage/app/previews/client` for comparison. The in-app browser connector was unavailable, so browser QA used a separate local test browser. The Android source was not edited.

Verified result: **71 PHP feature/regression checks, 7 client logic checks, and 12 browser checks passed**. The five native reference tests also passed. Browser screenshots use fixture data.

Live integrations still need the normal deployment smoke check with a test customer account: login, configured bank/avatar/ad media, contacts, and read-only result/history pages. A visual review on the target phones remains useful for browser/system-font differences.
