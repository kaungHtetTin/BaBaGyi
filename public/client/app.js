import { config, state, start, toast, button, esc } from "./core.js";
import { screens } from "./screens.js";
import { bettingScreens } from "./betting.js";
Object.assign(state.screens, screens, bettingScreens);
window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    state.install = event;
    document.dispatchEvent(new CustomEvent("client-install-ready"));
});
window.addEventListener("appinstalled", () => {
    state.install = null;
    toast("MasterGyi ကို ထည့်သွင်းပြီးပါပြီ");
});
try {
    await start();
    if ("serviceWorker" in navigator && window.isSecureContext)
        try {
            state.registration = await navigator.serviceWorker.register(
                `${config.base}sw.js`,
                { scope: config.scope },
            );
            state.registration?.addEventListener("updatefound", () => {
                const worker = state.registration.installing;
                worker?.addEventListener("statechange", () => {
                    if (
                        worker.state === "installed" &&
                        navigator.serviceWorker.controller
                    )
                        toast(
                            "ဗားရှင်းအသစ်ရရှိပါပြီ။ ဆက်တင်များတွင် စစ်ဆေးနိုင်ပါသည်။",
                        );
                });
            });
        } catch (error) {
            console.warn("Offline support could not start.", error.message);
        }
} catch (error) {
    document.getElementById("client-root").innerHTML =
        `<main class="boot"><h1>MasterGyi</h1><p role="alert">${esc("အက်ပ်ကို မဖွင့်နိုင်သေးပါ။ ပြန်လည်ကြိုးစားပါ။")}</p>${button("ပြန်ကြိုးစားမည်", "reload")}</main>`;
    document.querySelector('[data-action="reload"]').onclick = () =>
        location.reload();
}
