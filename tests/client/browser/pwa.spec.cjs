const { test, expect } = require("@playwright/test");
test.use({ serviceWorkers: "allow" });

test("installed shell launches offline without caching accounts or replaying writes", async ({
    page,
    context,
}) => {
    await page.goto("/app");
    await expect(page.locator(".auth-card")).toBeVisible();
    await page.evaluate(() => navigator.serviceWorker.ready);
    await page.reload();
    await expect(page.locator(".auth-card")).toBeVisible();
    expect(
        await page.evaluate(() => Boolean(navigator.serviceWorker.controller)),
    ).toBe(true);
    const cache = await page.evaluate(async () => {
        const keys = await caches.keys();
        const entries = await Promise.all(
            keys
                .filter((x) => x.startsWith("mastergyi-client-"))
                .map(async (key) =>
                    (await (await caches.open(key)).keys()).map((x) => x.url),
                ),
        );
        return entries.flat();
    });
    expect(cache.some((x) => x.includes("/client/logic.js"))).toBe(true);
    expect(
        cache.some(
            (x) =>
                x.includes("/app/api/") ||
                x.includes("/app/session") ||
                x.includes("/admin"),
        ),
    ).toBe(false);
    const manifest = await (
        await page.request.get("/app/manifest.webmanifest")
    ).json();
    expect(manifest.display).toBe("standalone");
    for (const icon of manifest.icons)
        expect((await page.request.get(icon.src)).ok()).toBe(true);
    await context.setOffline(true);
    await page.reload();
    await expect(page.locator(".auth-card")).toBeVisible();
    await expect(page.locator(".offline-notice")).toBeVisible();
    await page.locator('[name="phone"]').fill("09123456789");
    await page.locator('[name="password"]').fill("password");
    await page.locator('button[type="submit"]').click();
    await expect(page.locator(".error-box")).toBeVisible();
    await context.setOffline(false);
    expect(await page.evaluate(() => localStorage.length)).toBe(0);
});
