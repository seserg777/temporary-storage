// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    retries: 0,
    reporter: 'html',

    use: {
        baseURL: 'https://temporary-storage.loc',
        ignoreHTTPSErrors: true, // OSPanel uses a self-signed cert for .loc domains
        video: 'on',
        screenshot: 'on',
        trace: 'retain-on-failure',
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});