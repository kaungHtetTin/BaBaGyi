import {
    $,
    $$,
    state,
    config,
    t,
    nativeText,
    esc,
    money,
    amount,
    icon,
    logo,
    media,
    avatar,
    field,
    button,
    errorBox,
    loading,
    empty,
    on,
    setError,
    toast,
    copy,
    api,
    refreshUser,
    submit,
    formData,
    mutationId,
    navigate,
    back,
    clearPrivateViews,
    screen,
    page,
    authPage,
    actionTiles,
    menuRow,
    openDialog,
    closeDialog,
    dateLabel,
} from "./core.js";

function login() {
    const view = screen(
        authPage(
            `<form class="auth-card"><h1>${esc(t("ui_login_title"))}</h1><p class="caption">${esc(t("ui_account_intro"))}</p><div class="auth-fields">${field("phone", t("enter_your_phone"), "tel", "", 'required autocomplete="tel" inputmode="tel"')}${field("password", t("enter_your_password"), "password", "", 'required autocomplete="current-password"')}</div><a href="#/forgot" class="forgot-link" data-action="forgot">${esc(t("forget_your_password"))}</a>${errorBox()}<button class="button primary submit-button" type="submit">${esc(t("log_in"))}</button><div class="auth-register"><p>${esc(t("don_t_have_an_account"))}</p>${button(t("register"), "register", "text")}</div></form>`,
        ),
    );
    const form = $(view.element, "form");
    on(view.element, "click", '[data-action="forgot"]', (e) => {
        e.preventDefault();
        navigate("forgot");
    });
    on(view.element, "click", '[data-action="register"]', () =>
        navigate("register"),
    );
    form.onsubmit = (e) => {
        e.preventDefault();
        submit(form, async () => {
            const result = await api("login", {
                method: "POST",
                data: formData(form),
            });
            state.user = result.user;
            form.reset();
            clearPrivateViews();
            navigate("home", {}, true);
        });
    };
    return view;
}

function register() {
    const view = screen(
        authPage(
            `<section class="auth-card"><h1>${esc(t("ui_signup_title"))}</h1><p class="caption">${esc(t("ui_signup_intro"))}</p><form style="margin-top:24px"><div class="steps">${[1, 2, 3, 4].map((n) => `<span data-step="${n - 1}">${esc(t(`_${n}`))}</span>`).join("")}</div><p class="register-prompt"></p><div class="register-input"></div>${errorBox()}<button type="submit" class="button primary submit-button">${esc(t("ui_next"))}</button></form></section>`,
        ),
    );
    const form = $(view.element, "form");
    let step = 0;
    const data = {};
    const fields = [
        ["name", t("please_enter_your_name"), "text", "name"],
        ["phone", t("enter_your_phone"), "tel", "tel"],
        ["password", t("enter_your_password"), "password", "new-password"],
        [
            "confirmation",
            "လျှို့ဝှက်နံပါတ်အား အတည်ပြုဖြည့်သွင်းပါ",
            "password",
            "new-password",
        ],
    ];
    function draw() {
        const keepFocus = form.contains(document.activeElement);
        $$(form, "[data-step]").forEach((el) => {
            el.className =
                Number(el.dataset.step) < step
                    ? "complete"
                    : Number(el.dataset.step) === step
                      ? "active"
                      : "";
        });
        $(form, ".register-prompt").textContent =
            step < 4 ? fields[step][1] : "အကောင့်မှတ်ပုံတင်ရန် အသင့်ဖြစ်ပါပြီ";
        $(form, ".register-input").innerHTML =
            step < 4
                ? field(
                      fields[step][0],
                      fields[step][1],
                      fields[step][2],
                      data[fields[step][0]] || "",
                      `required autocomplete="${fields[step][3]}"`,
                  )
                : "";
        $(form, 'button[type="submit"]').textContent =
            step < 4 ? t("ui_next") : "မှတ်ပုံတင်ပါ";
        setError(form, "");
        if (keepFocus) $(form, "input")?.focus({ preventScroll: true });
    }
    on(form, "click", '[data-action="previous"]', () => {
        if (step > 0) {
            step--;
            draw();
        } else back();
    });
    form.onsubmit = (e) => {
        e.preventDefault();
        submit(form, async () => {
            if (step === 4) {
                const result = await api("register", {
                    method: "POST",
                    data: {
                        name: data.name,
                        phone: data.phone,
                        password: data.password,
                    },
                });
                state.user = result.user;
                clearPrivateViews();
                navigate("home", {}, true);
                return;
            }
            const raw = $(form, "input").value;
            const value = step >= 2 ? raw : raw.trim();
            if (!value) throw new Error(fields[step][1]);
            if (step === 1) {
                const exists = await api("check-user", {
                    method: "POST",
                    data: { phone: value },
                });
                if (exists.user_exist)
                    throw new Error(
                        "ဤဖုန်းနံပါတ်ဖြင့် အကောင့်ရှိပြီးဖြစ်ပါသည်",
                    );
            }
            if (step === 2 && value.length < 5)
                throw new Error("လျှို့ဝှက်နံပါတ် အနည်းဆုံး ၅ လုံး ဖြည့်ပါ");
            if (step === 3 && value !== data.password)
                throw new Error("လျှို့ဝှက်နံပါတ် မတူညီပါ");
            data[fields[step][0]] = value;
            step++;
            draw();
        });
    };
    draw();
    return view;
}

