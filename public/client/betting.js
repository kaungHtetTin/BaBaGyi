import {
    $,
    $$,
    state,
    config,
    t,
    esc,
    money,
    amount,
    icon,
    logo,
    field,
    button,
    errorBox,
    loading,
    empty,
    on,
    setError,
    toast,
    api,
    refreshUser,
    submit,
    mutationId,
    navigate,
    replaceParams,
    back,
    screen,
    page,
    actionTiles,
    openDialog,
    closeDialog,
    today,
    dateParts,
    clockLabel,
} from "./core.js";
import {
    historyDrawTimes,
    permutations,
    quickPick,
    toggleNumbers,
    aggregateCart,
    drawClosed,
    weekdayCalendar,
    next3dDraw,
} from "./logic.js";

const typeInfo = (type) =>
    state.home?.lottery_types?.find((x) => Number(x.id) === Number(type));
async function ensureType(signal) {
    if (!state.home) {
        state.home = await api("notices", { signal });
        state.user = state.home.user;
    }
}
const drawLabel = (time) =>
    ({
        "09:30": "09:30 AM",
        "12:01": "12:01 PM",
        "14:00": "02:00 PM",
        "16:30": "04:30 PM",
    })[time];
async function chooseClock(type) {
    try {
        const clocks = await api(`lottery-types/${type}/clocks`);
        openDialog(
            `<h2>ထီထိုးချိန်ရွေးချယ်ပါ</h2>${clocks.length ? clocks.map((clock) => `<button class="clock-row" data-clock="${clock.id}"><span>${esc(clockLabel(clock))}</span><span>›</span></button>`).join("") : empty()}`,
            (root) =>
                on(root, "click", "[data-clock]", (e, target) => {
                    const clock = clocks.find(
                        (x) => Number(x.id) === Number(target.dataset.clock),
                    );
                    if (
                        drawClosed(
                            type,
                            clock,
                            dateParts(),
                            !!typeInfo(type)?.open,
                        )
                    ) {
                        toast(t("lottery_close_for_this_hour"));
                        return;
                    }
                    state.clock = clock;
                    closeDialog(() =>
                        navigate("buy", { type, clock: clock.id }),
                    );
                }),
        );
    } catch (error) {
        toast(error.message);
    }
}
function lottery(params) {
    const type = Number(params.type) === 3 ? 3 : 2;
    const serviceLabels = type === 3
        ? ["ကစားမည်", "3D ပေါက်စဉ်", "3D စာရင်း"]
        : ["ကစားမည်", "2D ပေါက်စဉ်", "2D စာရင်း"];
    const resultCards =
        type === 2
            ? ""
            : [1, 2]
                  .map(
                      (n) =>
                          `<div class="result-card"><strong data-result="${n}">--</strong><span data-date="${n}">—</span></div>`,
                  )
                  .join("");
    const view = screen(
        page(
            `MM ${type}D`,
            "ကံစမ်းမည်",
            `<div class="page"><h1 class="title">MM ${type}D</h1><p class="caption">${esc(t("ui_live_result"))}</p><section class="navy-card live-card"><strong class="live-number" data-live>--</strong><div class="live-info"><strong data-today>${esc(new Intl.DateTimeFormat("en-US", { timeZone: "Asia/Yangon", weekday: "short", month: "short", day: "numeric" }).format(new Date()))}</strong><span data-stock></span></div></section><h2 class="section-title">${esc(t("ui_previous_results"))}</h2><div class="results-grid">${resultCards}</div><h2 class="section-title">${esc(t("home_services"))}</h2><div class="action-tiles"><button data-action="buy">${icon("round_add_reaction_24")}<span>${esc(serviceLabels[0])}</span></button><button data-nav="calendar?type=${type}">${icon("round_calendar_month_24")}<span>${esc(serviceLabels[1])}</span></button><button data-nav="vouchers?type=${type}">${icon("baseline_format_list_bulleted_24")}<span>${esc(serviceLabels[2])}</span></button></div><div data-loading></div>${errorBox()}</div>`,
        ),
        async (signal) => {
            await ensureType(signal);
            let running = false;
            async function refresh() {
                if (running || signal.aborted) return;
                running = true;
                try {
                    const p = dateParts();
                    const tasks = [api(`remote/thai-${type}d`, { signal })];
                    if (type === 2)
                        tasks.push(
                            api(
                                `lotteries/records?type=2&year=${p.year}&month=${Number(p.month)}&include_draws=1`,
                                { signal },
                            ),
                        );
                    const results = await Promise.allSettled(tasks);
                    if (signal.aborted) return;
                    $(view.element, "[data-today]").textContent =
                        new Intl.DateTimeFormat("en-US", {
                            timeZone: "Asia/Yangon",
                            weekday: "short",
                            month: "short",
                            day: "numeric",
                        }).format(new Date());
                    const live = results[0];
                    if (live.status === "fulfilled") {
                        if (type === 2) {
                            $(view.element, "[data-live]").textContent =
                                live.value.live?.twod || "--";
                            $(view.element, "[data-stock]").textContent =
                                `Set - ${live.value.live?.set || "--"}\nValue - ${live.value.live?.value || "--"}`;
                        } else {
                            $(view.element, "[data-live]").textContent =
                                live.value.number || "--";
                            for (const [n, key] of [
                                [1, "first"],
                                [2, "second"],
                            ]) {
                                const row = live.value.history?.[key];
                                $(
                                    view.element,
                                    `[data-result="${n}"]`,
                                ).textContent = row?.number || "--";
                                $(
                                    view.element,
                                    `[data-date="${n}"]`,
                                ).textContent =
                                    row?.date ||
                                    row?.created_at?.slice(0, 10) ||
                                    "—";
                            }
                        }
                    }
                    if (type === 2 && results[1].status === "fulfilled") {
                        const data = results[1].value;
                        $(view.element, ".results-grid").innerHTML = historyDrawTimes(data).map(
                            (time) => `<div class="result-card"><strong data-saved="${time}">--</strong><span>${esc(drawLabel(time))}</span></div>`,
                        ).join("");
                        for (const row of data.records || (Array.isArray(data) ? data : [])) {
                            if (row.date === today()) {
                                const target = $(
                                    view.element,
                                    `[data-saved="${row.draw_time}"]`,
                                );
                                if (target && /^[0-9]{2}$/.test(row.number))
                                    target.textContent = row.number;
                            }
                        }
                    }
                    const errors = results.filter(
                        (x) =>
                            x.status === "rejected" &&
                            x.reason.name !== "AbortError",
                    );
                    setError(
                        view.element,
                        errors.length ? errors[0].reason.message : "",
                    );
                } finally {
                    running = false;
                }
            }
            await refresh();
            const timer = setInterval(() => {
                if (document.visibilityState === "visible") refresh();
            }, 8000);
            signal.addEventListener("abort", () => clearInterval(timer), {
                once: true,
            });
        },
    );
    on(view.element, "click", '[data-action="buy"]', () => chooseClock(type));
    return view;
}

