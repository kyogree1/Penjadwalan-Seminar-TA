const fs = require('fs');
const scratch =
    'C:/Users/Tenma/AppData/Local/hermes/profiles/redmess/cache/scratch/current-browser';
const { chromium } = require(scratch + '/node_modules/playwright');
const out = __dirname;
(async () => {
    const [role, route, label, actions = '[]'] = process.argv.slice(2);
    const browser = await chromium.launch({
        executablePath:
            'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
        headless: true,
    });
    const state = scratch + '/' + role + '.json';
    const context = await browser.newContext({
        acceptDownloads: true,
        viewport: { width: 1440, height: 1000 },
        ...(fs.existsSync(state) ? { storageState: state } : {}),
    });
    const page = await context.newPage();
    const events = [];
    page.on('console', (m) =>
        events.push({ kind: 'console', type: m.type(), text: m.text() }),
    );
    page.on('pageerror', (e) =>
        events.push({ kind: 'pageerror', text: e.message }),
    );
    page.on('dialog', async (d) => {
        events.push({ kind: 'dialog', type: d.type(), text: d.message() });
        await d.accept();
    });
    page.on('response', (r) =>
        events.push({
            kind: 'response',
            method: r.request().method(),
            url: r.url(),
            status: r.status(),
        }),
    );
    page.on('requestfailed', (r) =>
        events.push({
            kind: 'requestfailed',
            url: r.url(),
            error: r.failure(),
        }),
    );
    try {
        if (!fs.existsSync(state)) {
            await page.goto('http://127.0.0.1:8897/login');
            const cookies = await context.cookies();
            const token = decodeURIComponent(
                cookies.find((c) => c.name === 'XSRF-TOKEN').value,
            );
            const response = await context.request.post(
                'http://127.0.0.1:8897/login',
                {
                    headers: { 'X-XSRF-TOKEN': token },
                    form: { demo_role: role },
                },
            );
            if (!response.ok())
                throw Error('Local demo login failed ' + response.status());
        }
        await page.goto('http://127.0.0.1:8897' + route);
        await page.waitForTimeout(600);
        for (const a of JSON.parse(actions)) {
            let loc = a.selector
                ? page.locator(a.selector)
                : a.text
                  ? page.getByRole(a.role || 'button', {
                        name: a.text,
                        exact: a.exact !== false,
                    })
                  : null;
            if (a.nth !== undefined) loc = loc.nth(a.nth);
            if (a.click) await loc.click();
            if (a.fill !== undefined) await loc.fill(a.fill);
            if (a.select !== undefined) await loc.selectOption(a.select);
            if (a.press) {
                if (loc) await loc.press(a.press);
                else await page.keyboard.press(a.press);
            }
            if (a.offline !== undefined) await context.setOffline(a.offline);
            if (a.viewport) await page.setViewportSize(a.viewport);
            if (a.checkLayout) {
                const l = await page.evaluate(() => ({
                    width: innerWidth,
                    scroll: document.documentElement.scrollWidth,
                }));
                if (l.scroll > l.width) throw Error('Viewport overflow');
                events.push({ kind: 'viewportassert', ...l });
            }
            if (a.reload) await page.reload();
            if (a.download) {
                const [d] = await Promise.all([
                    page.waitForEvent('download'),
                    loc.click(),
                ]);
                await d.saveAs(out + '/' + a.download);
            }
            if (a.upload) await loc.setInputFiles(a.upload);
            if (a.document) {
                const [response] = await Promise.all([
                    context.waitForEvent('response', (r) =>
                        r.url().includes('/dokumen/'),
                    ),
                    loc.click(),
                ]);
                const bytes = await response.body();
                fs.writeFileSync(out + '/' + a.document, bytes);
                events.push({
                    kind: 'document',
                    status: response.status(),
                    headers: Object.fromEntries(
                        Object.entries(response.headers()).filter(([k]) =>
                            [
                                'content-type',
                                'cache-control',
                                'content-security-policy',
                                'x-content-type-options',
                                'content-disposition',
                            ].includes(k),
                        ),
                    ),
                    sha256: require('crypto')
                        .createHash('sha256')
                        .update(bytes)
                        .digest('hex'),
                });
            }
            if (a.assertText)
                await page
                    .getByText(a.assertText, { exact: false })
                    .first()
                    .waitFor();
            if (
                a.assertAbsent &&
                (await page.getByText(a.assertAbsent, { exact: false }).count())
            )
                throw Error('Unexpected text ' + a.assertAbsent);
            if (a.focus) {
                const actual = await page.evaluate(
                    () =>
                        document.activeElement?.getAttribute('aria-label') ||
                        document.activeElement?.textContent?.trim(),
                );
                if (actual !== a.focus)
                    throw Error('Focus expected ' + a.focus + ' got ' + actual);
                events.push({ kind: 'focusassert', actual });
            }
            await page.waitForTimeout(a.wait || 400);
        }
        await context.storageState({ path: state });
        const snapshot = {
            label,
            url: page.url(),
            body: await page.locator('body').innerText(),
            controls: await page
                .locator('input,select,textarea,button,a')
                .evaluateAll((es) =>
                    es.map((e) => ({
                        tag: e.tagName,
                        type: e.type,
                        text: (e.innerText || '').trim(),
                        placeholder: e.placeholder,
                        name: e.name,
                        title: e.title,
                        aria: e.getAttribute('aria-label'),
                        href: e.getAttribute('href'),
                        value: e.type === 'password' ? undefined : e.value,
                        options: e.options
                            ? [...e.options].map((o) => ({
                                  value: o.value,
                                  text: o.text,
                              }))
                            : undefined,
                    })),
                ),
            layout: await page.evaluate(() => ({
                width: innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                dark: document.documentElement.classList.contains('dark'),
                focus:
                    document.activeElement?.tagName +
                    ' ' +
                    document.activeElement?.getAttribute('aria-label') +
                    ' ' +
                    (document.activeElement?.innerText || '').slice(0, 120),
            })),
        };
        fs.writeFileSync(
            out + '/' + label + '.json',
            JSON.stringify(snapshot, null, 2),
        );
        await page.screenshot({
            path: out + '/' + label + '.png',
            fullPage: true,
        });
        console.log(
            JSON.stringify(
                {
                    label,
                    url: snapshot.url,
                    body: snapshot.body,
                    form: snapshot.controls.filter((c) =>
                        ['INPUT', 'SELECT', 'TEXTAREA'].includes(c.tag),
                    ),
                    layout: snapshot.layout,
                },
                null,
                2,
            ),
        );
    } catch (e) {
        events.push({ kind: 'harnesserror', text: e.message });
        console.error(e);
        process.exitCode = 1;
        await page.screenshot({
            path: out + '/' + label + '-error.png',
            fullPage: true,
        });
    } finally {
        fs.appendFileSync(
            out + '/events.jsonl',
            events.map((e) => JSON.stringify({ label, ...e })).join('\n') +
                '\n',
        );
        await browser.close();
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