function recoveryHint() {
    if (state.hintShown || state.user?.recovery_hint || state.modal) return;
    state.hintShown = true;
    const texts = [];
    const walk = (node) => {
        if (node.tag === "TextView" && node.attrs.text)
            texts.push(node.attrs.text);
        node.children.forEach(walk);
    };
    walk(state.resources.layouts.dialog_recovery_hint);
    openDialog(
        `<h2>${esc(texts.shift() || "Password Recovery Hint")}</h2>${texts.map((x) => `<p>${esc(x)}</p>`).join("")}<form>${field("recovery_hint", "မိမိကြိုက်နှစ်သက်ရာစကားလုံး", "text", "", 'required maxlength="255" autocomplete="off"')}${errorBox()}<button class="button primary wide" type="submit" style="margin-top:20px">${esc(t("confirm_now"))}</button></form>`,
        (root) => {
            $(root, "form").onsubmit = (e) => {
                e.preventDefault();
                submit(root, async () => {
                    const result = await api(
                        "user/update-password-recovery-hint",
                        { method: "POST", data: formData(e.target) },
                    );
                    state.user = result.user;
                    closeDialog();
                });
            };
        },
    );
}

function homeBody(data) {
    const types = data?.lottery_types || [];
    const coefficient = (id) =>
        types.find((x) => Number(x.id) === id)?.coefficient ??
        types.find((x) => Number(x.id) === id)?.coe;
    return `<h1 class="greeting">${esc(state.user?.name ? t("home_welcome_name").replace("%1$s", state.user.name) : t("home_welcome"))}</h1><p class="body-copy">${esc(t("home_intro"))}</p><section class="wallet-card"><div class="wallet-heading">${icon("ic_wallet_white")}<span>${esc(t("home_wallet_label"))}</span><span class="currency">MMK</span></div><div class="wallet-balance" data-balance>${esc(amount())}</div><div class="wallet-actions">${[
        ["topup", "top_up", "ic_home_topup"],
        ["withdraw", "withdraw", "ic_home_withdraw"],
        ["wallet-history", "history", "ic_home_history"],
    ]
        .map(
            ([nav, label, img]) =>
                `<button data-nav="${nav}">${icon(img)}<span>${esc(t(label))}</span></button>`,
        )
        .join(
            "",
        )}</div></section>${data?.notices?.length ? `<div class="notice notice-row">${icon("ic_home_notice")}<span>${esc(data.notices.map((x) => x.notice || x.content || x.message || x.title || "").join(" · "))}</span></div>` : ""}<div data-loading>${data ? "" : `<p class="body-copy" role="status">${esc(t("home_loading"))}</p>`}</div><h2 class="section-title home-section">${esc(t("home_services"))}</h2><p class="body-copy">${esc(t("home_choose_service"))}</p><div class="services">${[2, 3].map((id) => `<button class="service-card" data-nav="lottery?type=${id}"><span class="service-badge">${id}D</span><strong>MM ${id}D</strong><span class="body-copy">${esc(t(id === 2 ? "home_two_digit" : "home_three_digit"))}</span><span class="rate">${coefficient(id) ? esc(t("home_rate_format").replace("%1$d", coefficient(id))) : "—"}</span><span class="service-action"><span>ကစားရန်</span>${icon("ic_home_arrow")}</span></button>`).join("")}</div>${data?.ads?.length ? `<h2 class="section-title">${esc(t("home_ads"))}</h2><div class="hero-ads">${data.ads.map((x) => `<img src="${esc(media(x.url || x.photo_url))}" alt="${esc(t("home_ad_description"))}" loading="lazy">`).join("")}</div>` : ""}<button class="surface support-row" data-nav="contacts">${icon("ic_home_support")}<span><strong>${esc(t("home_support_title"))}</strong><p>${esc(t("home_support_body"))}</p></span>${icon("ic_home_arrow")}</button>`;
}
function home() {
    const view = screen(
        page(
            "MasterGyi",
            t("explore"),
            `<div class="page" data-home>${homeBody(state.home)}</div>`,
            { main: true, tab: "home" },
        ),
        async (signal) => {
            const data = await api("notices", { signal });
            if (signal.aborted) return;
            if (!data?.user || !Array.isArray(data.lottery_types))
                throw new Error(t("home_load_error"));
            state.home = data;
            state.user = data.user;
            $(view.element, "[data-home]").innerHTML = homeBody(data);
            recoveryHint();
            const ads = $(view.element, ".hero-ads");
            if (ads && data.ads.length > 1) {
                let ad = 0;
                const timer = setInterval(() => {
                    ad = (ad + 1) % data.ads.length;
                    ads.scrollTo({
                        left: (ads.clientWidth + 12) * ad,
                        behavior: matchMedia("(prefers-reduced-motion:reduce)")
                            .matches
                            ? "instant"
                            : "smooth",
                    });
                }, 10000);
                signal.addEventListener("abort", () => clearInterval(timer), {
                    once: true,
                });
            }
        },
    );
    return view;
}

