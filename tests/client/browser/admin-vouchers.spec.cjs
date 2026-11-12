const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

test.beforeAll(() => {
    execFileSync('php', ['artisan', 'test', '--filter=AdminVoucherFiltersTest::test_defaults_show_all_history_with_consistent_totals'], { stdio: 'pipe' });
});

// HTML is rendered by AdminVoucherFiltersTest using an isolated SQLite database.
// Assets and scripts are served by the real Laravel app; production data is never used.
test.beforeEach(async ({ page, baseURL }) => {
    const html = fs.readFileSync(path.resolve('storage/app/previews/admin-voucher-filters.html'), 'utf8')
        .replaceAll('http://localhost', baseURL);
    await page.route('**/admin/vouchers/2d**', route => route.fulfill({ contentType: 'text/html', body: html }));
    await page.route('**/api/admin-notify*', route => route.fulfill({ json: { transaction_req: 0, withdraw_req: 0 } }));
});

for (const mode of [
    { width: 1440, height: 900, theme: 'light' },
    { width: 1280, height: 720, theme: 'dark' },
    { width: 1024, height: 900, theme: 'light' },
    { width: 390, height: 844, theme: 'light' },
    { width: 1440, height: 900, theme: 'light', density: 'comfortable' },
]) {
    test(`voucher filters fit ${mode.width}px ${mode.theme} ${mode.density || 'compact'}`, async ({ page }) => {
        await page.setViewportSize({ width: mode.width, height: mode.height });
        await page.addInitScript(theme => localStorage.setItem('app.theme', theme), mode.theme);
        await page.goto('/admin/vouchers/2d');
        await page.locator('.app-root').evaluate((root, mode) => {
            root.dataset.theme = mode.theme;
            root.dataset.density = mode.density || 'compact';
        }, mode);
        await expect(page.getByRole('button', { name: 'Apply', exact: true })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Reset', exact: true })).toBeVisible();
        await expect(page.getByLabel('Customer / voucher')).toBeVisible();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        const apply = await page.getByRole('button', { name: 'Apply', exact: true }).boundingBox();
        expect(apply.x + apply.width).toBeLessThanOrEqual(mode.width);
        await page.locator('.voucher-more summary').click();
        await expect(page.getByLabel('Exact number')).toBeVisible();
        await expect(page.getByLabel('Min amount (MMK)')).toBeVisible();
        await expect(page.getByLabel('Max amount (MMK)')).toBeVisible();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.getByLabel('Exact number').focus();
        await expect(page.getByLabel('Exact number')).toBeFocused();
        await page.getByLabel('Exact number').blur();
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.screenshot({ path: `storage/app/previews/vouchers-${mode.width}-${mode.theme}-${mode.density || 'compact'}.png`, fullPage: true });
    });
}

test('apply uses a GET URL, preserves exact zeroes and resets pagination', async ({ page }) => {
    await page.goto('/admin/vouchers/2d?page=3');
    await page.getByLabel('Customer / voucher').fill('Alice');
    await page.getByRole('combobox', { name: 'Status', exact: true }).selectOption('unpaid_win');
    await page.locator('.voucher-more summary').click();
    await page.getByLabel('Exact number').fill('07');
    await page.getByLabel('Min amount (MMK)').fill('100');
    await page.getByLabel('Max amount (MMK)').fill('500');
    await page.getByRole('button', { name: 'Apply', exact: true }).click();
    await expect(page).toHaveURL(/number=07/);
    const query = new URL(page.url()).searchParams;
    expect(query.get('q')).toBe('Alice');
    expect(query.get('status')).toBe('unpaid_win');
    expect(query.get('amount_min')).toBe('100');
    expect(query.get('amount_max')).toBe('500');
    expect(query.has('page')).toBe(false);
    await page.getByRole('link', { name: 'Reset', exact: true }).click();
    expect(new URL(page.url()).search).toBe('');
});
