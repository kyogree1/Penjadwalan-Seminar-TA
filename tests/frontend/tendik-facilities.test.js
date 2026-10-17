import { expect, test } from 'bun:test';
import { existsSync, readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import { createRenderer, defineComponent, h, nextTick, reactive } from 'vue';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';

globalThis.Document = class {};
globalThis.ShadowRoot = class {};
// Compile the actual SFC; only visual wrappers and Inertia navigation are stubbed.
for (const pageName of ['Dashboard', 'Arsip']) {
    const source = readFileSync(
        `resources/js/pages/Tendik/${pageName}.vue`,
        'utf8',
    );
    const { descriptor } = parse(
        source.replace(
            "'@/types/tendik-facilities'",
            "'../../types/tendik-facilities'",
        ),
        { filename: `resources/js/pages/Tendik/${pageName}.vue` },
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
                h('div', [
                    slots.default?.(),
                    slots.action?.(),
                    slots.actions?.(),
                ]),
    });
    const stat = defineComponent({
        props: ['title', 'value'],
        setup: (props) => () => h('div', `${props.title}: ${props.value}`),
    });
    const requests = [];
    const imports = {
        mockRuangan: [],
        mockPengajuan: [],
        mockArsipDokumen: [],
        mockCetakTerbaru: [],
        verifikasi: () => '/tendik/verifikasi',
        ruangan: () => '/tendik/ruangan',
        mahasiswa: () => '/tendik/mahasiswa',
        arsip: () => '/tendik/arsip',
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
            post: (...args) => requests.push(['post', ...args]),
            put: (...args) => requests.push(['put', ...args]),
            delete: (...args) => requests.push(['delete', ...args]),
        },
        Link: wrapper,
        Card: wrapper,
        StatCard: stat,
        PageHeaderBox: wrapper,
    };
    let code = new Bun.Transpiler({ loader: 'ts' }).transformSync(
        compiled.content,
    );
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
    const walk = (el) => [el, ...el.children.flatMap(walk)];
    const text = (el) => [el.text, ...el.children.map(text)].join(' ');

    const mount = (input) => {
        requests.length = 0;
        const props = reactive(input);
        const root = node();
        renderer
            .createApp({ render: () => h(Directory, { ...props }) })
            .mount(root);
        return { root, props };
    };
    if (pageName === 'Dashboard')
        test('dashboard displays supported server stats reactively and no fixture print/month metrics', async () => {
            const { root, props } = mount({
                stats: {
                    antreanVerifikasi: 7,
                    antreanSempro: 4,
                    antreanSidang: 3,
                    totalMahasiswa: 13,
                },
            });
            expect(text(root)).toContain('Antrean Verifikasi: 7');
            expect(text(root)).toContain('Akun Mahasiswa: 13');
            expect(text(root)).toContain('4 Sempro');
            expect(text(root)).toContain('3 Sidang');
            expect(text(root)).not.toContain('Bulan ini');
            expect(text(root)).not.toContain('28 Dokumen');
            props.stats = {
                antreanVerifikasi: 0,
                antreanSempro: 0,
                antreanSidang: 0,
                totalMahasiswa: 0,
            };
            await nextTick();
            expect(text(root)).toContain('Antrean Verifikasi: 0');
            expect(text(root)).toContain('Akun Mahasiswa: 0');
        });
    if (pageName === 'Arsip')
        test('archive distinguishes unsupported capability from an empty stored archive and has no pretend print action', () => {
            const { root } = mount({
                archiveCapabilities: { persistence: false, printing: false },
            });
            expect(text(root)).toContain('Pengarsipan belum tersedia');
            expect(text(root)).toContain('Riwayat pencetakan belum tersimpan');
            expect(text(root)).toContain('Pencetakan belum tersedia');
            expect(walk(root).some((el) => el.onClick)).toBe(false);
            expect(text(root)).not.toContain('TA-06');
        });
}
