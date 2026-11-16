const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

test.beforeAll(() => {
    execFileSync('php', ['-d', 'error_reporting=24575', 'vendor/pestphp/pest/bin/pest',
        'tests/Feature/BonusPaymentTest.php', '--filter=test_settings_render_bonus_form_and_a_fresh_reference_after_payment', '--no-coverage'], { stdio: 'pipe' });
});

test.beforeEach(async ({ page, baseURL }) => {
    const html = fs.readFileSync(path.resolve('storage/app/previews/admin-user-bonus.html'), 'utf8').replaceAll('http://localhost', baseURL);
    await page.route('**/admin/users/*/setting', route => route.fulfill({ contentType: 'text/html', body: html }));
    await page.route('**/api/admin-notify*', route => route.fulfill({ json: { transaction_req: 0, withdraw_req: 0 } }));
});

for (const mode of [
    { width: 1440, height: 900, theme: 'light' },
    { width: 1280, height: 720, theme: 'dark' },
    { width: 1024, height: 900, theme: 'light' },
    { width: 390, height: 844, theme: 'light' },
    { width: 1440, height: 900, theme: 'light', density: 'comfortable' },
]) {
    test(`bonus form fits ${mode.width}px ${mode.theme} ${mode.density || 'compact'}`, async ({ page }) => {
        await page.setViewportSize({ width: mode.width, height: mode.height });
        await page.goto('/admin/users/2/setting');
        await page.locator('.app-root').evaluate((root, mode) => {
            root.dataset.theme = mode.theme;
            root.dataset.density = mode.density || 'compact';
        }, mode);
        await expect(page.getByRole('heading', { name: 'Pay bonus', exact: true })).toBeVisible();
        await page.getByLabel('Bonus amount (MMK)', { exact: true }).fill('5000');
        await page.getByLabel('Bonus note (optional)', { exact: true }).fill('Welcome bonus');
        await page.getByRole('button', { name: 'Pay bonus', exact: true }).click();
        const modal = page.getByRole('dialog');
        await expect(modal).toBeVisible();
        await expect(modal).toContainText('5,000 MMK');
        await expect(modal).toContainText('Bonus Customer');
        await expect(modal).toContainText('Welcome bonus');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.screenshot({ path: `storage/app/previews/bonus-confirm-${mode.width}-${mode.theme}-${mode.density || 'compact'}.png`, fullPage: true });
        await modal.getByRole('button', { name: 'Cancel', exact: true }).click();
        await expect(modal).not.toBeVisible();
        await expect(page.locator('.modal-backdrop')).toHaveCount(0);
        await expect(page.getByLabel('Bonus amount (MMK)', { exact: true })).toHaveValue('5000');
        await page.screenshot({ path: `storage/app/previews/bonus-${mode.width}-${mode.theme}-${mode.density || 'compact'}.png`, fullPage: true });
    });
}

test('review and cancel send nothing, confirming posts once with a payment reference', async ({ page }) => {
    const payments = [];
    await page.route('**/admin/users/*/bonus', async route => {
        payments.push(route.request().postData());
        await route.fulfill({ contentType: 'text/html', body: '<p>Bonus paid successfully</p>' });
    });
    await page.goto('/admin/users/2/setting');
    await page.getByLabel('Bonus amount (MMK)', { exact: true }).fill('2500');
    await page.getByLabel('Bonus note (optional)', { exact: true }).fill('Promotion');
    await page.getByRole('button', { name: 'Pay bonus', exact: true }).click();
    await page.getByRole('button', { name: 'Cancel', exact: true }).click();
    expect(payments).toHaveLength(0);
    await page.getByRole('button', { name: 'Pay bonus', exact: true }).click();
    await page.getByRole('button', { name: 'Confirm payment', exact: true }).click();
    await expect(page.getByText('Bonus paid successfully')).toBeVisible();
    expect(payments).toHaveLength(1);
    const body = new URLSearchParams(payments[0]);
    expect(body.get('bonus_amount')).toBe('2500');
    expect(body.get('bonus_note')).toBe('Promotion');
    expect(body.get('bonus_request_id')).toMatch(/^[a-f0-9-]{36}$/);
    expect(body.has('_token')).toBe(true);
});

test('empty and negative amounts cannot reach confirmation', async ({ page }) => {
    await page.goto('/admin/users/2/setting');
    await page.getByRole('button', { name: 'Pay bonus', exact: true }).click();
    await expect(page.getByRole('dialog')).not.toBeVisible();
    await page.getByLabel('Bonus amount (MMK)', { exact: true }).fill('-1');
    await page.getByRole('button', { name: 'Pay bonus', exact: true }).click();
    await expect(page.getByRole('dialog')).not.toBeVisible();
});