function wallet() {
    const body = () =>
        `<div class="page"><h1 class="title">${esc(t("pocket"))}</h1><p class="caption">${esc(t("ui_wallet_intro"))}</p><section class="wallet-card pocket-card"><div class="profile-line">${avatar(52)}<div><strong>${esc(state.user?.name)}</strong><p>${esc(state.user?.phone)}</p></div></div><div class="wallet-label">${esc(t("my_balance"))}</div><div class="wallet-balance" data-balance>${esc(amount())}</div></section><div class="pocket-actions">${actionTiles(
            [
                ["topup", "top_up", "ic_home_topup"],
                ["withdraw", "withdraw", "ic_home_withdraw"],
                ["wallet-history", "history", "ic_home_history"],
            ],
        )}</div><section class="surface hours"><h2>${esc(t("top_up_withdraw_service_hour"))}</h2><hr><h3>${esc(t("top_up"))}</h3><p class="caption">${esc(t("daily_08_00_am_to_08_00_pm"))}</p><h3>${esc(t("withdraw"))}</h3><p class="caption">${esc(t("daily_12_01_pm_to_08_00_pm"))}</p></section><h2 class="section-title">${esc(t("faq"))}</h2>${menuRow("how_to_top_up", "guide?kind=topup", "ic_visit_to_web")}${menuRow("how_to_withdraw", "guide?kind=withdraw", "ic_visit_to_web")}${errorBox()}</div>`;
    const view = screen(
        page("MasterGyi", t("pocket"), body(), { main: true, tab: "wallet" }),
        async (signal) => {
            await refreshUser(signal);
            if (!signal.aborted) $(view.element, "main").innerHTML = body();
        },
    );
    return view;
}