function calendar(params) {
    const type = Number(params.type) === 3 ? 3 : 2;
    const p = dateParts();
    let year = Number(params.year) || Number(p.year),
        month = Number(params.month) || Number(p.month),
        generation = 0;
    const view = screen(
        page(
            "Thai 2D",
            t("record_calender"),
            `<div class="page"><div class="calendar-header"><button class="icon-button" data-action="previous" aria-label="Previous ${type === 2 ? "month" : "year"}">${icon("round_arrow_back_ios_24")}</button><strong data-month></strong><button class="icon-button" data-action="next" aria-label="Next ${type === 2 ? "month" : "year"}">${icon("round_arrow_forward_ios_24")}</button></div>${type === 2 ? `<p class="calendar-note"></p><div class="weekdays">${["Mon", "Tue", "Wed", "Thu", "Fri"].map((x) => `<span>${x}</span>`).join("")}</div>` : ""}<div data-loading>${loading()}</div>${errorBox()}</div>`,
        ),
        (signal) => load(signal),
    );
    async function load(signal) {
        const version = ++generation;
        $(view.element, "[data-month]").textContent =
            type === 2
                ? new Intl.DateTimeFormat("en-US", {
                      month: "long",
                      year: "numeric",
                      timeZone: "UTC",
                  }).format(new Date(Date.UTC(year, month - 1, 1)))
                : String(year);
        $(view.element, "[data-loading]").innerHTML = loading();
        setError(view.element, "");
        try {
            const data = await api(
                type === 2
                    ? `lotteries/calendar?type=2&year=${year}&month=${month}`
                    : `lotteries/3d-records?year=${year}`,
                { signal },
            );
            if (signal?.aborted || version !== generation) return;
            if (type === 2) {
                const days = weekdayCalendar(year, month, data.days || []);
                const drawTimes = historyDrawTimes(data);
                $(view.element, ".calendar-note").textContent = drawTimes.map(drawLabel).join(" · ");
                $(view.element, "[data-loading]").innerHTML =
                    `<div class="calendar-grid">${days.map((day) => (day ? `<article class="calendar-day ${day.date === today() ? "today" : ""}"><span class="day-label">${day.day}</span>${drawTimes.map((time) => `<div class="day-result"><small>${time}</small><strong>${esc(day.results?.[time]?.number ?? "--")}</strong></div>`).join("")}</article>` : "<div></div>")).join("")}</div>`;
            } else {
                $(view.element, "[data-loading]").innerHTML =
                    `<div class="months-grid">${Array.from({ length: 12 }, (_, i) => `<article class="surface month-card">${new Intl.DateTimeFormat("en-US", { month: "short", timeZone: "UTC" }).format(new Date(Date.UTC(year, i, 1)))}${[1, 16].map((day) => `<strong>${esc(data.filter((x) => Number(x.month) === i + 1 && Number((x.created_at || "").slice(8, 10)) === day).at(-1)?.number ?? "--")}</strong><small>${day}</small>`).join("")}</article>`).join("")}</div>`;
            }
        } catch (error) {
            if (error.name !== "AbortError" && version === generation) {
                $(view.element, "[data-loading]").innerHTML = "";
                setError(view.element, error.message);
            }
        }
    }
    for (const [action, delta] of [
        ["previous", -1],
        ["next", 1],
    ])
        on(view.element, "click", `[data-action="${action}"]`, () => {
            if (type === 2) {
                month += delta;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                if (month > 12) {
                    month = 1;
                    year++;
                }
            } else year += delta;
            replaceParams({ year, month });
            load(view.controller?.signal);
        });
    return view;
}

