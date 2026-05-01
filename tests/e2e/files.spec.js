// @ts-check
import { test, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PDF_PATH = path.join(__dirname, 'fixtures', 'sample.pdf');
const PROJECT_ROOT = path.resolve(__dirname, '../../');

test.describe('Temporary Storage — file lifecycle', () => {

    test.beforeAll(() => {
        execSync('php artisan migrate:fresh --force', {
            cwd: PROJECT_ROOT,
            stdio: 'ignore',
        });
    });

    test('home page shows the uploader and no file list', async ({ page }) => {
        await page.goto('/');

        await expect(page).toHaveURL('/');
        await expect(page.locator('#upload-zone')).toBeVisible();
        await expect(page.locator('#file-input')).toBeAttached();
        await expect(page.locator('table')).not.toBeAttached();
    });

    test('uploading a PDF redirects to /files and shows the file in the list', async ({ page }) => {
        await page.goto('/');

        await page.locator('#file-input').setInputFiles(PDF_PATH);

        await page.waitForURL('**/files', { timeout: 15_000 });

        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('tbody tr').first()).toBeVisible();
        await expect(page.locator('tbody tr').first()).toContainText('sample.pdf');
    });

    test('deleting a file removes it from the list', async ({ page }) => {
        await page.goto('/files');

        await expect(page.locator('table')).toBeVisible();

        page.once('dialog', dialog => dialog.accept());

        await page.locator('tbody tr').first().locator('button:has-text("Delete")').click();

        await expect(page.locator('p.text-center')).toContainText('No files uploaded yet.');
    });

    test('deleting a file from the show page redirects to /files', async ({ page }) => {
        await page.goto('/');
        await page.locator('#file-input').setInputFiles(PDF_PATH);
        await page.waitForURL('**/files', { timeout: 15_000 });

        await page.locator('tbody tr').first().locator('a', { hasText: 'View' }).click();
        await expect(page).toHaveURL(/\/files\/\d+/);

        page.once('dialog', dialog => dialog.accept());

        await page.locator('button:has-text("Delete this file")').click();

        await page.waitForURL('**/files');

        await expect(page.locator('p.text-center')).toContainText('No files uploaded yet.');
    });

    test('shows session expired message when CSRF token is invalid', async ({ page }) => {
        await page.goto('/');

        // Intercept the upload request and return a 419 with the same JSON the server produces
        await page.route('**/files', async (route, request) => {
            if (request.method() === 'POST') {
                await route.fulfill({
                    status: 419,
                    contentType: 'application/json',
                    body: JSON.stringify({ message: 'Your session has expired. Please refresh the page.' }),
                });
            } else {
                await route.continue();
            }
        });

        await page.locator('#file-input').setInputFiles(PDF_PATH);

        await expect(page.locator('#upload-error')).toBeVisible({ timeout: 10_000 });
        await expect(page.locator('#upload-error')).toContainText(/session.*expired|expired.*session/i);
    });

    test('shows rate limit error after exceeding upload limit', async ({ page }) => {
        await page.goto('/');

        await page.route('**/files', async (route, request) => {
            if (request.method() === 'POST') {
                await route.fulfill({
                    status: 429,
                    headers: { 'Retry-After': '60' },
                    contentType: 'application/json',
                    body: JSON.stringify({ message: 'Too Many Attempts.' }),
                });
            } else {
                await route.continue();
            }
        });

        await page.locator('#file-input').setInputFiles(PDF_PATH);

        await expect(page.locator('#upload-error')).toBeVisible({ timeout: 10_000 });
        await expect(page.locator('#upload-error')).toContainText(/too many/i);
    });

    test('clicking View on a file opens the file detail page', async ({ page }) => {
        await page.goto('/');
        await page.locator('#file-input').setInputFiles(PDF_PATH);
        await page.waitForURL('**/files', { timeout: 15_000 });

        await page.locator('tbody tr').first().locator('a', { hasText: 'View' }).click();

        await expect(page).toHaveURL(/\/files\/\d+/);
        await expect(page.locator('dl')).toBeVisible();
        await expect(page.locator('dd').first()).toContainText('sample.pdf');
    });
});