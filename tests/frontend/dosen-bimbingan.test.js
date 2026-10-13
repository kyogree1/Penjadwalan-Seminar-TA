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
const source = readFileSync('resources/js/pages/Dosen/Bimbingan.vue', 'utf8');
const { descriptor } = parse(
    source.replace(
        "'@/types/dosen-bimbingan'",
        "'../../types/dosen-bimbingan'",
    ),
    { filename: 'resources/js/pages/Dosen/Bimbingan.vue' },
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
    mockDosenAdvisees: [],
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
        patch: (...args) => requests.push(['patch', ...args]),
        delete: (...args) => requests.push(['delete', ...args]),
    },
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
const Directory = runInNewContext(`(function () { ${code} })()`, {
    imports,
    setTimeout: () => {},
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
const walk = (el) => [el, ...el.children.flatMap(walk)];
const click = (root, label) => {
    const el = walk(root).find(
        (el) => el.onClick && (text(el).trim() === label || el.title === label),
    );
    expect(el).toBeDefined();
    el.onClick();
};
const mount = (list = []) => {
    requests.length = 0;
    const props = reactive({
        advisees: list,
        flash: { success: null, error: null },
    });
    const root = node();
    renderer
        .createApp({ render: () => h(Directory, { ...props }) })
        .mount(root);
    return { root, props };
};

const live = () => [
    {
        id: 41,
        nama: 'Aktual',
        nim: '123',
        judul: 'Judul asli',
        roleAs: 'Pembimbing 1',
        pembimbing1: 'Dosen asli',
        pembimbing2: null,
        sesiList: [
            {
                id: 87,
                no: 1,
                tanggal: '2026-10-01',
                keterangan: 'Bab 2',
                rangkuman: 'Isi asli',
                catatanDosen: null,
                status: 'process',
            },
        ],
    },
];
describe('live logbook actual SFC', () => {
    test('create retains form and no synthetic sessions on failure, then renders persisted refresh', async () => {
        const { root, props } = mount([{ ...live()[0], sesiList: [] }]);
        click(root, 'Catat Sesi Baru');
        await nextTick();
        const summary = walk(root).find((el) =>
            el.placeholder?.startsWith('Tuliskan poin'),
        );
        summary['onUpdate:modelValue']('Ringkasan baru');
        await nextTick();
        click(root, 'Simpan Sesi');
        expect(requests[0].slice(0, 2)).toEqual([
            'post',
            '/dosen/bimbingan/sesi',
        ]);
        expect(requests[0][2].mahasiswa_id).toBe(41);
        expect(requests[0][2].keterangan).toBe('Bab 1 - Pendahuluan');
        expect(requests[0][2].rangkuman).toBe('Ringkasan baru');
        expect(props.advisees[0].sesiList).toHaveLength(0);
        requests[0][3].onError({ rangkuman: 'Rangkuman tidak valid' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Rangkuman tidak valid');
        expect(text(root)).toContain('Form Sesi Bimbingan');
        click(root, 'Simpan Sesi');
        requests[1][3].onNetworkError();
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Koneksi gagal');
        expect(text(root)).toContain('Form Sesi Bimbingan');
        click(root, 'Simpan Sesi');
        requests[2][3].onSuccess({
            props: { flash: { error: 'Ditolak server' } },
        });
        requests[2][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Ditolak server');
        expect(text(root)).toContain('Form Sesi Bimbingan');
        click(root, 'Simpan Sesi');
        requests[3][3].onSuccess({
            props: { flash: { success: 'Tersimpan di server' } },
        });
        requests[3][3].onFinish();
        props.advisees = live();
        await nextTick();
        expect(text(root)).not.toContain('Form Sesi Bimbingan');
        expect(text(root)).toContain('Tersimpan di server');
        expect(props.advisees[0].sesiList[0].id).toBe(87);
    });
    test('review sends persisted ID and notes without optimistic changes', async () => {
        const { root, props } = mount(live());
        click(root, 'Edit Arahan');
        await nextTick();
        const notes = walk(root).find((el) =>
            el.placeholder?.startsWith('Tuliskan arahan'),
        );
        notes['onUpdate:modelValue']('Arahan baru');
        await nextTick();
        click(root, 'Setujui & Paraf');
        await nextTick();
        click(root, 'Simpan & Beri Paraf Digital');
        expect(requests[0][1]).toBe('/dosen/bimbingan/87/paraf');
        expect(requests[0][2]).toEqual({
            status: 'delivered',
            catatan_dosen: 'Arahan baru',
        });
        expect(props.advisees[0].sesiList[0].catatanDosen).toBeNull();
        requests[0][3].onHttpException();
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Validasi & Catatan');
        expect(text(root)).toContain('Server tidak dapat');
    });
    test('reactive live props and empty state never fall back to fixtures', async () => {
        const { root, props } = mount(live());
        expect(text(root)).toContain('Aktual');
        expect(text(root)).toContain('Isi asli');
        expect(text(root)).toContain('Bab 2');
        expect(text(root)).toContain('Ambang kelayakan belum tersedia');
        props.advisees = [];
        await nextTick();
        expect(text(root)).not.toContain('Aktual');
        expect(text(root)).toContain('Belum ada mahasiswa bimbingan');
    });
    test('paraf keeps server data unchanged until refreshed and exposes errors', async () => {
        const { root, props } = mount(live());
        click(root, 'Paraf');
        expect(requests[0].slice(0, 2)).toEqual([
            'patch',
            '/dosen/bimbingan/87/paraf',
        ]);
        expect(props.advisees[0].sesiList[0].status).toBe('process');
        expect(text(root)).not.toContain('berhasil');
        requests[0][3].onError({ status: 'Ditolak server' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Ditolak server');
        click(root, 'Paraf');
        requests[1][3].onHttpException();
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Server tidak dapat');
        click(root, 'Paraf');
        requests[2][3].onSuccess({
            props: { flash: { success: 'Server berhasil' } },
        });
        requests[2][3].onFinish();
        props.advisees = live();
        props.advisees[0].sesiList[0].status = 'delivered';
        await nextTick();
        expect(text(root)).toContain('Server berhasil');
        expect(text(root)).toContain('Diparaf Dosen');
    });
});
