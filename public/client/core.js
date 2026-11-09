export const config = window.CLIENT_CONFIG;
export const state = {
    resources: null,
    user: null,
    csrf: "",
    home: null,
    views: new Map(),
    screens: {},
    active: null,
    install: null,
    registration: null,
};
export const $ = (root, selector) => root.querySelector(selector);
export const $$ = (root, selector) => [...root.querySelectorAll(selector)];
export const esc = (value) =>
    String(value ?? "").replace(
        /[&<>"']/g,
        (char) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[char],
    );
export const t = (key) => state.resources?.strings[key] ?? key;
export function nativeText(layout, id) {
    let value = "";
    const walk = (node) => {
        if (node.attrs?.id?.split("/").at(-1) === id)
            value = node.attrs.text || "";
        node.children?.forEach(walk);
    };
    walk(state.resources.layouts[layout]);
    return value.startsWith("@string/") ? t(value.slice(8)) : value;
}
export const money = (value) =>
    Number.isFinite(Number(value))
        ? new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 }).format(
              Number(value),
          )
        : "—";
export const amount = () =>
    state.user ? `${money(state.user.balance)} ကျပ်` : "—";
export const icon = (name, cls = "") => {
    // SVGs are generated from the trusted native vectors; tint follows the XML parent.
    const svg = (state.resources?.icons?.[name] || "").replace(
        /(fill|stroke)="#[0-9a-f]{6,8}"/gi,
        (attribute) =>
            /[0-9a-f]{6}00"$/i.test(attribute)
                ? attribute.replace(/"[^"]+"/, '"none"')
                : attribute.replace(/"[^"]+"/, '"currentColor"'),
    );
    return `<span class="icon ${cls}" aria-hidden="true">${svg}</span>`;
};
export const logo = () => `${config.assets}images/mastergyi_logo.webp`;
export const media = (value) => {
    if (!value) return `${config.assets}images/mastergyi_avatar.webp`;
    if (/^https?:\/\//i.test(value)) return value;
    return new URL(
        `storage/${String(value).replace(/^\/+|^app\/public\//g, "")}`,
        new URL("../", config.base),
    ).href;
};
export const avatar = (size = 48) =>
    `<img class="avatar" src="${esc(media(state.user?.avatar_url))}" alt="${esc(t("profile"))}" width="${size}" height="${size}">`;
export const field = (name, hint, type = "text", value = "", attrs = "") =>
    `<input class="input" name="${name}" type="${type}" placeholder="${esc(hint)}" aria-label="${esc(hint)}" value="${esc(value)}" ${attrs}>`;
export const button = (label, action, cls = "primary", attrs = "") =>
    `<button class="button ${cls}" ${action ? `data-action="${action}"` : ""} ${attrs}>${esc(label)}</button>`;
export const errorBox = () =>
    '<div class="error-box" role="alert" hidden></div>';
export const loading = () =>
    `<div class="loading"><span class="spinner" role="status" aria-label="${esc(t("home_loading"))}"></span></div>`;
export const empty = (label = "မှတ်တမ်းမရှိသေးပါ") =>
    `<p class="empty">${esc(label)}</p>`;
export const dateParts = () =>
    Object.fromEntries(
        new Intl.DateTimeFormat("en-CA", {
            timeZone: "Asia/Yangon",
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            hourCycle: "h23",
            weekday: "short",
        })
            .formatToParts(new Date())
            .map((x) => [x.type, x.value]),
    );
export const today = () => {
    const p = dateParts();
    return `${p.year}-${p.month}-${p.day}`;
};
export const clockLabel = (clock) =>
    clock
        ? `${String(clock.hour).padStart(2, "0")}:${String(clock.minute).padStart(2, "0")} ${Number(clock.morning) ? "AM" : "PM"}`
        : "—";
export const dateLabel = (value) =>
    value
        ? new Intl.DateTimeFormat("en-GB", {
              timeZone: "Asia/Yangon",
              dateStyle: "medium",
              timeStyle: "short",
          }).format(
              new Date(
                  /Z|[+-]\d\d:\d\d$/.test(value)
                      ? value
                      : `${value.replace(" ", "T")}+06:30`,
              ),
          )
        : "—";
export function on(root, event, selector, handler) {
    root.addEventListener(event, (e) => {
        const target = e.target.closest(selector);
        if (target && root.contains(target)) handler(e, target);
    });
}
export function setError(root, message) {
    const box = $(root, ".error-box");
    if (box) {
        box.textContent = message;
        box.hidden = !message;
    } else if (message) toast(message);
}
export function toast(message) {
    const root = document.getElementById("toast-root");
    root.textContent = message;
    root.classList.add("visible");
    clearTimeout(state.toastTimer);
    state.toastTimer = setTimeout(() => root.classList.remove("visible"), 3500);
}
export async function copy(value) {
    try {
        await navigator.clipboard.writeText(value);
        toast("ကူးယူပြီးပါပြီ");
    } catch {
        openDialog(
            `<h2>${esc(t("ui_copy"))}</h2>${field("copy", t("ui_copy"), "text", value, "readonly")}<p>${esc(value)}</p>`,
        );
    }
}
export async function api(
    endpoint,
    { method = "GET", data, signal, requestId, csrfRetry = false } = {},
) {
    if (!navigator.onLine)
        throw new Error("အင်တာနက်ချိတ်ဆက်မှုကို စစ်ဆေးပြီး ပြန်ကြိုးစားပါ။");
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    };
    let body;
    if (method !== "GET") {
        headers["Content-Type"] = "application/json";
        headers["X-CSRF-TOKEN"] = state.csrf;
        body = JSON.stringify({
            ...data,
            ...(requestId ? { request_id: requestId } : {}),
        });
    }
    const timeout = AbortSignal.timeout
        ? AbortSignal.timeout(20000)
        : undefined;
    const response = await fetch(`${config.api}${endpoint}`, {
        method,
        headers,
        body,
        credentials: "same-origin",
        cache: "no-store",
        signal:
            signal && timeout && AbortSignal.any
                ? AbortSignal.any([signal, timeout])
                : signal || timeout,
    });
    if (response.status === 419 && !csrfRetry) {
        const session = await fetch(config.session, {
            credentials: "same-origin",
            cache: "no-store",
        }).then((r) => r.json());
        state.csrf = session.csrf;
        return api(endpoint, {
            method,
            data,
            signal,
            requestId,
            csrfRetry: true,
        });
    }
    let result;
    try {
        result = await response.json();
        if (typeof result === "string") result = JSON.parse(result);
    } catch {
        throw new Error(t("home_load_error"));
    }
    if (response.status === 401) {
        state.user = null;
        clearPrivateViews();
        navigate("login", {}, true);
        throw new Error("အကောင့်ပြန်လည်ဝင်ရောက်ပါ");
    }
    if (result.disabled) {
        navigate("disabled", {}, true);
        throw new Error(result.message);
    }
    if (!response.ok)
        throw new Error(
            Object.values(result.errors ?? {}).flat()[0] ||
                result.message ||
                t("home_load_error"),
        );
    if (result.status === "fail")
        throw new Error(result.error || result.msg || t("home_load_error"));
    return result;
}
export async function refreshUser(signal) {
    const data = await api("user", { signal });
    state.user = data.user;
    updateBalances();
    return data.user;
}
export function updateBalances() {
    document
        .querySelectorAll("[data-balance]")
        .forEach(
            (x) =>
                (x.textContent =
                    x.dataset.balance === "raw"
                        ? String(state.user?.balance ?? "—")
                        : amount()),
        );
}
export async function submit(root, task) {
    if (root.dataset.busy === "true") return;
    root.dataset.busy = "true";
    setError(root, "");
    const controls = $$(root, 'button[type="submit"], [data-submit]');
    controls.forEach((x) => (x.disabled = true));
    try {
        await task();
    } catch (error) {
        if (error.name !== "AbortError") setError(root, error.message);
    } finally {
        root.dataset.busy = "false";
        controls.forEach((x) => (x.disabled = false));
    }
}
export const formData = (form) => Object.fromEntries(new FormData(form));
export function mutationId(form, data) {
    const body = JSON.stringify(data);
    if (form._requestBody !== body) {
        form._requestBody = body;
        form._requestId = crypto.randomUUID();
    }
    return form._requestId;
}
export function routeInfo() {
    const [name, query] = (
        location.hash.slice(2) || (state.user ? "home" : "login")
    ).split("?");
    return {
        name,
        params: Object.fromEntries(new URLSearchParams(query)),
        key: location.hash || `#/${name}`,
    };
}
export function navigate(name, params = {}, replace = false) {
    const query = new URLSearchParams(params).toString();
    const hash = `#/${name}${query ? `?${query}` : ""}`;
    if (replace) {
        history.replaceState({ client: true }, "", hash);
        render();
    } else if (location.hash !== hash) {
        history.pushState({ client: true }, "", hash);
        render();
    }
}
export function replaceParams(params) {
    const previous = state.current.key;
    const next = { ...state.current.params, ...params };
    const key = `#/${state.current.name}?${new URLSearchParams(next)}`;
    state.views.delete(previous);
    state.views.set(key, state.active);
    state.current = { ...state.current, params: next, key };
    history.replaceState({ ...history.state, client: true }, "", key);
}
export function back() {
    if (state.modal) return closeDialog();
    if (history.state?.client && state.visits > 1) history.back();
    else navigate(state.user ? "home" : "login", {}, true);
}
export function clearPrivateViews() {
    state.active?.controller?.abort();
    state.views.clear();
    state.home = null;
    state.cart = null;
}
export function screen(html, enter = null) {
    const element = document.createElement("div");
    element.className = "screen";
    element.innerHTML = html;
    return { element, enter };
}
export function header(title, subtitle = "", main = false) {
    return `<header class="app-bar ${main ? "main-bar" : ""}">${main ? "" : `<button class="icon-button back" data-nav-back aria-label="${esc(t("ui_back"))}">${icon("ic_ui_back")}</button>`}<div class="app-bar-title"><strong>${esc(title)}</strong><span>${esc(subtitle)}</span></div>${state.user ? `<button class="balance-pill" data-nav="topup">${icon("ic_wallet_white")}<span data-balance="raw">${esc(state.user?.balance ?? "—")}</span></button>` : `<button class="header-login" data-nav="login">${esc(t("log_in"))}</button>`}<button class="avatar-button" data-nav="profile" aria-label="${esc(t("profile"))}">${avatar()}</button></header>`;
}
export function navigation(name) {
    return `<nav class="bottom-nav" aria-label="Main navigation">${[
        ["home", "explore", "ic_home_nav"],
        ["wallet", "pocket", "navbar_pocket_default"],
        ["contacts", "contact", "ic_home_support"],
        ["settings", "setting", "ic_home_settings"],
    ]
        .map(
            ([route, label, img]) =>
                `<button data-nav="${route}" ${route === name ? 'class="active" aria-current="page"' : ""}><span>${icon(img)}</span><small>${esc(t(label))}</small></button>`,
        )
        .join("")}</nav>`;
}
export function page(title, subtitle, body, options = {}) {
    return `${header(title, subtitle, options.main)}<main class="screen-scroll ${options.className || ""}" tabindex="-1">${body}</main>${options.main ? navigation(options.tab) : ""}<div class="offline-notice" ${navigator.onLine ? "hidden" : ""}>အင်တာနက်ချိတ်ဆက်မှု မရှိပါ</div>`;
}
export function authPage(body) {
    return `<main class="auth-scroll"><section class="auth-hero"><div><img src="${logo()}" alt="" width="64" height="64"><strong>MasterGyi</strong></div><p>${esc(t("ui_account_intro"))}</p></section>${body}</main><div class="offline-notice" ${navigator.onLine ? "hidden" : ""}>အင်တာနက်ချိတ်ဆက်မှု မရှိပါ</div>`;
}
export function actionTiles(items) {
    return `<div class="action-tiles">${items.map(([nav, label, img]) => `<button data-nav="${nav}">${icon(img)}<span>${esc(t(label))}</span></button>`).join("")}</div>`;
}
export function menuRow(label, nav, img, danger = false) {
    return `<button class="menu-row ${danger ? "danger-text" : ""}" data-nav="${nav}">${icon(img)}<span>${esc(t(label))}</span></button>`;
}
export function openDialog(html, setup, { dismissible = true } = {}) {
    if (state.modal) return;
    const root = document.getElementById("dialog-root");
    state.modalFocus = document.activeElement;
    root.innerHTML = `<div class="dialog-backdrop"><section class="dialog" role="dialog" aria-modal="true" aria-label="MasterGyi"><button class="dialog-close icon-button" data-close aria-label="${esc(t("ui_close"))}">×</button>${html}</section></div>`;
    const element = $(root, ".dialog");
    state.modal = { element, dismissible };
    history.pushState({ client: true, dialog: true }, "", location.href);
    on(root, "click", "[data-close]", () => {
        if (dismissible) closeDialog();
    });
    root.onclick = (e) => {
        if (e.target.classList.contains("dialog-backdrop") && dismissible)
            closeDialog();
    };
    root.onkeydown = (e) => {
        if (e.key === "Escape" && dismissible) {
            e.preventDefault();
            closeDialog();
        }
        if (e.key === "Tab") {
            const focusable = $$(
                element,
                "button:not(:disabled),input:not(:disabled),select,a[href]",
            );
            const first = focusable[0],
                last = focusable.at(-1);
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last?.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first?.focus();
            }
        }
    };
    setup?.(element);
    $(element, "input,button")?.focus();
}
export function closeDialog(next) {
    if (!state.modal) return next?.();
    state.afterModal = next;
    history.back();
}
export async function render() {
    const root = document.getElementById("client-root");
    state.active?.controller?.abort();
    if (state.active)
        state.active.scrolls = $$(
            state.active.element,
            ".screen-scroll,.auth-scroll,[data-preserve-scroll]",
        ).map((el) => ({ top: el.scrollTop, left: el.scrollLeft }));
    const route = routeInfo();
    if (!state.screens[route.name])
        return navigate(state.user ? "home" : "login", {}, true);
    const publicRoutes = ["login", "register", "forgot", "contacts"];
    if (!state.user && !publicRoutes.includes(route.name))
        return navigate("login", {}, true);
    if (
        Number(state.user?.disable) &&
        !["disabled", "contacts", "forgot"].includes(route.name)
    )
        return navigate("disabled", {}, true);
    let view = state.views.get(route.key);
    const fresh = !view;
    if (!view) {
        view = state.screens[route.name](route.params);
        state.views.set(route.key, view);
    }
    if (fresh) view.scrolls = history.state?.scrolls;
    state.active = view;
    state.current = route;
    state.visits = (state.visits || 0) + 1;
    root.replaceChildren(view.element);
    root.setAttribute("aria-busy", "false");
    $$(
        view.element,
        ".screen-scroll,.auth-scroll,[data-preserve-scroll]",
    ).forEach((el, index) =>
        el.scrollTo(
            view.scrolls?.[index]?.left || 0,
            view.scrolls?.[index]?.top || 0,
        ),
    );
    view.controller = new AbortController();
    document.title = "MasterGyi";
    try {
        await view.enter?.(view.controller.signal);
    } catch (error) {
        if (error.name !== "AbortError" && state.active === view) {
            const loading = $(view.element, "[data-loading]");
            if (loading)
                loading.innerHTML = `<div role="status"><p class="body-copy">${esc(error.message)}</p>${button(t("home_retry"), "retry", "text")}</div>`;
            else setError(view.element, error.message);
        }
    }
    if (fresh && view.scrolls && state.active === view)
        $$(
            view.element,
            ".screen-scroll,.auth-scroll,[data-preserve-scroll]",
        ).forEach((el, index) =>
            el.scrollTo(
                view.scrolls[index]?.left || 0,
                view.scrolls[index]?.top || 0,
            ),
        );
    updateBalances();
}
window.addEventListener("popstate", () => {
    if (state.modal) {
        document.getElementById("dialog-root").replaceChildren();
        state.modal = null;
        state.modalFocus?.focus();
        const next = state.afterModal;
        state.afterModal = null;
        next?.();
    } else render();
});
document.addEventListener("click", (e) => {
    const backButton = e.target.closest("[data-nav-back]");
    if (backButton) return back();
    const nav = e.target.closest("[data-nav]");
    if (nav) navigate(nav.dataset.nav);
    if (e.target.closest('[data-action="retry"]')) render();
});
document.addEventListener(
    "scroll",
    (event) => {
        if (
            !state.active ||
            state.modal ||
            state.current?.key !== location.hash ||
            !state.active.element.contains(event.target)
        )
            return;
        const scrolls = $$(
            state.active.element,
            ".screen-scroll,.auth-scroll,[data-preserve-scroll]",
        ).map((el) => ({ top: el.scrollTop, left: el.scrollLeft }));
        history.replaceState({ ...history.state, scrolls }, "", location.href);
    },
    true,
);
for (const event of ["online", "offline"])
    window.addEventListener(event, () =>
        document
            .querySelectorAll(".offline-notice")
            .forEach((el) => (el.hidden = navigator.onLine)),
    );
export async function start() {
    state.resources = await fetch(
        `${config.assets}android-resources.json`,
    ).then((r) => r.json());
    // Use native text metrics before restoring a screen's scroll position.
    if (document.fonts)
        await Promise.all([
            document.fonts.load("14px Roboto"),
            document.fonts.load("14px Myanmar"),
            document.fonts.load("700 14px Myanmar"),
            document.fonts.load("400 14px Poppins"),
            document.fonts.load("500 14px Poppins"),
            document.fonts.load("600 14px Poppins"),
            document.fonts.load("700 14px Poppins"),
        ]).catch(() => {});
    try {
        const session = await fetch(config.session, {
            credentials: "same-origin",
            cache: "no-store",
        }).then((r) => r.json());
        state.csrf = session.csrf;
        if (session.authenticated) await refreshUser();
    } catch (error) {
        state.startupError = error.message;
    }
    history.replaceState(
        { ...history.state, client: true },
        "",
        location.hash || `#/${state.user ? "home" : "login"}`,
    );
    await render();
}