function buy(params) {
    const type = Number(params.type) === 3 ? 3 : 2;
    let clock = null,
        numbers = [],
        hot = [],
        selected = new Set(),
        reverse = false;
    const view = screen(
        page(
            `MM ${type}D`,
            "",
            `<div class="buy-controls"><div class="buy-control-row">${button("အမြန်‌ရွေး", "quick", "quick-button")}${field("stake", "100ks min", "number", "", 'min="100" step="1" inputmode="numeric"')}</div><div class="buy-control-row">${button("ရှင်းလင်းမည်", "clear", "clear-button")}<button class="reverse-button" data-action="reverse" aria-pressed="false">R</button>${button("ထိုးမည်", "continue", "buy-button", "data-submit")}</div><p data-close-time></p><p class="hot-label" hidden></p></div><div data-loading>${loading()}</div><div class="number-grid" data-preserve-scroll role="group" aria-label="${esc(t("lottery_number"))}"></div><div class="page" style="padding-top:0">${errorBox()}</div>`,
            { className: "buy-scroll" },
        ),
        async (signal) => {
            await ensureType(signal);
            if (!clock) {
                const clocks = await api(`lottery-types/${type}/clocks`, {
                    signal,
                });
                clock = clocks.find(
                    (x) => Number(x.id) === Number(params.clock),
                );
                if (!clock)
                    throw new Error(t("please_select_the_correct_time"));
            }
            $(view.element, ".app-bar-title>span").textContent =
                `ထီထွက်ချိန် ${type === 3 ? next3dDraw(dateParts()) + " " : ""}${clockLabel(clock)}`;
            const closeMinutes =
                ((Number(clock.hour) % 12) + (Number(clock.morning) ? 0 : 12)) *
                    60 +
                Number(clock.minute) -
                Number(clock.close_before);
            const closeTime = `${String(Math.floor(closeMinutes / 60) % 12 || 12).padStart(2, "0")}:${String(closeMinutes % 60).padStart(2, "0")} ${closeMinutes >= 720 ? "PM" : "AM"}`;
            $(view.element, "[data-close-time]").textContent =
                `ထီပိတ်ချိန် ${type === 3 ? next3dDraw(dateParts()) + " " : ""}${closeTime}`;
            const data = await api(
                `numbers?lottery_type_id=${type}&clock_id=${clock.id}`,
                { signal },
            );
            if (signal.aborted) return;
            numbers = data.numbers || [];
            hot = data.hot_numbers || [];
            selected = new Set(
                [...selected].filter((n) =>
                    numbers.some(
                        (x) => String(x.number) === n && !Number(x.disable),
                    ),
                ),
            );
            $(view.element, "[data-loading]").innerHTML = "";
            $(view.element, ".hot-label").hidden = !hot.length;
            $(view.element, ".hot-label").textContent = hot
                .map((x) => String(x.number)[0])
                .join(", ");
            draw();
        },
    );
    const isHot = (n) =>
        type === 2 && hot.some((x) => String(x.number)[0] === String(n)[0]);
    function draw() {
        const grid = $(view.element, ".number-grid");
        grid.innerHTML = numbers
            .map((n) => {
                const ratio =
                    Number(n.sell) > 0
                        ? Math.min(
                              100,
                              Math.max(
                                  0,
                                  (100 * Number(n.demand)) / Number(n.sell),
                              ),
                          )
                        : 100;
                return `<button class="number-cell ${selected.has(String(n.number)) ? "selected" : ""}" data-number="${esc(n.number)}" aria-pressed="${selected.has(String(n.number))}" ${Number(n.disable) ? "disabled" : ""} aria-label="${esc(n.number)}${isHot(n.number) ? " hot" : ""}">${isHot(n.number) ? '<i class="hot-dot"></i>' : ""}<strong>${esc(n.number)}</strong><span class="demand-track"><span style="width:${ratio}%;background:${ratio < 50 ? "#4CAF50" : ratio < 80 ? "#FFC107" : "#F44336"}"></span></span></button>`;
            })
            .join("");
    }
    on(view.element, "click", "[data-number]", (e, target) => {
        const n = target.dataset.number;
        selected = toggleNumbers(
            selected,
            reverse
                ? type === 3
                    ? permutations(n)
                    : [n, [...n].reverse().join("")]
                : [n],
            numbers,
        );
        draw();
    });
    on(view.element, "click", '[data-action="reverse"]', (e, target) => {
        reverse = !reverse;
        target.setAttribute("aria-pressed", String(reverse));
    });
    on(view.element, "click", '[data-action="clear"]', () => {
        selected.clear();
        reverse = false;
        $(view.element, '[data-action="reverse"]').setAttribute(
            "aria-pressed",
            "false",
        );
        draw();
    });
    on(view.element, "click", '[data-action="quick"]', () => {
        const digits = (group) =>
            `<div class="quick-grid digits">${Array.from({ length: 10 }, (_, i) => `<button data-pick="${group}" data-value="${i}" aria-pressed="false">${i}</button>`).join("")}</div>`;
        const content =
            type === 3
                ? `<h3>၃ လုံး ဂဏန်းပူးများ</h3><div class="quick-grid four">${Array.from({ length: 10 }, (_, i) => `<button data-pick-single="${String(i).repeat(3)}" aria-pressed="false">${String(i).repeat(3)}</button>`).join("")}</div><h3>၁၀၀ စီ</h3><div class="quick-grid four">${Array.from({ length: 10 }, (_, i) => `<button data-pick="block" data-value="${i * 100}" aria-pressed="false">${String(i * 100).padStart(3, "0")}–${i * 100 + 99}</button>`).join("")}</div>`
                : `<div class="quick-grid"><button data-pick="even-even">စုံစုံ</button><button data-pick="even-odd">စုံမ</button><button data-pick="odd-even">မစုံ</button><button data-pick="odd-odd">မမ</button><button data-pick="double">ပူး</button></div><h3>ပတ်</h3>${digits("contains")}<h3>ထိပ်</h3>${digits("front")}<h3>နောက်</h3>${digits("back")}${[
                      ["star", "နက္ခတ် 07, 18, 24, 35, 69"],
                      ["star-r", "နက္ခတ် R 07, 18, 24, 35, 69"],
                      ["power", "ပါဝါ 05, 16, 27, 38, 49"],
                      ["power-r", "ပါဝါ R 05, 16, 27, 38, 49"],
                  ]
                      .map(([key, label]) =>
                          button(
                              label,
                              `pick-${key}`,
                              "secondary wide",
                              `data-pick="${key}"`,
                          ),
                      )
                      .join(
                          "",
                      )}<h3>ခွေ</h3><div class="snake-row">${field("snake", "ခွေမည်", "text", "", 'inputmode="numeric" pattern="[0-9]+" maxlength="10"')}${button("ခွေမည်", "snake", "secondary")}</div><h3>ပူးခွေ</h3><div class="snake-row">${field("snake-double", "ခွေမည်", "text", "", 'inputmode="numeric" pattern="[0-9]+" maxlength="10"')}${button("ခွေမည်", "snake-double", "secondary")}</div><h3>၂၀ စီ</h3><div class="quick-grid">${[0, 20, 40, 60, 80].map((n) => `<button data-pick="block" data-value="${n}">${String(n).padStart(2, "0")}–${n + 19}</button>`).join("")}</div>`;
        openDialog(`<h2>အမြန်ရွေး</h2>${content}`, (root) => {
            function toggle(list, target) {
                selected = toggleNumbers(selected, list, numbers);
                if (target)
                    target.setAttribute(
                        "aria-pressed",
                        target.getAttribute("aria-pressed") === "true"
                            ? "false"
                            : "true",
                    );
                draw();
            }
            on(root, "click", "[data-pick]", (e, target) =>
                toggle(
                    quickPick(type, target.dataset.pick, target.dataset.value),
                    target,
                ),
            );
            on(root, "click", "[data-pick-single]", (e, target) =>
                toggle([target.dataset.pickSingle], target),
            );
            for (const name of ["snake", "snake-double"])
                on(root, "click", `[data-action="${name}"]`, () => {
                    const value = $(root, `[name="${name}"]`).value;
                    if (!/^[0-9]+$/.test(value)) {
                        toast(t("please_enter_the_lottery_number"));
                        return;
                    }
                    toggle(quickPick(type, name, value));
                });
        });
    });
    on(view.element, "click", '[data-action="continue"]', () =>
        submit(view.element, async () => {
            if (drawClosed(type, clock, dateParts(), !!typeInfo(type)?.open))
                throw new Error(t("lottery_close_for_this_hour"));
            const stake = Number($(view.element, '[name="stake"]').value);
            if (!Number.isSafeInteger(stake) || stake < 100)
                throw new Error(t("the_amount_cannot_be_less_than_100"));
            if (!selected.size)
                throw new Error(t("please_enter_the_lottery_number"));
            const rows = [];
            for (const value of selected) {
                const number = numbers.find((x) => String(x.number) === value);
                const row = {
                    id: number.id,
                    number: String(number.number),
                    amount: stake,
                    allowed_amount: Math.max(
                        0,
                        Number(number.sell) - Number(number.demand),
                    ),
                    key: `${number.id}`,
                };
                rows.push(row);
                if (isHot(value) && value !== [...value].reverse().join("")) {
                    const pair = numbers.find(
                        (x) =>
                            String(x.number) === [...value].reverse().join(""),
                    );
                    if (pair)
                        rows.push({
                            id: pair.id,
                            number: String(pair.number),
                            amount: stake,
                            allowed_amount: Number(pair.disable)
                                ? 0
                                : Math.max(
                                      0,
                                      Number(pair.sell) - Number(pair.demand),
                                  ),
                            pairOf: row.key,
                            key: `${number.id}-pair`,
                        });
                }
            }
            state.cart = {
                type,
                clock,
                rows,
                source: state.current.key,
                completed: false,
            };
            state.views.delete(`#/confirm`);
            state.views.delete(`#/review`);
            navigate("confirm");
        }),
    );
    return view;
}

