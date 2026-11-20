import { test, expect } from '@playwright/test';
import { readFileSync, existsSync } from 'node:fs';
import { resolve, extname } from 'node:path';

const types = { '.css': 'text/css', '.js': 'text/javascript', '.woff2': 'font/woff2', '.woff': 'font/woff', '.ttf': 'font/ttf', '.svg': 'image/svg+xml', '.png': 'image/png' };

const textContrast = locator => locator.evaluate(node => {
    const channel = color => color.match(/[\d.]+/g).map(Number);
    const luminance = color => channel(color).slice(0, 3).map(value => {
        const normalized = value / 255;
        return normalized <= 0.04045 ? normalized / 12.92 : ((normalized + 0.055) / 1.055) ** 2.4;
    }).reduce((total, value, i) => total + value * [0.2126, 0.7152, 0.0722][i], 0);
    let ancestor = node;
    while (ancestor && channel(getComputedStyle(ancestor).backgroundColor)[3] === 0) ancestor = ancestor.parentElement;
    const foreground = luminance(getComputedStyle(node).color);
    const background = luminance(ancestor ? getComputedStyle(ancestor).backgroundColor : 'rgb(255,255,255)');
    return (Math.max(foreground, background) + 0.05) / (Math.min(foreground, background) + 0.05);
});


test.beforeAll(() => {
    expect(process.env.DBCMS_ADMIN_FIXTURE_DIR, 'Export real admin PHP responses first').toBeTruthy();
    expect(process.env.DBCMS_ADMIN_ASSET_ROOT, 'Supply the real candidate Vite build').toBeTruthy();
    expect(process.env.DBCMS_ADMIN_THEME_ROOT, 'Supply the optional admin checkout for its local theme assets').toBeTruthy();
});

test.beforeEach(async ({ page }) => {
    await page.route('**/*', async route => {
        const pathname = new URL(route.request().url()).pathname;
        if (pathname.startsWith('/__admin/')) {
            const path = resolve(process.env.DBCMS_ADMIN_FIXTURE_DIR, pathname.slice('/__admin/'.length));
            expect(existsSync(path), `Missing exported fixture ${path}`).toBeTruthy();
            return route.fulfill({ contentType: 'text/html', body: readFileSync(path) });
        }
        if (pathname.startsWith('/build/')) {
            const path = resolve(process.env.DBCMS_ADMIN_ASSET_ROOT, `.${pathname}`);
            expect(existsSync(path), `Missing built asset ${path}`).toBeTruthy();
            return route.fulfill({ contentType: types[extname(path)] || 'application/octet-stream', body: readFileSync(path) });
        }
        if (pathname.includes('/theme-assets/termocms/')) {
            const relative = pathname.split('/theme-assets/termocms/')[1];
            const path = resolve(process.env.DBCMS_ADMIN_THEME_ROOT, 'assets', relative);
            expect(existsSync(path), `Missing optional admin asset ${path}`).toBeTruthy();
            return route.fulfill({ contentType: types[extname(path)] || 'application/octet-stream', body: readFileSync(path) });
        }
        return route.fulfill({ status: 204, body: '' });
    });
});

for (const theme of ['dbcms', 'termocms']) {
    for (const width of [390, 800, 1480]) {
        test(`${theme} retains usable shared admin surfaces at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto(`http://dbcms-admin.test/__admin/${theme}-login.html`);
            const email = page.locator('#login-email');
            const password = page.locator('#login-password');
            const toggle = page.locator('[data-password-toggle="login-password"]');
            await expect(email).toBeVisible();
            await expect(email).toHaveAccessibleName(/.+/);
            await expect(password).toHaveAccessibleName(/.+/);
            await email.fill('browser-fixture@example.test');
            await password.fill('unsubmitted-fixture');
            await expect(password).toHaveAttribute('type', 'password');
            const selectedLocale = page.locator('button[name="locale"][aria-pressed="true"]');
            expect(await textContrast(selectedLocale)).toBeGreaterThanOrEqual(4.5);
            if (theme === 'termocms') {
                await toggle.click();
                await expect(password).toHaveAttribute('type', 'text');
                await expect(toggle).toHaveAttribute('aria-pressed', 'true');
                await toggle.click();
                await expect(password).toHaveAttribute('type', 'password');
            }
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
            await page.screenshot({ path: `${process.env.DBCMS_BROWSER_RESULTS}/${theme}-login-${width}.png`, fullPage: true });

            await page.goto(`http://dbcms-admin.test/__admin/${theme}-dashboard.html`);
            await expect(page.locator('[data-admin-shell]')).toBeVisible();
            await expect(page.locator('main')).toBeVisible();
            const formsTitle = page.locator('main h3').filter({ hasText: 'Gestionar Formularios' });
            expect(await textContrast(formsTitle)).toBeGreaterThanOrEqual(4.5);
            if (width < 768) {
                const menu = page.locator('button[aria-controls="admin-navigation"]');
                await expect(menu).toBeVisible();
                await menu.click();
                await expect(menu).toHaveAttribute('aria-expanded', 'true');
                await expect.poll(() => page.locator('#admin-navigation').evaluate(node => node.getBoundingClientRect().x)).toBeGreaterThanOrEqual(0);
                await page.keyboard.press('Escape');
                await expect(menu).toHaveAttribute('aria-expanded', 'false');
                await expect.poll(() => page.locator('#admin-navigation').evaluate(node => node.getBoundingClientRect().right)).toBeLessThanOrEqual(0);
            }
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
            await page.screenshot({ path: `${process.env.DBCMS_BROWSER_RESULTS}/${theme}-dashboard-${width}.png`, fullPage: true });
        });
    }
}