function contacts(params = {}, forgot = false) {
    const main = !!state.user && !forgot;
    const view = screen(
        page(
            forgot ? t("password") : "MasterGyi",
            forgot ? "အသစ်တောင်းခံမည်" : t("contact"),
            `<div class="page">${forgot ? `<p class="surface">${esc(nativeText("activity_forget_password", "textView4"))}</p>` : `<h1 class="title">${esc(t("contact"))}</h1><p class="caption">${esc(t("ui_support_intro"))}</p>`}<div data-loading style="margin-top:20px">${loading()}</div></div>`,
            { main, tab: "contacts" },
        ),
        async (signal) => {
            const rows = await api("contacts", { signal });
            if (signal.aborted) return;
            $(view.element, "[data-loading]").innerHTML = rows.length
                ? rows
                      .map(
                          (x) =>
                              `<article class="surface contact-row" data-copy="${esc(x.contact)}" tabindex="0" role="button" aria-label="${esc(x.service)} ${esc(x.contact)}">${icon("ic_home_support")}<div><small>${esc(x.service)}</small><strong>${esc(x.contact)}</strong></div><button class="copy-button">${esc(t("ui_copy"))}</button></article>`,
                      )
                      .join("")
                : empty();
        },
    );
    on(view.element, "click", "[data-copy]", (e, target) =>
        copy(target.dataset.copy),
    );
    on(view.element, "keydown", "[data-copy]", (e, target) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            copy(target.dataset.copy);
        }
    });
    return view;
}

function settings() {
    const view = screen(
        page(
            "MasterGyi",
            t("setting"),
            `<div class="page"><section class="surface profile-card"><button class="avatar-button" data-nav="profile">${avatar(88)}</button><strong data-name>${esc(state.user?.name)}</strong></section><h2 class="section-title">${esc(t("ui_account_section"))}</h2>${menuRow("profile", "profile", "ic_setting_edit_profile")}${menuRow("password_reset", "password", "ic_setting_reset_password")}<h2 class="section-title">${esc(t("ui_app_section"))}</h2>${menuRow("share_app", "share", "ic_setting_share_app").replace('data-nav="share"', 'data-action="share-app"')}${menuRow("check_update", "update", "ic_setting_check_update")}${menuRow("delete_account", "delete-account", "ic_setting_delete_account", true)}<button class="menu-row" data-action="logout">${icon("ic_menu_sign_out")}<span>${esc(t("log_out"))}</span></button><button class="menu-row" data-action="install">${icon("ic_download_chapter")}<span>MasterGyi ထည့်သွင်းရန်</span></button>${errorBox()}</div>`,
            { main: true, tab: "settings" },
        ),
        async (signal) => {
            await refreshUser(signal);
            $(view.element, "[data-name]").textContent = state.user.name;
        },
    );
    on(view.element, "click", '[data-action="logout"]', () =>
        submit(view.element, async () => {
            await api("logout", { method: "POST", data: {} });
            state.user = null;
            state.hintShown = false;
            clearPrivateViews();
            navigate("login", {}, true);
        }),
    );
    on(view.element, "click", '[data-action="share-app"]', async () => {
        try {
            if (navigator.share)
                await navigator.share({ title: "MasterGyi", url: config.base });
            else await copy(config.base);
        } catch (error) {
            if (error.name !== "AbortError") toast(error.message);
        }
    });
    on(view.element, "click", '[data-action="install"]', async () => {
        if (state.install) {
            await state.install.prompt();
            state.install = null;
        } else toast("Browser menu → Install app / Add to Home Screen");
    });
    return view;
}

