const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

test.beforeAll(() => {
    execFileSync('php', ['-d', 'error_reporting=24575', 'vendor/pestphp/pest/bin/pest',
        'tests/Feature/TelegramNotificationsTest.php', '--filter=test_admin_can_retry_failed_alerts_and_render_delivery_history', '--no-coverage'], { stdio: 'pipe' });
});

// Rendered by TelegramNotificationsTest against isolated SQLite; never uses real admin credentials.
test.beforeEach(async ({ page, baseURL }) => {
    const html = fs.readFileSync(path.resolve('storage/app/previews/admin-telegram.html'), 'utf8')
        .replaceAll('http://localhost', baseURL);
    await page.route('**/admin/telegram**', route => route.fulfill({ contentType: 'text/html', body: html }));
    await page.route('**/api/admin-notify*', route => route.fulfill({ json: { transaction_req: 0, withdraw_req: 0 } }));
});

for (const mode of [
    { width: 1440, height: 900, theme: 'light' },
    { width: 1280, height: 720, theme: 'dark' },
    { width: 1024, height: 900, theme: 'light' },
    { width: 390, height: 844, theme: 'light' },
    { width: 1440, height: 900, theme: 'light', density: 'comfortable' },
    { width: 390, height: 844, theme: 'dark', density: 'comfortable' },
]) {
    test(`Telegram settings fit ${mode.width}px ${mode.theme} ${mode.density || 'compact'}`, async ({ page }) => {
        await page.setViewportSize({ width: mode.width, height: mode.height });
        await page.goto('/admin/telegram');
        await page.locator('.app-root').evaluate((root, mode) => {
            root.dataset.theme = mode.theme;
            root.dataset.density = mode.density || 'compact';
        }, mode);
        await expect(page.getByRole('heading', { name: 'Telegram admin alerts' })).toBeVisible();
        await expect(page.getByLabel('Bot token', { exact: true })).toHaveValue('');
        await expect(page.getByLabel('Admin chat ID', { exact: true })).toHaveValue('123456789');
        await expect(page.getByRole('button', { name: 'Save settings' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Send test message' })).toBeEnabled();
        await page.getByLabel('Admin chat ID', { exact: true }).focus();
        await expect(page.getByLabel('Admin chat ID', { exact: true })).toBeFocused();
        await page.getByLabel('Enable Telegram notifications').uncheck();
        await expect(page.getByLabel('Enable Telegram notifications')).not.toBeChecked();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.screenshot({ path: `storage/app/previews/telegram-${mode.width}-${mode.theme}-${mode.density || 'compact'}.png`, fullPage: true });
    });
}

test('settings, test and retry forms use their own protected actions', async ({ page }) => {
    await page.goto('/admin/telegram');
    await expect(page.getByRole('button', { name: 'Save settings' }).locator('..').locator('..')).toHaveAttribute('method', 'POST');
    const requests = [];
    await page.route('**/admin/telegram**', async route => {
        if (route.request().method() === 'POST') {
            requests.push({ url: route.request().url(), data: route.request().postData() });
            await route.fulfill({ status: 200, contentType: 'text/html', body: '<p>Action received</p>' });
        } else {
            await route.fallback();
        }
    });
    await page.getByLabel('Admin chat ID', { exact: true }).fill('987654321');
    await page.getByRole('button', { name: 'Save settings' }).click();
    await expect(page.getByText('Action received')).toBeVisible();
    expect(requests[0].data).toContain('_method=PUT');
    expect(requests[0].data).toContain('_token=');
    expect(requests[0].data).toContain('admin_chat_id=987654321');
    await page.goto('/admin/telegram');
    await page.getByRole('button', { name: 'Send test message' }).click();
    await expect(page.getByText('Action received')).toBeVisible();
    expect(requests[1].url).toMatch(/\/admin\/telegram\/test$/);
    await page.goto('/admin/telegram');
    await page.getByRole('button', { name: 'Retry', exact: true }).click();
    await expect(page.getByText('Action received')).toBeVisible();
    expect(requests[2].url).toMatch(/\/admin\/telegram\/alerts\/\d+\/retry$/);
});
