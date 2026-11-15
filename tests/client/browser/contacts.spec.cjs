const { test, expect } = require('@playwright/test');
const { fixtures } = require('./fixtures.cjs');
const fs = require('fs');
const path = require('path');

for (const route of ['contacts', 'forgot']) {
    test(`${route} provides launch links and separate copy controls`, async ({ page, context }) => {
        const { errors } = await fixtures(page, { authenticated: false });
        await context.grantPermissions(['clipboard-read', 'clipboard-write']);
        await page.route('**/app/api/contacts', request => request.fulfill({ json: [
            { id: 1, service: 'Telegram', contact: 'https://t.me/support_test', launch_url: 'https://t.me/support_test' },
            { id: 2, service: 'Viber', contact: '+959123456789', launch_url: 'viber://chat?number=%2B959123456789' },
            { id: 3, service: 'Phone', contact: '09123456789', launch_url: null },
        ] }));
        await page.goto(`/app/#/${route}`);
        const rows = page.locator('.contact-row');
        await expect(rows).toHaveCount(3);
        await expect(page.getByRole('link', { name: 'Open Telegram' })).toHaveAttribute('href', 'https://t.me/support_test');
        await expect(page.getByRole('link', { name: 'Open Viber' })).toHaveAttribute('href', 'viber://chat?number=%2B959123456789');
        await expect(page.getByRole('link', { name: 'Get Viber' })).toHaveAttribute('href', 'https://www.viber.com/en/download/');
        await expect(rows.nth(2).locator('a')).toHaveCount(0);
        const screenshots = path.resolve('storage/app/previews/contacts');
        fs.mkdirSync(screenshots, { recursive: true });
        for (const width of [360, 412, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
            await page.screenshot({ path: path.join(screenshots, `${route}-${width}.png`), fullPage: true });
        }
        await rows.nth(1).getByRole('button').click();
        await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe('+959123456789');
        await rows.nth(0).getByRole('button').focus();
        await page.keyboard.press('Enter');
        await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe('https://t.me/support_test');
        await expect(page).toHaveURL(new RegExp(`#/${route}$`));
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        expect(errors).toEqual([]);
    });
}