function banking(kind) {
    const isTopup = kind === "topup";
    let banks = null,
        bank = null,
        payment = null;
    const view = screen(
        page(
            t(isTopup ? "top_up" : "withdraw"),
            "Request",
            `<div class="form-page"><section class="surface form-card"><div data-bank-select><p>${esc(t("select_banking"))}</p><div data-loading>${loading()}</div></div><form hidden><div data-bank-details></div><div data-fields></div>${errorBox()}<button class="button primary wide" type="submit">${esc(t("confirm_now"))}</button></form></section></div>`,
        ),
        async (signal) => {
            if (banks) return;
            banks = await api("bankings", { signal });
            if (signal.aborted) return;
            $(view.element, "[data-loading]").innerHTML = banks.length
                ? banks
                      .map(
                          (x) =>
                              `<button class="bank-row" data-bank="${x.id}"><span class="radio"></span><img src="${esc(media(x.icon_url))}" alt=""><span>${esc(x.bank)}</span></button>`,
                      )
                      .join("")
                : empty();
        },
    );
    const form = $(view.element, "form");
    on(view.element, "click", "[data-bank]", async (e, target) => {
        if (target.disabled) return;
        target.disabled = true;
        try {
            bank = banks.find((x) => String(x.id) === target.dataset.bank);
            if (isTopup)
                payment = await api(`payment-method?banking_id=${bank.id}`);
            $(view.element, "[data-bank-select]").hidden = true;
            form.hidden = false;
            $(form, "[data-bank-details]").innerHTML = isTopup
                ? `<section class="payment-card"><strong>${esc(t("ui_payment_details"))}</strong><div class="bank-name"><img src="${esc(media(bank.icon_url))}" alt=""><span>${esc(bank.bank)}</span></div><label class="form-label">${esc(t("account_name"))}</label><div class="copy-field">${esc(payment.account_name)}</div><label class="form-label">${esc(t("account_phone_no"))}</label><button type="button" class="copy-field" data-copy="${esc(payment.method)}">${esc(payment.method)} ${icon("round_content_copy_24")}</button></section>`
                : `<div class="profile-card"><img src="${esc(media(bank.icon_url))}" alt="" width="50" height="50"><strong>${esc(bank.bank)}</strong></div>`;
            $(form, "[data-fields]").innerHTML = isTopup
                ? `<div class="payment-inputs"><label class="form-label">${esc(t("amount"))}</label>${field("amount", t("enter_the_amount"), "number", "", 'required min="100" step="1" inputmode="numeric"')}<label class="form-label">လုပ်ဆောင်မှုနံပါတ်စဉ်</label>${field("bank_transaction_id", "နောက်ဆုံးဂဏန်း ၆ လုံး", "text", "", 'required pattern="[0-9]{6}" maxlength="6" inputmode="numeric" autocomplete="off"')}</div>`
                : `${field("account_name", t("enter_account_name"), "text", "", 'required autocomplete="name"')}${field("method", t("enter_account_phone_number"), "tel", "", 'required autocomplete="tel"')}${field("amount", t("enter_the_amount"), "number", "", 'required min="1" step="1" inputmode="numeric"')}`;
        } catch (error) {
            toast(error.message);
        } finally {
            target.disabled = false;
        }
    });
    on(form, "click", "[data-copy]", (e, target) => copy(target.dataset.copy));
    form.onsubmit = (e) => {
        e.preventDefault();
        submit(form, async () => {
            const data = {
                ...formData(form),
                ...(isTopup
                    ? { payment_method_id: payment.id }
                    : { banking_id: bank.id }),
            };
            if (!isTopup && Number(data.amount) > Number(state.user.balance))
                throw new Error(t("insufficient_balance"));
            await api(isTopup ? "top-up" : "withdraw", {
                method: "POST",
                data,
                requestId: mutationId(form, data),
            });
            await refreshUser().catch(() => {});
            state.views.delete(state.current.key);
            toast("တောင်းဆိုမှု ပေးပို့ပြီးပါပြီ");
            back();
        });
    };
    return view;
}

