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
const source = readFileSync('resources/js/pages/Kaprodi/Dosen.vue', 'utf8');
const { descriptor } = parse(
    source.replace("'@/types/kaprodi-dosen'", "'../../types/kaprodi-dosen'"),
    { filename: 'resources/js/pages/Kaprodi/Dosen.vue' },
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
        post: (...args) => requests.push(['post', ...args]),
        put: (...args) => requests.push(['put', ...args]),
        delete: (...args) => requests.push(['delete', ...args]),
    },
    Link: wrapper,
    Card: wrapper,
    StatCard: stat,
    PageHeaderBox: wrapper,
};
const importDescriptor = parse(
    readFileSync('resources/js/Components/LecturerCsvImport.vue', 'utf8'),
).descriptor;
const importCompiled = compileScript(importDescriptor, {
    id: 'csv-test',
    inlineTemplate: true,
});
const compileComponent = (content) =>
    new Bun.Transpiler({ loader: 'ts' })
        .transformSync(content)
        .replace(
            /import\s+\{([^}]+)\}\s+from\s+["'][^"']+["'];?/g,
            (_, names) => `const {${names.replace(/\bas\b/g, ':')}} = imports;`,
        )
        .replace(
            /import\s+(\w+)\s+from\s+["'][^"']+["'];?/g,
            (_, name) => `const ${name} = imports.${name} ?? imports.Card;`,
        )
        .replace('export default', 'return');
