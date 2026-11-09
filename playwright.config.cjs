const { defineConfig } = require("@playwright/test");
module.exports = defineConfig({
    testDir: "./tests/client/browser",
    timeout: 30000,
    workers: 1,
    use: {
        baseURL: "http://127.0.0.1:8098",
        channel: "msedge",
        headless: true,
        viewport: { width: 412, height: 846 },
        deviceScaleFactor: 1,
        colorScheme: "light",
        serviceWorkers: "block",
        screenshot: "only-on-failure",
        trace: "retain-on-failure",
    },
    webServer: {
        command: "php artisan serve --host=127.0.0.1 --port=8098",
        url: "http://127.0.0.1:8098/app/",
        timeout: 30000,
        reuseExistingServer: false,
    },
});
