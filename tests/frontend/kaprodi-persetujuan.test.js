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
const source = readFileSync(
    'resources/js/pages/Kaprodi/Persetujuan.vue',
    'utf8',
);
const { descriptor } = parse(
    source.replace(
        "'@/types/kaprodi-persetujuan'",
        "'../../types/kaprodi-persetujuan'",
    ),
    { filename: 'resources/js/pages/Kaprodi/Persetujuan.vue' },
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
        put: (...args) => requests.push(['put', ...args]),
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
const mount = () => {
    requests.length = 0;
    const props = reactive({
        dbJudul: [
            {
                id: 1,
                judul_ta: 'Judul aktual',
                bidang_penelitian: 'AI',
                status: 'menunggu',
                mahasiswa: { id: 2, name: 'Mahasiswa aktual', nim_nip: null },
                pembimbing_1_id: 8,
                pembimbing_2_id: null,
                catatan_kaprodi: 'Catatan judul',
            },
        ],
        dbSempro: [
            {
                id: 1,
                judul_ta: 'Proposal aktual',
                status: 'menunggu',
                mahasiswa: { id: 2, name: 'Mahasiswa aktual', nim_nip: null },
                catatan: 'Admin sempro',
                catatan_kaprodi: 'Catatan sempro',
                status_akademik: 'menunggu',
            },
        ],
        dbSidang: [
            {
                id: 1,
                judul_ta: 'Sidang aktual',
                status: 'terjadwal',
                mahasiswa: { id: 2, name: 'Mahasiswa aktual', nim_nip: null },
                catatan: 'Catatan sidang',
            },
        ],
        dosenOptions: [{ id: 8, name: 'Dosen aktual' }],
    });
    const root = node();
    renderer
        .createApp({ render: () => h(Directory, { ...props }) })
        .mount(root);
    return { root, props };
};
describe('Kaprodi review actual SFC', () => {
    test('live payload refresh and empty results never show fixtures', async () => {
        const { root, props } = mount();
        expect(text(root)).toContain('Judul aktual');
        props.dbJudul = [{ ...props.dbJudul[0], judul_ta: 'Judul diperbarui' }];
        await nextTick();
        expect(text(root)).toContain('Judul diperbarui');
        props.dbJudul = [];
        await nextTick();
        expect(text(root)).not.toContain('Judul diperbarui');
        expect(text(root)).toContain('Tidak ada pengajuan');
    });
    test('supervisor request uses persisted IDs and waits for confirmation', async () => {
        const { root, props } = mount();
        click(root, 'Review Judul');
        await nextTick();
        click(root, 'Setujui Judul');
        click(root, 'Setujui Judul');
        await nextTick();
        expect(requests.length).toBe(1);
        expect(requests[0][1]).toBe('/kaprodi/persetujuan/judul/1');
        expect(requests[0][2]).toEqual({
            status: 'disetujui',
            pembimbing_1_id: 8,
            pembimbing_2_id: null,
            catatan_kaprodi: 'Catatan judul',
        });
        expect(props.dbJudul[0].status).toBe('menunggu');
        expect(text(root)).not.toContain('Server menyimpan');
        requests[0][3].onError({ pembimbing_1_id: 'Dosen tidak valid' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Dosen tidak valid');
        click(root, 'Setujui Judul');
        requests[1][3].onSuccess({
            props: { flash: { success: 'Server menyimpan' } },
        });
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Server menyimpan');
    });
    test('review decisions send revise and reject without optimistic status changes', async () => {
        const { root, props } = mount();
        click(root, 'Review Judul');
        await nextTick();
        click(root, 'Minta Revisi');
        expect(requests[0][2].status).toBe('revisi');
        expect(props.dbJudul[0].status).toBe('menunggu');
        requests[0][3].onFinish();
        await nextTick();
        click(root, 'Tolak');
        expect(requests[1][2].status).toBe('ditolak');
        expect(props.dbJudul[0].status).toBe('menunggu');
    });
    test('academic notes use selected type even after switching tabs with separate review fields', async () => {
        const { root, props } = mount();
        click(root, 'Sempro (1)');
        await nextTick();
        click(root, 'Tinjau');
        await nextTick();
        click(root, 'Sidang TA (1)');
        await nextTick();
        click(root, 'Simpan Catatan');
        await nextTick();
        expect(requests[0][1]).toBe('/kaprodi/persetujuan/sempro/1');
        expect(requests[0][2]).toEqual({
            status_akademik: 'menunggu',
            catatan_kaprodi: 'Catatan sempro',
        });
        expect(props.dbSempro[0].catatan).toBe('Admin sempro');
        requests[0][3].onNetworkError();
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Koneksi');
        expect(
            walk(root).find(
                (el) =>
                    el.onClick !== undefined &&
                    text(el).trim() === 'Minta Revisi',
            ),
        ).toBeDefined();
        for (const [callback, message] of [
            ['onError', 'Catatan tidak valid'],
            ['onHttpException', 'Permintaan gagal'],
            ['onCancel', 'dibatalkan'],
            ['onSuccess', 'Konfirmasi server belum diterima'],
        ]) {
            click(root, 'Simpan Catatan');
            const options = requests.at(-1)[3];
            if (callback === 'onError') options.onError({ catatan: message });
            else if (callback === 'onSuccess')
                options.onSuccess({ props: { flash: {} } });
            else options[callback]();
            options.onFinish();
            await nextTick();
            expect(text(root)).toContain(message);
            expect(text(root)).toContain('Simpan Catatan');
        }
        click(root, 'Simpan Catatan');
        requests.at(-1)[3].onSuccess({
            props: { flash: { success: 'Catatan tersimpan' } },
        });
        requests.at(-1)[3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Catatan tersimpan');
        click(root, 'Tinjau');
        await nextTick();
        props.dbSidang = [];
        await nextTick();
        expect(text(root)).toContain('Data pengajuan tidak lagi tersedia');
    });
});
