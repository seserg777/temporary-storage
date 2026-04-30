// @ts-check
import { test, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PDF_PATH = path.join(__dirname, 'fixtures', 'sample.pdf');

test.describe('Temporary Storage — file lifecycle', () => {

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

        await page.locator('tbody tr').first().locator('button[type="submit"]').click();

        await page.waitForURL('**/files');

        await expect(page.locator('p.text-center')).toContainText('No files uploaded yet.');
    });
});