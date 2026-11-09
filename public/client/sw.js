/* Replaced by the scoped Laravel worker response. Never cache API/account data. */
const SHELL = __SHELL_URLS__;
const CACHE = "mastergyi-client-__CACHE_VERSION__";
self.addEventListener("install", (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)));
});
self.addEventListener("message", (event) => {
    if (event.data === "ACTIVATE_UPDATE") self.skipWaiting();
});
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter(
                            (key) =>
                                key.startsWith("mastergyi-client-") &&
                                key !== CACHE,
                        )
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});
self.addEventListener("fetch", (event) => {
    const request = event.request;
    const url = new URL(request.url);
    url.hash = "";
    const key = url.href;
    if (request.method !== "GET" || !SHELL.includes(key)) return;
    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok && response.type === "basic") {
                    const copy = response.clone();
                    event.waitUntil(
                        caches
                            .open(CACHE)
                            .then((cache) => cache.put(key, copy)),
                    );
                }
                return response;
            })
            .catch(() => caches.match(key)),
    );
});