function confirm() {
    const cart = state.cart;
    if (!cart || cart.completed)
        return screen(
            page(
                "MasterGyi",
                "ထီစာရင်း",
                `<div class="page">${empty()}${button(t("ui_back"), "home", "primary wide")}</div>`,
            ),
        );
    const view = screen(
        page(
            "MasterGyi",
            t("ui_purchase_details"),
            `<div class="confirm-head"><span>ထီဂဏန်း</span><span>ငွေပမာဏ</span><span>ပါယ်ဖျက်</span></div><div class="cart-list"></div><div class="page">${errorBox()}</div>`,
        ),
    );
    const footer = document.createElement("div");
    footer.className = "purchase-footer";
    footer.innerHTML = `<strong data-total></strong>${button("ထိုးမည်", "review", "primary", "data-submit")}`;
    view.element.append(footer);
    function draw() {
        $(view.element, ".cart-list").innerHTML = cart.rows
            .map(
                (row) =>
                    `<div class="cart-row ${row.pairOf ? "paired" : ""}"><strong>${esc(row.number)}</strong>${field(row.key, t("amount"), "number", row.amount, `min="100" step="1" inputmode="numeric" data-row="${row.key}" ${row.pairOf ? "readonly" : ""}`)}${row.pairOf ? "<span>R</span>" : `<button class="icon-button" data-remove="${row.key}" aria-label="${esc(t("ui_remove"))} ${esc(row.number)}">${icon("round_cancel_24")}</button>`}</div>`,
            )
            .join("");
        total();
    }
    function total() {
        $(view.element, "[data-total]").textContent =
            `${money(cart.rows.reduce((sum, row) => sum + Number(row.amount), 0))} MMK`;
    }
    on(view.element, "input", "[data-row]", (e, target) => {
        const row = cart.rows.find((x) => x.key === target.dataset.row);
        row.amount = target.value;
        for (const pair of cart.rows.filter((x) => x.pairOf === row.key)) {
            pair.amount = target.value;
            $(view.element, `[data-row="${pair.key}"]`).value = target.value;
        }
        total();
    });
    on(view.element, "click", "[data-remove]", (e, target) => {
        cart.rows = cart.rows.filter(
            (x) =>
                x.key !== target.dataset.remove &&
                x.pairOf !== target.dataset.remove,
        );
        if (!cart.rows.length) {
            back();
            return;
        }
        draw();
    });
    on(view.element, "click", '[data-action="review"]', () =>
        submit(view.element, async () => {
            for (const row of aggregateCart(cart.rows)) {
                if (!Number.isSafeInteger(row.amount) || row.amount < 100)
                    throw new Error(t("the_amount_cannot_be_less_than_100"));
                if (row.amount > row.allowed_amount)
                    throw new Error(
                        `${row.number} · ${money(row.allowed_amount)} MMK သာ ထိုးနိုင်ပါသည်`,
                    );
            }
            await refreshUser();
            if (
                cart.rows.reduce((sum, row) => sum + Number(row.amount), 0) >
                Number(state.user.balance)
            )
                throw new Error(t("insufficient_balance"));
            state.views.delete("#/review");
            navigate("review", {}, true);
        }),
    );
    draw();
    return view;
}