function walletHistory() {
    let next = 1,
        busy = false,
        seen = new Set();
    const view = screen(
        page(
            t("pocket"),
            t("history"),
            `<div class="page"><div data-pending></div><div data-list></div><div data-loading>${loading()}</div>${button("နောက်ထပ် မှတ်တမ်းများ", "more", "text wide")}${errorBox()}</div>`,
        ),
        async (signal) => {
            next = 1;
            seen.clear();
            $(view.element, "[data-list]").innerHTML = "";
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
    const row = (x, pending = false, income = x.income) =>
        `<article class="surface history-row"><div><strong>${esc(x.title || (income ? t("top_up") : t("withdraw")))}</strong><p>${esc(pending ? "အတည်ပြုနေပါသည်" : dateLabel(x.created_at))}</p></div><span class="${pending ? "pending" : Number(income) ? "income" : "expense"}">${Number(income) ? "+" : "−"}${money(x.amount)}</span></article>`;
    async function load(signal) {
        if (busy || !next) return;
        busy = true;
        try {
            const data = await api(`user/wallet-histories?page=${next}`, {
                signal,
            });
            if (signal?.aborted) return;
            if (next === 1)
                $(view.element, "[data-pending]").innerHTML = [
                    ...(data.unverified_transactions || []).map((x) =>
                        row(x, true, 1),
                    ),
                    ...(data.unverified_withdraws || []).map((x) =>
                        row(x, true, 0),
                    ),
                ].join("");
            const page = data.wallet_histories;
            for (const item of page.data || []) {
                if (!seen.has(item.id)) {
                    $(view.element, "[data-list]").insertAdjacentHTML(
                        "beforeend",
                        row(item),
                    );
                    seen.add(item.id);
                }
            }
            next = page.next_page_url
                ? Number(
                      new URL(page.next_page_url, config.base).searchParams.get(
                          "page",
                      ),
                  )
                : null;
            $(view.element, "[data-loading]").innerHTML = "";
            $(view.element, '[data-action="more"]').hidden = !next;
            if (
                !seen.size &&
                !$(view.element, "[data-pending]").children.length
            )
                $(view.element, "[data-list]").innerHTML = empty();
        } finally {
            busy = false;
        }
    }
    on(view.element, "click", '[data-action="more"]', () =>
        load(view.controller?.signal).catch((error) =>
            setError(view.element, error.message),
        ),
    );
    return view;
}

function profile() {
    let selected = state.user.avatar_url;
    let loaded = false;
    const view = screen(
        page(
            t("profile"),
            t("edit"),
            `<div class="form-page"><form class="surface form-card"><div class="profile-card" style="padding-top:30px"><img class="avatar" data-preview src="${esc(media(selected))}" alt="${esc(t("profile"))}" width="100" height="100"><p class="caption" style="margin-top:10px">${esc(t("select_avatar_profile"))}</p></div><div class="avatar-options" data-avatars></div>${field("name", t("please_enter_your_name"), "text", state.user.name, 'required maxlength="255" autocomplete="name"')}${errorBox()}<button class="button primary wide" type="submit">${esc(t("confirm_now"))}</button></form></div>`,
        ),
        async (signal) => {
            if (loaded) return;
            const rows = await api("avatars", { signal });
            if (signal.aborted) return;
            loaded = true;
            $(view.element, "[data-avatars]").innerHTML = rows
                .map(
                    (x) =>
                        `<button type="button" data-avatar="${esc(x.url)}" class="${x.url === selected ? "selected" : ""}" aria-label="${esc(t("select_avatar_profile"))} ${x.id}"><img src="${esc(media(x.url))}" alt=""></button>`,
                )
                .join("");
        },
    );
    on(view.element, "click", "[data-avatar]", (e, target) => {
        selected = target.dataset.avatar;
        $(view.element, "[data-preview]").src = media(selected);
        $$(view.element, "[data-avatar]").forEach((x) =>
            x.classList.toggle("selected", x === target),
        );
    });
    $(view.element, "form").onsubmit = (e) => {
        e.preventDefault();
        submit(e.target, async () => {
            if (!selected) throw new Error(t("please_select_an_avatar_icon"));
            await api("user/update", {
                method: "POST",
                data: { ...formData(e.target), avatar_url: selected },
            });
            await refreshUser();
            state.views.delete(state.current.key);
            back();
        });
    };
    return view;
}

function passwordForm(deleting = false) {
    const view = screen(
        page(
            t(deleting ? "delete_account" : "password"),
            deleting ? "Delete" : t("reset"),
            `<div class="form-page"><form class="surface form-card">${deleting ? `<p style="line-height:1.9">${esc(t("account_delete_information"))}</p>` : ""}<div class="profile-card">${avatar(70)}<strong>${esc(state.user.name)}</strong></div>${deleting ? field("password", t("password"), "password", "", 'required autocomplete="current-password"') : `${field("current_password", t("enter_current_password"), "password", "", 'required autocomplete="current-password"')}${field("new_password", t("enter_new_password"), "password", "", 'required minlength="5" autocomplete="new-password"')}`}${errorBox()}<button class="button ${deleting ? "danger" : "primary"} wide" type="submit">${esc(t("confirm_now"))}</button></form></div>`,
        ),
    );
    $(view.element, "form").onsubmit = (e) => {
        e.preventDefault();
        submit(e.target, async () => {
            await api(deleting ? "delete-account" : "password-reset", {
                method: "POST",
                data: formData(e.target),
            });
            e.target.reset();
            if (deleting) {
                state.user = null;
                clearPrivateViews();
                navigate("login", {}, true);
            } else {
                state.views.delete(state.current.key);
                back();
            }
        });
    };
    return view;
}

function guide(params) {
    return screen(
        page(
            t(params.kind === "withdraw" ? "how_to_withdraw" : "how_to_top_up"),
            "",
            `<iframe class="guide-frame" title="${esc(t("faq"))}" src="${esc(config.guides[params.kind === "withdraw" ? "withdraw" : "topup"])}" sandbox="allow-same-origin"></iframe>`,
        ),
    );
}
function update() {
    const view = screen(
        page(
            "MasterGyi",
            "ဆော့ဝဲလ်အဆင့်မြင့်ခြင်း",
            `<div class="page"><section class="surface update-info"><img src="${logo()}" width="72" height="72" alt=""><div><h1>MasterGyi</h1><p>ဗားရှင်း ${esc(config.version)} · Web</p></div></section><p class="update-message" data-update-message></p><div data-loading>${loading()}</div>${button("ဗားရှင်းအသစ်ရယူမယ်", "update", "primary wide", "hidden data-submit")}${errorBox()}</div>`,
        ),
        async (signal) => {
            const syncUpdate = () => {
                if (signal.aborted) return;
                const ready = Boolean(state.registration?.waiting);
                $(view.element, "[data-update-message]").textContent = ready
                    ? "ဆော့ဝဲလ်ဗားရှင်းအသစ်ထွက်ရှိပါပြီ"
                    : "ဆော့ဝဲလ်အသစ်မထွက်ရှိသေးပါ";
                $(view.element, "[data-action=update]").hidden = !ready;
                $(view.element, "[data-loading]").innerHTML = "";
            };
            const observeUpdate = () => {
                state.registration?.installing?.addEventListener(
                    "statechange",
                    syncUpdate,
                    { signal },
                );
                syncUpdate();
            };
            state.registration?.addEventListener("updatefound", observeUpdate, {
                signal,
            });
            observeUpdate();
            await state.registration?.update();
            observeUpdate();
        },
    );
    on(view.element, "click", "[data-action=update]", () =>
        submit(view.element, async () => {
            if (!state.registration?.waiting) return;
            navigator.serviceWorker.addEventListener(
                "controllerchange",
                () => location.reload(),
                { once: true },
            );
            state.registration.waiting.postMessage("ACTIVATE_UPDATE");
        }),
    );
    return view;
}
function share() {
    const view = screen(
        page(
            t("share_app"),
            "",
            `<div class="page"><section class="surface profile-card"><img src="${logo()}" width="80" height="80" alt=""><h1 class="title">MasterGyi</h1><p class="body-copy">${esc(config.base)}</p>${button(t("share_app"), "share", "primary wide")}${button(t("ui_copy"), "copy", "text wide")}</section></div>`,
        ),
    );
    on(view.element, "click", '[data-action="share"]', async () => {
        try {
            if (navigator.share)
                await navigator.share({ title: "MasterGyi", url: config.base });
            else await copy(config.base);
        } catch (error) {
            if (error.name !== "AbortError") toast(error.message);
        }
    });
    on(view.element, "click", '[data-action="copy"]', () => copy(config.base));
    return view;
}
function disabled() {
    return screen(
        page(
            "MasterGyi",
            "Disable User",
            `<div class="disabled-container">${icon("baseline_do_disturb_24", "disabled-icon")}<p class="surface">သင်၏အကောင့်ကို Admin မှ ပိတ်သိမ်းထားပါသည်</p></div>`,
        ),
    );
}

export const screens = {
    login,
    register,
    home,
    wallet,
    contacts,
    settings,
    forgot: () => contacts({}, true),
    topup: () => banking("topup"),
    withdraw: () => banking("withdraw"),
    "wallet-history": walletHistory,
    profile,
    password: () => passwordForm(false),
    "delete-account": () => passwordForm(true),
    guide,
    update,
    share,
    disabled,
};