let previewResponse = {
    valid: false,
    errors: ['Header tidak valid'],
    rows: [],
};
imports.LecturerCsvImport = runInNewContext(
    `(function () { ${compileComponent(importCompiled.content)} })()`,
    {
        imports,
        Error,
        AbortController,
        FormData,
        document: { cookie: '' },
        fetch: async () => ({ ok: true, json: async () => previewResponse }),
    },
);
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
const rows = (count = 1) =>
    Array.from({ length: count }, (_, i) => ({
        id: 40 + i,
        name: `Aktual ${i}`,
        email: `d${i}@example.test`,
        nip: null,
        prodi: 'S1 Informatika',
        jabatan: null,
    }));
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
        dosenList: list,
        flash: { success: null, error: null },
    });
    const root = node();
    renderer
        .createApp({ render: () => h(Directory, { ...props }) })
        .mount(root);
    return { root, props };
};
const fill = async (root, id, value) => {
    const el = walk(root).find((el) => el.id === `dosen-${id}`);
    el['onUpdate:modelValue'](value);
    await nextTick();
};
describe('lecturer directory actual SFC', () => {
    test('CRUD waits for server confirmation, preserves validation errors and real IDs', async () => {
        const { root, props } = mount(rows());
        click(root, 'Edit Dosen');
        await nextTick();
        await fill(root, 'nip', '123');
        click(root, 'Simpan Perubahan');
        await nextTick();
        expect(requests[0].slice(0, 2)).toEqual(['put', '/kaprodi/dosen/40']);
        expect(Object.keys(requests[0][2]).sort()).toEqual([
            'email',
            'jabatan',
            'name',
            'nip',
            'prodi',
        ]);
        expect(text(root)).not.toContain('berhasil');
        requests[0][3].onError({ email: 'Email sudah digunakan' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Email sudah digunakan');
        expect(text(root)).toContain('Edit Data Dosen');
        click(root, 'Simpan Perubahan');
        requests[1][3].onSuccess({
            props: { flash: { success: 'Server menyimpan' } },
        });
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Server menyimpan');
        expect(text(root)).not.toContain('Edit Data Dosen');
        click(root, 'Hapus Dosen');
        await nextTick();
        expect(requests[2].slice(0, 2)).toEqual([
            'delete',
            '/kaprodi/dosen/40',
        ]);
        expect(text(root)).toContain('Aktual 0');
        requests[2][2].onNetworkError();
        requests[2][2].onFinish();
        await nextTick();
        expect(text(root)).toContain('Koneksi');
        expect(text(root)).toContain('Aktual 0');
        click(root, 'Hapus Dosen');
        requests[3][2].onSuccess({
            props: { flash: { error: 'Tidak dapat menghapus' } },
        });
        requests[3][2].onFinish();
        await nextTick();
        expect(text(root)).toContain('Tidak dapat menghapus');
        click(root, 'Tambah Dosen');
        await nextTick();
        for (const [key, value] of Object.entries({
            name: 'Baru',
            email: 'baru@example.test',
            nip: '456',
            prodi: 'S1 Informatika',
        }))
            await fill(root, key, value);
        const buttons = walk(root).filter(
            (el) => el.onClick && text(el).trim() === 'Tambah Dosen',
        );
        buttons.at(-1).onClick();
        await nextTick();
        expect(requests[4].slice(0, 2)).toEqual(['post', '/kaprodi/dosen']);
        requests[4][3].onSuccess({
            props: { flash: { success: 'Server membuat' } },
        });
        requests[4][3].onFinish();
        props.dosenList = [{ ...rows()[0], id: 87, name: 'Baru' }];
        await nextTick();
        expect(text(root)).toContain('Baru');
        expect(text(root)).not.toContain('Aktual 0');
    });
    test('filters reset pages and refreshed shorter lists clamp pagination; CSV selection is real', async () => {
        const { root, props } = mount(rows(17));
        click(root, '3');
        await nextTick();
        expect(text(root)).toContain('Aktual 16');
        const search = walk(root).find((el) =>
            el.placeholder?.startsWith('Cari dosen'),
        );
        search['onUpdate:modelValue']('Aktual');
        await nextTick();
        expect(text(root)).toContain('Aktual 0');
        click(root, '3');
        await nextTick();
        props.dosenList = rows(1);
        await nextTick();
        expect(text(root)).toContain('Aktual 0');
        props.dosenList = rows(17).map((row) => ({
            ...row,
            jabatan: 'Lektor',
        }));
        await nextTick();
        for (const [label, value] of [
            ['Filter program studi', 'S1 Informatika'],
            ['Filter jabatan', 'Lektor'],
        ]) {
            click(root, '3');
            await nextTick();
            const filter = walk(root).find((el) => el['aria-label'] === label);
            filter['onUpdate:modelValue'](value);
            await nextTick();
            expect(text(root)).toContain('Aktual 0');
        }
        click(root, 'Import CSV');
        await nextTick();
        const input = walk(root).find((el) => el.type === 'file');
        expect(input.accept).toBe('.csv,text/csv');
        const template = walk(root).find(
            (el) => el.href === '/kaprodi/dosen/template',
        );
        expect(text(template)).toContain('Unduh template CSV');
        expect(text(root)).toContain('NIP/username dan email');
    });
    test('CSV file preview shows row errors, gates commit and waits for persistence confirmation', async () => {
        const { root, props } = mount(rows());
        click(root, 'Import CSV');
        await nextTick();
        const input = walk(root).find((el) => el.type === 'file');
        const file = new File(
            ['name,email,nip,prodi,jabatan\nA,a@example.test,001,IF,'],
            'actual.csv',
        );
        previewResponse = {
            valid: false,
            errors: [],
            rows: [
                {
                    row: 2,
                    data: { name: 'A', nip: '001' },
                    errors: { nip: ['Duplikat NIP'] },
                },
            ],
        };
        await input.onChange({ target: { files: [file] } });
        await nextTick();
        expect(text(root)).toContain('Duplikat NIP');
        click(root, 'Simpan import CSV');
        expect(requests).toHaveLength(0);
        previewResponse = {
            valid: true,
            errors: [],
            rows: [{ row: 2, data: { name: 'A', nip: '001' }, errors: {} }],
        };
        await input.onChange({ target: { files: [file] } });
        await nextTick();
        click(root, 'Simpan import CSV');
        expect(requests[0].slice(0, 2)).toEqual([
            'post',
            '/kaprodi/dosen/import',
        ]);
        expect(requests[0][2].file).toBe(file);
        expect(text(root)).not.toContain('berhasil');
        requests[0][3].onError({ file: 'CSV ditolak' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('CSV ditolak');
        click(root, 'Simpan import CSV');
        requests[1][3].onSuccess({
            props: { flash: { success: 'CSV tersimpan' } },
        });
        requests[1][3].onFinish();
        props.dosenList = [{ ...rows()[0], id: 90, name: 'A' }];
        await nextTick();
        expect(text(root)).toContain('CSV tersimpan');
        expect(text(root)).not.toContain('Aktual 0');
        await input.onChange({
            target: { files: [new File(['bad'], 'bad.xlsx')] },
        });
        await nextTick();
        expect(text(root)).toContain('Pilih CSV UTF-8');
    });
    test('real and empty refreshed props never show fixtures or quotas', async () => {
        const { root, props } = mount(rows());
        expect(text(root)).toContain('Aktual 0');
        expect(text(root)).toContain('belum tersedia');
        props.dosenList = [];
        await nextTick();
        expect(text(root)).not.toContain('Aktual 0');
        expect(text(root)).toContain('Belum ada dosen terdaftar');
        expect(text(root)).not.toContain('Tejo');
    });
});
