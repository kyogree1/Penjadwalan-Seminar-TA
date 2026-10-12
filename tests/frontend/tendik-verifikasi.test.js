import { describe, expect, test } from 'bun:test';
import { existsSync, readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import { createRenderer, defineComponent, h, nextTick, reactive } from 'vue';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';

globalThis.Document = class {};
globalThis.ShadowRoot = class {};
// Compile the actual SFC; only visual wrappers and Inertia navigation are stubbed.
const source = readFileSync('resources/js/pages/Tendik/Verifikasi.vue', 'utf8');
const { descriptor } = parse(
    source.replace(
        "'@/types/tendik-verifikasi'",
        "'../../types/tendik-verifikasi'",
    ),
    { filename: 'resources/js/pages/Tendik/Verifikasi.vue' },
);
const compiled = compileScript(descriptor, {
    id: 'dashboard-test',
    inlineTemplate: true,
    fs: {
        fileExists: existsSync,
        readFile: (path) => readFileSync(path, 'utf8'),
    },
});
const wrapper = defineComponent({
    setup:
        (_, { slots }) =>
        () =>
            h('div', [slots.default?.(), slots.action?.()]),
});
const stat = defineComponent({
    props: ['title', 'value'],
    setup: (props) => () => h('div', `${props.title}: ${props.value}`),
});
const requests = [];
const imports = {
    mockPengajuan: [],
    STATUS_PENGAJUAN: new Proxy(
        {},
        { get: (_, key) => ({ label: key, variant: 'slate' }) },
    ),
    STATUS_VERIFIKASI: new Proxy(
        {},
        { get: (_, key) => ({ label: key, variant: 'slate' }) },
    ),
    ...Vue,
    ...Icons,
    Head: wrapper,
    Button: defineComponent({
        setup:
            (_, { slots }) =>
            () =>
                h('button', slots.default?.()),
    }),
    Modal: defineComponent({
        props: ['show'],
        setup:
            (props, { slots }) =>
            () =>
                props.show ? h('section', slots.default?.()) : null,
    }),
    router: {
        patch: (...args) => requests.push(['patch', ...args]),
        post: (...args) => requests.push(['post', ...args]),
        put: (...args) => requests.push(['put', ...args]),
        delete: (...args) => requests.push(['delete', ...args]),
    },
    Link: wrapper,
    Card: wrapper,
    StatCard: stat,
    PageHeaderBox: wrapper,
    StatusBadge: defineComponent({
        props: ['text'],
        setup: (props) => () => h('span', props.text),
    }),
};
let code = new Bun.Transpiler({ loader: 'ts' }).transformSync(compiled.content);
code = code
    .replace(
        /import\s+\{([^}]+)\}\s+from\s+["'][^"']+["'];?/g,
        (_, names) => `const {${names.replace(/\bas\b/g, ':')}} = imports;`,
    )
    .replace(
        /import\s+(\w+)\s+from\s+["'][^"']+["'];?/g,
        (_, name) => `const ${name} = imports.${name} ?? imports.Card;`,
    )
    .replace('export default', 'return');
