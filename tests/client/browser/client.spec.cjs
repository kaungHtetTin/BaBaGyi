const { test, expect } = require("@playwright/test");
const { fixtures } = require("./fixtures.cjs");
const fs = require("fs");
const path = require("path");
const screenshots = path.resolve("storage/app/previews/client");
test.beforeAll(() => fs.mkdirSync(screenshots, { recursive: true }));

test("login matches native structure and signs in without a full page navigation", async ({
    page,
}) => {
    const { errors } = await fixtures(page, { authenticated: false });
    await page.goto("/app/");
    await expect(page.locator(".auth-card")).toBeVisible();
    await expect(page.locator(".auth-hero")).toHaveCSS("min-height", "200px");
    await page.screenshot({ path: path.join(screenshots, "web-login.png") });
    await page.locator('[name="phone"]').fill("09123456789");
    await page.locator('[name="password"]').fill("password");
    await page.locator('button[type="submit"]').click();
    await expect(page.locator(".greeting")).toContainText("ကိုအောင်");
    await expect(page.locator(".bottom-nav button")).toHaveCount(4);
    expect(errors).toEqual([]);
});

test("home wallet shortcuts and browser back restore the source scroll", async ({
    page,
}) => {
    const { errors } = await fixtures(page);
    await page.goto("/app/");
    await expect(page.locator(".service-card .rate").first()).toContainText(
        "85",
    );
    expect((await page.locator(".wallet-heading>.icon").boundingBox()).x).toBe(
        40,
    );
    expect(
        (await page.locator(".wallet-heading>span:nth-child(2)").boundingBox())
            .x,
    ).toBe(72);
    await page.screenshot({ path: path.join(screenshots, "web-home.png") });
    await page.locator(".screen-scroll").evaluate((el) => (el.scrollTop = 180));
    const scroll = await page
        .locator(".screen-scroll")
        .evaluate((el) => el.scrollTop);
    await page.locator('.wallet-actions [data-nav="topup"]').click();
    await expect(page.locator(".bank-row")).toHaveCount(2);
    await page.goBack();
    await expect(page.locator(".greeting")).toBeVisible();
    expect(
        await page.locator(".screen-scroll").evaluate((el) => el.scrollTop),
    ).toBe(scroll);
    await page.locator('.bottom-nav [data-nav="wallet"]').click();
    await expect(page.locator(".pocket-card")).toBeVisible();
    await page.locator('.action-tiles [data-nav="wallet-history"]').click();
    await expect(page.locator(".history-row")).toHaveCount(3);
    expect(errors).toEqual([]);
});

test("topup and withdraw use the native request fields and one request ID", async ({
    page,
}) => {
    const { calls, errors } = await fixtures(page);
    await page.goto("/app/#/topup");
    await page.locator('[data-bank="1"]').click();
    await expect(page.locator(".payment-card")).toContainText("Test recipient");
    await page.locator('[name="amount"]').fill("1000");
    await page.locator('[name="bank_transaction_id"]').fill("001234");
    await page.screenshot({ path: path.join(screenshots, "web-topup.png") });
    await page.locator('button[type="submit"]').click();
    await expect
        .poll(() => calls.filter((x) => x.endpoint === "top-up").length)
        .toBe(1);
    const request = calls.find((x) => x.endpoint === "top-up").body;
    expect(request.bank_transaction_id).toBe("001234");
    expect(request.payment_method_id).toBe(4);
    expect(request.request_id).toMatch(/^[a-f0-9-]{36}$/);
    await page.goto("/app/#/withdraw");
    await page.locator('[data-bank="1"]').click();
    await page.locator('[name="account_name"]').fill("Test name");
    await page.locator('[name="method"]').fill("09123456789");
    await page.locator('[name="amount"]').fill("1000");
    await page.locator('button[type="submit"]').click();
    await expect
        .poll(() => calls.filter((x) => x.endpoint === "withdraw").length)
        .toBe(1);
    expect(calls.find((x) => x.endpoint === "withdraw").body.method).toBe(
        "09123456789",
    );
    expect(errors).toEqual([]);
});

test("disabled extra draws disappear from result cards and calendar including empty days", async ({ page }) => {
    const { errors } = await fixtures(page, { extraDraws: false });
    await page.goto("/app/#/lottery?type=2");
    await expect(page.locator("[data-saved]")).toHaveCount(2);
    await expect(page.locator('[data-saved="12:01"]')).toHaveText("12");
    await expect(page.locator('[data-saved="09:30"]')).toHaveCount(0);
    await expect(page.locator('[data-saved="14:00"]')).toHaveCount(0);
    await page.locator('[data-nav="calendar?type=2"]').click();
    await expect(page.locator(".calendar-day")).toHaveCount(21);
    await expect(page.locator(".calendar-day").first().locator(".day-result")).toHaveCount(2);
    await expect(page.locator(".calendar-note")).toHaveText("12:01 PM · 04:30 PM");
    await expect(page.locator(".calendar-grid")).not.toContainText("09:30");
    await expect(page.locator(".calendar-grid")).not.toContainText("14:00");
    await page.locator("[data-nav-back]").click();
    await expect(page.locator("[data-saved]")).toHaveCount(2);
    expect(errors).toEqual([]);
});

