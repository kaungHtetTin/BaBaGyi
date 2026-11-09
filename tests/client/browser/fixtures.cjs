const user = {
    id: 101,
    name: "ကိုအောင်",
    phone: "09123456789",
    balance: 125000,
    avatar_url: "",
    recovery_hint: "test",
    disable: 0,
};
const clocks = [
    { id: 2, hour: 12, minute: 1, morning: 0, close_before: 30 },
    { id: 4, hour: 4, minute: 30, morning: 0, close_before: 30 },
];
async function fixtures(page, { authenticated = true, partial = false } = {}) {
    const calls = [];
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await page.clock.setFixedTime(new Date("2026-11-09T03:00:00Z"));
    await page.route("**/app/session", (route) =>
        route.fulfill({ json: { csrf: "test-csrf", authenticated } }),
    );
    await page.route("**/app/api/**", async (route) => {
        const request = route.request();
        const url = new URL(request.url());
        const endpoint = url.pathname.split("/app/api/")[1];
        const body =
            request.method() === "POST" ? request.postDataJSON() : null;
        calls.push({ endpoint, method: request.method(), body });
        let data;
        if (endpoint === "login" || endpoint === "register")
            data = { status: "success", user };
        else if (endpoint === "check-user") data = { user_exist: false };
        else if (endpoint === "user") data = { status: "success", user };
        else if (endpoint === "notices")
            data = {
                user,
                notices: [{ content: "MasterGyi မှ ကြိုဆိုပါတယ်" }],
                ads: [],
                lottery_types: [
                    { id: 2, type: "MM 2D", coefficient: 85, open: 1 },
                    { id: 3, type: "MM 3D", coefficient: 500, open: 1 },
                ],
            };
        else if (endpoint === "contacts")
            data = [{ id: 1, service: "Telegram", contact: "@mastergyi_test" }];
        else if (endpoint === "bankings")
            data = [
                { id: 1, bank: "KBZ Pay", icon_url: "" },
                { id: 2, bank: "Wave Pay", icon_url: "" },
            ];
        else if (endpoint === "payment-method")
            data = {
                id: 4,
                banking_id: 1,
                method: "09999999999",
                account_name: "Test recipient",
            };
        else if (endpoint === "avatars")
            data = [
                {
                    id: 1,
                    url: `${url.origin}/client/images/mastergyi_avatar.webp`,
                },
            ];
        else if (endpoint === "latest-version")
            data = { version_name: "1.2.0" };
        else if (endpoint === "user/wallet-histories")
            data = {
                unverified_transactions: [{ id: 1, amount: 2000 }],
                unverified_withdraws: [{ id: 2, amount: 1000 }],
                wallet_histories: {
                    data: [
                        {
                            id: 4,
                            title: "ထီထိုး",
                            income: 0,
                            amount: 100,
                            created_at: "2026-11-09 09:00:00",
                        },
                    ],
                    next_page_url: null,
                },
            };
        else if (endpoint === "user/vouchers")
            data = {
                data: [
                    {
                        id: 1,
                        number: "07",
                        amount: 100,
                        created_at: "2026-11-09 09:30:00",
                        win: 0,
                        verified: 0,
                        clock: clocks[0],
                    },
                ],
                next_page_url: null,
            };
        else if (endpoint.startsWith("lottery-types/"))
            data = endpoint.includes("/3/")
                ? [{ id: 5, hour: 3, minute: 30, morning: 0, close_before: 30 }]
                : clocks;
        else if (endpoint === "remote/thai-2d")
            data = { live: { twod: "74", set: "1,234.56", value: "2,345.67" } };
        else if (endpoint === "remote/thai-3d")
            data = { number: "Soon", history: {} };
        else if (endpoint === "lotteries/records")
            data = ["07", "12", "34", "74"].map((number, i) => ({
                number,
                date: "2026-11-09",
                draw_time: ["09:30", "12:01", "14:00", "16:30"][i],
            }));
        else if (endpoint === "lotteries/calendar") {
            const year = Number(url.searchParams.get("year"));
            const month = Number(url.searchParams.get("month"));
            data = {
                year,
                month,
                days: [
                    {
                        date: `${year}-${String(month).padStart(2, "0")}-09`,
                        day: 9,
                        results: {
                            "09:30": { number: "07" },
                            "12:01": null,
                            "14:00": { number: "34" },
                            "16:30": null,
                        },
                    },
                ],
            };
        } else if (endpoint === "lotteries/3d-records") data = [];
        else if (endpoint === "numbers") {
            const type = Number(url.searchParams.get("lottery_type_id"));
            data = {
                numbers: Array.from(
                    { length: type === 3 ? 1000 : 100 },
                    (_, i) => ({
                        id: i + 1,
                        number: String(i).padStart(type, "0"),
                        sell: 5000,
                        demand: i === 10 ? 4500 : 100,
                        disable: i === 99 ? 1 : 0,
                    }),
                ),
                hot_numbers: [],
            };
        } else if (endpoint === "vouchers/store") {
            await new Promise((resolve) => setTimeout(resolve, 150));
            data = partial
                ? {
                      status: "success",
                      error_numbers: [
                          { number: "07", amount: 100, allowed_amount: 0 },
                      ],
                  }
                : { status: "success" };
        } else if (request.method() === "POST")
            data = { status: "success", user };
        else
            return route.fulfill({
                status: 404,
                json: { message: `Missing test fixture: ${endpoint}` },
            });
        return route.fulfill({ json: data });
    });
    return { calls, errors };
}
module.exports = { fixtures, user };