const Directory = runInNewContext(`(function () { ${code} })()`, {
    imports,
    confirm: () => true,
});
const node = (text = '') => ({
    text,
    children: [],
    parent: null,
    tagName: 'INPUT',
    options: [],
    value: '',
    addEventListener(event, fn) {
        this[event] = fn;
    },
    removeEventListener() {},
    getRootNode() {
        return { activeElement: null };
    },
    setAttribute() {},
    removeAttribute() {},
});
const renderer = createRenderer({
    createElement: (tag, _namespace, _is, props) =>
        Object.assign(node(), props, { tagName: tag.toUpperCase() }),
    createText: node,
    createComment: () => node(),
    setText: (el, text) => {
        el.text = text;
    },
    setElementText: (el, text) => {
        el.text = text;
        el.children = [];
    },
    patchProp: (el, key, _previous, value) => {
        el[key] = value;
    },
    insert: (el, parent, anchor) => {
        if (el.parent)
            el.parent.children.splice(el.parent.children.indexOf(el), 1);
        el.parent = parent;
        const index = anchor ? parent.children.indexOf(anchor) : -1;
        parent.children.splice(
            index < 0 ? parent.children.length : index,
            0,
            el,
        );
    },
    remove: (el) => {
        el.parent?.children.splice(el.parent.children.indexOf(el), 1);
    },
    parentNode: (el) => el.parent,
    nextSibling: (el) =>
        el.parent?.children[el.parent.children.indexOf(el) + 1],
});
const text = (el) => [el.text, ...el.children.map(text)].join(' ');
const rows = (tipe = 'sidang') => [
    {
        id: 40,
        tipe,
        nama: 'Aktual',
        nim: null,
        judul: 'Judul nyata',
        tanggalDaftar: '2026-01-01',
        status: 'menunggu',
        catatan: null,
        dokumen: [{ label: 'Proposal', path: null }],
    },
];
const walk = (el) => [el, ...el.children.flatMap(walk)];
const click = (root, label) => {
    const el = walk(root).find(
        (el) =>
            el.tagName === 'BUTTON' &&
            el.onClick &&
            (text(el).trim() === label || el.title === label),
    );
    expect(el).toBeDefined();
    el.onClick();
};
const mount = (list = []) => {
    requests.length = 0;
    const props = reactive({
        pengajuan: list,
        flash: { success: null, error: null },
    });
    const root = node();
    renderer
        .createApp({ render: () => h(Directory, { ...props }) })
        .mount(root);
    return { root, props };
};
describe('verification actual SFC', () => {
    test('persisted type and ID route mutations with modal feedback only after confirmation', async () => {
        for (const tipe of ['sidang', 'sempro']) {
            const { root, props } = mount(rows(tipe));
            if (tipe === 'sempro') {
                click(root, 'Seminar Proposal (1)');
                await nextTick();
            }
            click(root, 'Periksa Berkas');
            await nextTick();
            expect(text(root)).toContain('Belum tersedia');
            expect(text(root)).not.toContain('Lihat PDF');
            expect(
                walk(root).find((el) => el.tagName === 'TEXTAREA').value,
            ).toBe('');
            expect(text(root)).not.toContain('Minta Revisi');
            click(root, 'Verifikasi Berkas');
            click(root, 'Verifikasi Berkas');
            await nextTick();
            expect(requests.length).toBe(1);
            expect(
                walk(root).find((el) => el.tagName === 'TEXTAREA').disabled,
            ).toBe(true);
            const close = walk(root).find(
                (el) => el.tagName === 'BUTTON' && el['aria-label'] === 'Tutup',
            );
            expect(close?.disabled).toBe(true);
            close.onClick();
            await nextTick();
            expect(walk(root).some((el) => el.tagName === 'SECTION')).toBe(
                true,
            );
            expect(requests[0].slice(0, 3)).toEqual([
                'patch',
                `/tendik/verifikasi/${tipe}/40`,
                { status: 'verifikasi_tendik', catatan: '' },
            ]);
            expect(props.pengajuan[0].status).toBe('menunggu');
            expect(
                walk(root).find(
                    (el) =>
                        el.tagName === 'BUTTON' &&
                        text(el).trim() === 'Verifikasi Berkas',
                ).disabled,
            ).toBe(true);
            requests[0][3].onError({ status: 'Status tidak valid' });
            requests[0][3].onFinish();
            await nextTick();
            expect(
                text(walk(root).find((el) => el.tagName === 'SECTION')),
            ).toContain('Status tidak valid');
            click(root, 'Tolak Berkas');
            expect(requests[1][2].status).toBe('ditolak');
            requests[1][3].onNetworkError();
            requests[1][3].onFinish();
            await nextTick();
            expect(text(root)).toContain('Koneksi');
            click(root, 'Tolak Berkas');
            requests[2][3].onSuccess({
                props: { flash: { error: 'Akses ditolak' } },
            });
            requests[2][3].onFinish();
            await nextTick();
            expect(text(root)).toContain('Akses ditolak');
            click(root, 'Tolak Berkas');
            requests[3][3].onHttpException();
            requests[3][3].onFinish();
            await nextTick();
            expect(text(root)).toContain('server');
            click(root, 'Tolak Berkas');
            requests[4][3].onSuccess({ props: { flash: {} } });
            requests[4][3].onFinish();
            await nextTick();
            expect(text(root)).toContain('Konfirmasi server belum diterima');
            click(root, 'Verifikasi Berkas');
            props.pengajuan = [
                {
                    ...rows(tipe)[0],
                    status: 'verifikasi_tendik',
                    nama: 'Refresh server',
                },
            ];
            requests[5][3].onSuccess({
                props: { flash: { success: 'Verifikasi dikonfirmasi' } },
            });
            requests[5][3].onFinish();
            await nextTick();
            expect(text(root)).toContain('Verifikasi dikonfirmasi');
            expect(text(root)).toContain('Refresh server');
            expect(text(root)).toContain('verifikasi_tendik');
            expect(walk(root).some((el) => el.tagName === 'SECTION')).toBe(
                false,
            );
            click(root, 'Periksa Berkas');
            await nextTick();
            props.pengajuan = [];
            await nextTick();
            expect(text(root)).toContain(
                'Data pendaftaran tidak lagi tersedia',
            );
            expect(
                walk(root).some(
                    (el) =>
                        el.tagName === 'BUTTON' &&
                        text(el).trim() === 'Verifikasi Berkas',
                ),
            ).toBe(false);
        }
    });
    test('populated empty and refreshed props never use fixtures', async () => {
        const { root, props } = mount(rows());
        expect(text(root)).toContain('Aktual');
        props.pengajuan = [];
        await nextTick();
        expect(text(root)).not.toContain('Aktual');
        expect(text(root)).toContain('Tidak ada pendaftar');
        props.pengajuan = [{ ...rows()[0], nama: 'Diperbarui' }];
        await nextTick();
        expect(text(root)).toContain('Diperbarui');
    });
});