function review() {
    const cart = state.cart;
    if (!cart || cart.completed)
        return screen(
            page("MasterGyi", "", `<div class="page">${empty()}</div>`),
        );
    const total = cart.rows.reduce((sum, row) => sum + Number(row.amount), 0);
    const view = screen(
        page(
            "MasterGyi",
            "ထီစာရင်းအတည်ပြုခြင်း",
            `<div class="page"><section class="navy-card review-summary"><img src="${logo()}" width="50" height="50" alt=""><h1>MM ${cart.type}D</h1>${[
                [
                    "ထီထွက်ချိန်",
                    (cart.type === 3 ? next3dDraw(dateParts()) + " " : "") +
                        clockLabel(cart.clock),
                ],
                ["အဆ", typeInfo(cart.type)?.coefficient ?? "—"],
                ["စုစုပေါင်းကျသင့်ငွေ", `${money(total)} MMK`],
                [
                    "လက်ကျန်ငွေ",
                    `${money(Number(state.user.balance) - total)} MMK`,
                ],
            ]
                .map(
                    ([label, value]) =>
                        `<div><span>${esc(label)}</span><strong>${esc(value)}</strong></div>`,
                )
                .join(
                    "",
                )}</section>${cart.rows.map((row) => `<div class="cart-row read-only"><strong>${esc(row.number)}</strong><span>${money(row.amount)} MMK</span></div>`).join("")}${errorBox()}</div>`,
        ),
    );
    const footer = document.createElement("div");
    footer.className = "purchase-footer";
    footer.innerHTML = button(
        t("confirm"),
        "purchase",
        "primary wide",
        "data-submit",
    );
    view.element.append(footer);
    on(view.element, "click", '[data-action="purchase"]', () =>
        submit(view.element, async () => {
            if (cart.completed) return;
            if (
                drawClosed(
                    cart.type,
                    cart.clock,
                    dateParts(),
                    !!typeInfo(cart.type)?.open,
                )
            )
                throw new Error(t("lottery_close_for_this_hour"));
            const data = {
                lottery_type_id: cart.type,
                clock_id: cart.clock.id,
                numberJSON: JSON.stringify(
                    cart.rows.map((row) => ({
                        id: Number(row.id),
                        number: String(row.number),
                        amount: Number(row.amount),
                        allowed_amount: Number(row.allowed_amount),
                    })),
                ),
            };
            const result = await api("vouchers/store", {
                method: "POST",
                data,
                requestId: mutationId(view.element, data),
            });
            cart.completed = true;
            state.purchaseErrors = result.error_numbers || [];
            state.views.delete("#/confirm");
            state.views.delete("#/review");
            await refreshUser().catch(() => {});
            navigate(
                state.purchaseErrors.length ? "purchase-errors" : "vouchers",
                { type: cart.type },
                true,
            );
        }),
    );
    return view;
}