test("2D saved results and weekday calendar use all four saved slots", async ({
    page,
}) => {
    const { errors } = await fixtures(page);
    await page.goto("/app/#/lottery?type=2");
    await expect(page.locator('[data-saved="09:30"]')).toHaveText("07");
    await expect(page.locator('[data-saved="16:30"]')).toHaveText("74");
    await page.screenshot({ path: path.join(screenshots, "web-2d.png") });
    await page.locator('[data-nav="calendar?type=2"]').click();
    await expect(page.locator(".calendar-day")).toHaveCount(21);
    await expect(page.locator(".weekdays>span")).toHaveCount(5);
    await page.locator('[data-action="previous"]').click();
    await expect(page.locator("[data-month]")).toHaveText("October 2026");
    await page.screenshot({ path: path.join(screenshots, "web-calendar.png") });
    await page.locator("[data-nav-back]").click();
    await page.goForward();
    await expect(page.locator("[data-month]")).toHaveText("October 2026");
    await page.locator(".screen-scroll").evaluate((el) => (el.scrollTop = 160));
    await expect
        .poll(() => page.evaluate(() => history.state.scrolls?.[0]?.top))
        .toBe(160);
    await page.reload();
    await expect(page.locator(".calendar-day")).toHaveCount(22);
    await expect(page.locator("[data-month]")).toHaveText("October 2026");
    await expect
        .poll(() =>
            page.locator(".screen-scroll").evaluate((el) => el.scrollTop),
        )
        .toBe(160);
    expect(errors).toEqual([]);
});

test("selection, reverse, quick pick, two review screens and duplicate click protection", async ({
    page,
}) => {
    const { calls, errors } = await fixtures(page);
    await page.goto("/app/#/buy?type=2&clock=2");
    await expect(page.locator(".number-cell")).toHaveCount(100);
    await expect(page.locator('[data-number="99"]')).toBeDisabled();
    await page.locator('[data-action="reverse"]').click();
    await page.locator('[data-number="07"]').click();
    await expect(page.locator('[data-number="70"]')).toHaveAttribute(
        "aria-pressed",
        "true",
    );
    await page.locator('[data-action="quick"]').click();
    await expect(page.getByRole("dialog")).toBeVisible();
    await page.locator('[data-pick="double"]').click();
    await page.goBack();
    await expect(page.getByRole("dialog")).toHaveCount(0);
    await page.screenshot({
        path: path.join(screenshots, "web-number-grid.png"),
    });
    await page.locator('[name="stake"]').fill("100");
    const controlsTop = (await page.locator(".buy-controls").boundingBox()).y;
    await page.locator(".number-grid").evaluate((el) => (el.scrollTop = 200));
    expect((await page.locator(".buy-controls").boundingBox()).y).toBe(
        controlsTop,
    );
    await page.locator('[data-action="continue"]').click();
    await expect(page.locator(".cart-row")).toHaveCount(11);
    await page.locator('[data-row="8"]').fill("200");
    await page.locator('[data-action="review"]').click();
    await expect(page.locator(".review-summary")).toBeVisible();
    await page.screenshot({ path: path.join(screenshots, "web-review.png") });
    await page.locator('[data-action="purchase"]').dblclick();
    await expect(page.locator(".voucher-table")).toBeVisible();
    expect(calls.filter((x) => x.endpoint === "vouchers/store")).toHaveLength(
        1,
    );
    expect(
        JSON.parse(
            calls.find((x) => x.endpoint === "vouchers/store").body.numberJSON,
        ).find((x) => x.number === "07").amount,
    ).toBe(200);
    await page.goBack();
    await expect(page.locator('[data-action="purchase"]')).toHaveCount(0);
    await expect(page.locator('[data-number="07"]')).toHaveAttribute(
        "aria-pressed",
        "true",
    );
    expect(errors).toEqual([]);
});

test("invalid home responses expose the native retry action and recover", async ({
    page,
}) => {
    const { errors } = await fixtures(page);
    let failed = false;
    await page.route("**/app/api/notices", (route) => {
        if (!failed) {
            failed = true;
            return route.fulfill({ json: {} });
        }
        return route.fallback();
    });
    await page.goto("/app/");
    await expect(page.locator('[data-action="retry"]')).toBeVisible();
    await page.locator('[data-action="retry"]').click();
    await expect(page.locator(".service-card .rate").first()).toContainText(
        "85",
    );
    expect(errors).toEqual([]);
});

test("3D selection uses five columns and all unique digit permutations", async ({
    page,
}) => {
    const { errors } = await fixtures(page);
    await page.goto("/app/#/buy?type=3&clock=5");
    await expect(page.locator(".number-cell")).toHaveCount(1000);
    await page.locator('[data-action="reverse"]').click();
    await page.locator('[data-number="007"]').click();
    await expect(page.locator(".number-cell.selected")).toHaveCount(3);
    expect(errors).toEqual([]);
});

