import { describe, expect, test } from 'bun:test';
import { existsSync, readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import { createRenderer, defineComponent, h, nextTick, reactive } from 'vue';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';

// Compile the actual SFC; only visual wrappers and Inertia navigation are stubbed.
const source = readFileSync('resources/js/pages/Kaprodi/Dashboard.vue', 'utf8');
const { descriptor } = parse(
    source.replace(
        "'@/types/kaprodi-dashboard'",
        "'../../types/kaprodi-dashboard'",
    ),
    { filename: 'resources/js/pages/Kaprodi/Dashboard.vue' },
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
            h('div', slots.default?.()),
});
const stat = defineComponent({
    props: ['title', 'value'],
    setup: (props) => () => h('div', `${props.title}: ${props.value}`),
});
const imports = {
    ...Vue,
    ...Icons,
    Head: wrapper,
    Link: wrapper,
    Card: wrapper,
    StatCard: stat,
    PageHeaderBox: wrapper,
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
const Dashboard = runInNewContext(`(function () { ${code} })()`, { imports });
const node = (text = '') => ({ text, children: [], parent: null });
const renderer = createRenderer({
    createElement: () => node(),
    createText: node,
    createComment: () => node(),
    setText: (el, text) => {
        el.text = text;
    },
    setElementText: (el, text) => {
        el.text = text;
        el.children = [];
    },
    patchProp: () => {},
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
const empty = () => ({
    stats: { totalDosen: 0, totalMahasiswa: 0, antreanPersetujuan: 0 },
    pendingCounts: { judul: 0, sempro: 0, sidang: 0 },
    lecturers: [],
    pendingApprovals: [],
    deadlines: null,
});

describe('Kaprodi dashboard live props', () => {
    test('empty results never render fixtures or fabricated metrics', () => {
        const root = node();
        renderer.createApp(Dashboard, empty()).mount(root);
        const output = text(root);
        expect(output).toContain('Total Mahasiswa: 0');
        expect(output).toContain('Belum ada dosen terdaftar');
        expect(output).toContain('Tidak ada usulan judul menunggu');
        expect(output).toContain('Data batas waktu periode belum tersedia');
        expect(output).not.toContain('Tejo');
        expect(output).not.toContain('142');
        expect(output).not.toContain('0.994');
    });

    test('mounted dashboard reflects replaced Inertia props including empty arrays', async () => {
        const props = reactive(empty());
        const root = node();
        renderer
            .createApp({ render: () => h(Dashboard, { ...props }) })
            .mount(root);
        Object.assign(props, {
            stats: { totalDosen: 1, totalMahasiswa: 7, antreanPersetujuan: 3 },
            pendingCounts: { judul: 1, sempro: 2, sidang: 0 },
            lecturers: [
                {
                    id: 1,
                    name: 'Dosen Aktual',
                    nip: null,
                    bimbingan1: 2,
                    bimbingan2: 0,
                    total: 2,
                },
            ],
            pendingApprovals: [
                {
                    id: 1,
                    nama: 'Mahasiswa Aktual',
                    nim: null,
                    judul: 'Judul Aktual',
                    tanggal: '2026-10-01',
                    pembimbing1: null,
                    pembimbing2: null,
                },
            ],
        });
        await nextTick();
        expect(text(root)).toContain('Total Mahasiswa: 7');
        expect(text(root)).toContain('Dosen Aktual');
        expect(text(root)).toContain('Judul Aktual');
        Object.assign(props, empty());
        await nextTick();
        expect(text(root)).toContain('Total Mahasiswa: 0');
        expect(text(root)).toContain('Tidak ada usulan judul menunggu');
        expect(text(root)).not.toContain('Dosen Aktual');
        expect(text(root)).not.toContain('Judul Aktual');
    });
});