function purchaseErrors(params) {
    return screen(
        page(
            "MasterGyi",
            t("status"),
            `<div class="page"><p class="notice">ထီထိုးခြင်း အောင်မြင်ပါသည်။ အောက်ပါဂဏန်းများမှာ လက်ကျန်ပမာဏ မလုံလောက်သဖြင့် ထိုးခြင်းမပြုနိုင်ခဲ့ပါ။</p><div class="table-scroll" data-preserve-scroll><table class="voucher-table" style="width:100%"><thead><tr><th>${esc(t("number"))}</th><th>${esc(t("amount"))}</th><th>ထိုးနိုင်သည့်ပမာဏ</th></tr></thead><tbody>${(state.purchaseErrors || []).map((x) => `<tr><td>${esc(x.number)}</td><td>${money(x.amount)}</td><td>${money(x.allowed_amount)}</td></tr>`).join("")}</tbody></table></div>${button(t("my_lottery_history"), "vouchers", "primary wide")}</div>`,
        ),
    );
}

function vouchers(params) {
    const type = Number(params.type) === 3 ? 3 : 2;
    let filter = 2,
        next = 1,
        busy = false,
        generation = 0,
        seen = new Set();
    const view = screen(
        page(
            `MM ${type}D`,
            t("my_lottery_history"),
            `<div class="page"><div class="filter-chips" role="group" aria-label="${esc(t("status"))}">${[
                [2, "soon"],
                [3, "win"],
                [4, "fail"],
                [1, "all"],
            ]
                .map(
                    ([status, key]) =>
                        `<button class="chip ${status === 2 ? "active" : ""}" data-filter="${status}" aria-pressed="${status === 2}">${esc(key === "all" ? "အားလုံး" : t(key))}</button>`,
                )
                .join(
                    "",
                )}</div><div class="table-scroll"><table class="voucher-table"><colgroup><col style="width:110px"><col style="width:100px"><col style="width:90px"><col style="width:100px"><col style="width:150px"></colgroup><thead><tr>${["date", "hour", "number", "amount", "status"].map((x) => `<th>${esc(t(x))}</th>`).join("")}</tr></thead><tbody></tbody></table></div><div data-loading>${loading()}</div>${button("နောက်ထပ် မှတ်တမ်းများ", "more", "text wide")}${errorBox()}</div>`,
        ),
        async (signal) => {
            next = 1;
            generation++;
            seen.clear();
            $(view.element, "tbody").innerHTML = "";
            await load(signal);
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((x) => x.isIntersecting) && next && !busy)
                    load(signal).catch((error) =>
                        setError(view.element, error.message),
                    );
            });
            observer.observe($(view.element, '[data-action="more"]'));
            signal.addEventListener("abort", () => observer.disconnect(), {
                once: true,
            });
        },
    );
    async function load(signal) {
        if (busy || !next) return;
        busy = true;
        const version = generation;
        try {
            const data = await api(
                `user/vouchers?lottery_type_id=${type}&status=${filter}&page=${next}`,
                { signal },
            );
            if (signal?.aborted || version !== generation) return;
            for (const row of data.data || []) {
                if (seen.has(row.id)) continue;
                seen.add(row.id);
                const win = Number(row.win) || Number(row.bonus_win);
                const verified = Number(row.verified);
                const status = win
                    ? Number(row.bonus_win)
                        ? "အပိုဆုထီပေါက်"
                        : t("win")
                    : verified
                      ? t("fail")
                      : t("soon");
                $(view.element, "tbody").insertAdjacentHTML(
                    "beforeend",
                    `<tr><td>${esc(row.created_at?.slice(0, 10))}</td><td>${esc(clockLabel(row.clock))}</td><td>${esc(row.number)}</td><td>${money(row.amount)}</td><td class="${win ? (verified ? "income" : "pending") : verified ? "expense" : ""}">${esc(status)}</td></tr>`,
                );
            }
            next = data.next_page_url
                ? Number(
                      new URL(data.next_page_url, config.base).searchParams.get(
                          "page",
                      ),
                  )
                : null;
            $(view.element, "[data-loading]").innerHTML = seen.size
                ? ""
                : empty();
            $(view.element, '[data-action="more"]').hidden = !next;
        } finally {
            busy = false;
        }
    }
    on(view.element, "click", "[data-filter]", (e, target) => {
        if (busy) return;
        filter = Number(target.dataset.filter);
        generation++;
        next = 1;
        seen.clear();
        $(view.element, "tbody").innerHTML = "";
        $$(view.element, "[data-filter]").forEach((x) => {
            x.classList.toggle("active", x === target);
            x.setAttribute("aria-pressed", String(x === target));
        });
        load(view.controller?.signal).catch((error) =>
            setError(view.element, error.message),
        );
    });
    on(view.element, "click", '[data-action="more"]', () =>
        load(view.controller?.signal).catch((error) =>
            setError(view.element, error.message),
        ),
    );
    return view;
}

export const bettingScreens = {
    lottery,
    calendar,
    buy,
    confirm,
    review,
    vouchers,
    "purchase-errors": (params) => {
        const view = purchaseErrors(params);
        on(view.element, "click", '[data-action="vouchers"]', () =>
            navigate("vouchers", { type: params.type || 2 }, true),
        );
        return view;
    },
};