test("partial purchases have a dedicated error screen and never replay automatically", async ({
    page,
}) => {
    const { calls } = await fixtures(page, { partial: true });
    await page.goto("/app/#/buy?type=2&clock=2");
    await page.locator('[data-number="07"]').click();
    await page.locator('[name="stake"]').fill("100");
    await page.locator('[data-action="continue"]').click();
    await page.locator('[data-action="review"]').click();
    await page.locator('[data-action="purchase"]').click();
    await expect(page).toHaveURL(/purchase-errors/);
    await expect(page.locator("tbody")).toContainText("07");
    expect(calls.filter((x) => x.endpoint === "vouchers/store")).toHaveLength(
        1,
    );
});

test("layout fits compact screens and dark mode follows Android night resources", async ({
    page,
}) => {
    const { errors } = await fixtures(page);
    await page.setViewportSize({ width: 320, height: 740 });
    await page.goto("/app/");
    await expect(page.locator(".greeting")).toBeVisible();
    expect(
        await page.evaluate(() => document.documentElement.scrollWidth),
    ).toBe(320);
    expect(
        await page
            .locator(".screen-scroll")
            .evaluate((el) => el.scrollWidth <= el.clientWidth),
    ).toBe(true);
    await page.emulateMedia({ colorScheme: "dark" });
    await expect(page.locator(".screen")).toHaveCSS(
        "background-color",
        "rgb(16, 23, 34)",
    );
    await page.screenshot({
        path: path.join(screenshots, "web-home-dark-compact.png"),
    });
    await page.setViewportSize({ width: 768, height: 1000 });
    await page.emulateMedia({ colorScheme: "light" });
    expect(
        (await page.locator("[data-home] .services").boundingBox()).width,
    ).toBe(728);
    expect(errors).toEqual([]);
});

test("registration follows all four native steps and only registers after explicit confirmation", async ({
    page,
}) => {
    const { calls, errors } = await fixtures(page, { authenticated: false });
    await page.goto("/app/#/register");
    for (const [name, value] of [
        ["name", "Test name"],
        ["phone", "09123456789"],
        ["password", "secret123"],
        ["confirmation", "secret123"],
    ]) {
        await page.locator(`[name="${name}"]`).fill(value);
        await page.locator('button[type="submit"]').click();
    }
    await expect(page.locator(".register-prompt")).toContainText(
        "အသင့်ဖြစ်ပါပြီ",
    );
    expect(calls.filter((x) => x.endpoint === "register")).toHaveLength(0);
    await page.locator('button[type="submit"]').click();
    await expect(page.locator(".greeting")).toBeVisible();
    expect(calls.find((x) => x.endpoint === "register").body.name).toBe(
        "Test name",
    );
    expect(errors).toEqual([]);
});

test("settings profile and password screens preserve their native field contracts", async ({
    page,
}) => {
    const { calls, errors } = await fixtures(page);
    await page.goto("/app/#/settings");
    await page.locator('.menu-row[data-nav="password"]').click();
    await page.locator('[name="current_password"]').fill("secret123");
    await page.locator('[name="new_password"]').fill("secret456");
    await page.locator('button[type="submit"]').click();
    await expect
        .poll(() => calls.some((x) => x.endpoint === "password-reset"))
        .toBe(true);
    expect(
        calls.find((x) => x.endpoint === "password-reset").body.new_password,
    ).toBe("secret456");
    await page.goto("/app/#/profile");
    await expect(page.locator(".avatar-options button")).toHaveCount(1);
    await page.locator('[name="name"]').fill("Updated name");
    await page.locator(".avatar-options button").click();
    await page.screenshot({ path: path.join(screenshots, "web-profile.png") });
    await page.locator('button[type="submit"]').click();
    await expect
        .poll(() => calls.some((x) => x.endpoint === "user/update"))
        .toBe(true);
    expect(calls.find((x) => x.endpoint === "user/update").body.name).toBe(
        "Updated name",
    );
    expect(
        calls.find((x) => x.endpoint === "user/update").body.avatar_url,
    ).toContain("mastergyi_avatar.webp");
    await page.goto("/app/#/contacts");
    await expect(page.locator(".contact-row")).toContainText("@mastergyi_test");
    await page.goto("/app/#/settings");
    await page.evaluate(async () => {
        const { state } = await import("/client/core.js");
        const registration = new EventTarget();
        registration.update = async () => {};
        registration.installing = new EventTarget();
        state.registration = registration;
    });
    await page.locator('[data-nav="update"]').click();
    await expect(page.locator(".update-info h1")).toHaveText("MasterGyi");
    await expect(page.locator('[data-action="update"]')).toBeHidden();
    await page.evaluate(async () => {
        const { state } = await import("/client/core.js");
        state.registration.waiting = { postMessage() {} };
        state.registration.installing.dispatchEvent(new Event("statechange"));
    });
    await expect(page.locator('[data-action="update"]')).toBeVisible();
    await page.goBack();
    await page.locator('[data-action="logout"]').click();
    await expect(page.locator(".auth-card")).toBeVisible();
    expect(errors).toEqual([]);
});
