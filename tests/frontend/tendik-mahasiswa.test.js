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
const source = readFileSync('resources/js/pages/Tendik/Mahasiswa.vue', 'utf8');
const { descriptor } = parse(
    source.replace(
        "'@/types/tendik-mahasiswa'",
        "'../../types/tendik-mahasiswa'",
    ),
    { filename: 'resources/js/pages/Tendik/Mahasiswa.vue' },
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
    mockMahasiswa: [],
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
const previews = [];
const Directory = runInNewContext(`(function () { ${code} })()`, {
    imports,
    Error,
    AbortController,
    FormData,
    document: { cookie: 'XSRF-TOKEN=test-token' },
    fetch: (...args) => {
        previews.push(args);
        return Promise.resolve(previews.response);
    },
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
        nim_nip: null,
        username: null,
        prodi: 'S1 Informatika',
        jabatan: null,
    }));
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
        dbStudents: list,
        flash: { success: null, error: null },
    });
    const root = node();
    renderer
        .createApp({ render: () => h(Directory, { ...props }) })
        .mount(root);
    return { root, props };
};
describe('student directory actual SFC', () => {
    test('preview ignores stale responses, handles network failure and clears invalid selections', async () => {
        const { root } = mount();
        const input = walk(root).find((el) => el.type === 'file');
        const file = new File(
            ['name,email\nFirst,first@example.test'],
            'first.csv',
        );
        let resolve;
        previews.response = {
            ok: true,
            json: () =>
                new Promise((done) => {
                    resolve = done;
                }),
        };
        const pending = input.onChange({ target: { files: [file] } });
        await Promise.resolve();
        await input.onChange({ target: { files: [] } });
        resolve({
            count: 1,
            rows: [{ name: 'Stale preview', email: 'stale@example.test' }],
        });
        await pending;
        await nextTick();
        expect(text(root)).not.toContain('Stale preview');
        previews.response = {
            ok: true,
            json: async () => {
                throw new Error('Network failed');
            },
        };
        await input.onChange({ target: { files: [file] } });
        await nextTick();
        expect(text(root)).toContain('Network failed');
        await input.onChange({
            target: { files: [new File(['wrong'], 'wrong.xlsx')] },
        });
        await nextTick();
        expect(text(root)).toContain('Pilih CSV UTF-8');
        expect(
            walk(root).some((el) => text(el).trim() === 'Simpan import CSV'),
        ).toBe(false);
    });
    test('CSV uses server preview, invalidates on file changes and confirms import', async () => {
        const { root } = mount();
        previews.length = 0;
        const input = walk(root).find((el) => el.type === 'file');
        expect(input).toBeDefined();
        const file = new File(
            ['name,email\nCSV Student,csv@example.test\n'],
            'students.csv',
        );
        previews.response = {
            ok: true,
            json: async () => ({
                count: 1,
                rows: [{ name: 'CSV Student', email: 'csv@example.test' }],
            }),
        };
        await input.onChange({ target: { files: [file] } });
        await nextTick();
        expect(previews[0][0]).toBe('/tendik/mahasiswa/import-preview');
        expect(previews[0][1].headers['X-XSRF-TOKEN']).toBe('test-token');
        expect(text(root)).toContain('CSV Student');
        click(root, 'Simpan import CSV');
        click(root, 'Simpan import CSV');
        expect(requests).toHaveLength(1);
        expect(requests[0][1]).toBe('/tendik/mahasiswa/import');
        expect(requests[0][2].file).toBe(file);
        requests[0][3].onSuccess({ props: { flash: {} } });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Konfirmasi server belum diterima');
        click(root, 'Simpan import CSV');
        requests[1][3].onSuccess({
            props: { flash: { success: 'Imported by server' } },
        });
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Imported by server');
        expect(text(root)).not.toContain('CSV Student');
        previews.response = {
            ok: false,
            json: async () => ({ errors: { file: ['Duplicate email'] } }),
        };
        await input.onChange({ target: { files: [file] } });
        await nextTick();
        expect(text(root)).toContain('Duplicate email');
        expect(
            walk(root).some((el) => text(el).trim() === 'Simpan import CSV'),
        ).toBe(false);
        expect(
            walk(root).find((el) => el.href === '/tendik/mahasiswa/template'),
        ).toBeDefined();
    });
    test('create edits and history-protected deletion await server confirmation', async () => {
        const { root, props } = mount(rows());
        click(root, 'Tambah Mahasiswa');
        await nextTick();
        const set = (label, value) =>
            walk(root)
                .find((el) => el['aria-label'] === label)
                ['onUpdate:modelValue'](value);
        set('Nama mahasiswa', 'New student');
        set('Email mahasiswa', 'new@example.test');
        click(root, 'Simpan Akun');
        click(root, 'Simpan Akun');
        expect(requests).toHaveLength(1);
        expect(requests[0].slice(0, 3)).toEqual([
            'post',
            '/tendik/mahasiswa',
            { name: 'New student', email: 'new@example.test' },
        ]);
        requests[0][3].onSuccess({ props: { flash: {} } });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Konfirmasi server belum diterima');
        expect(props.dbStudents).toHaveLength(1);
        click(root, 'Simpan Akun');
        requests[1][3].onError({ email: 'Email already registered' });
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Email already registered');
        click(root, 'Simpan Akun');
        requests[2][3].onSuccess({
            props: { flash: { success: 'Created on server' } },
        });
        requests[2][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Created on server');
        click(root, 'Edit');
        await nextTick();
        set('Nama mahasiswa', 'Updated');
        click(root, 'Simpan Akun');
        expect(requests[3].slice(0, 3)).toEqual([
            'put',
            '/tendik/mahasiswa/40',
            { name: 'Updated', email: 'd0@example.test' },
        ]);
        requests[3][3].onSuccess({
            props: { flash: { success: 'Updated on server' } },
        });
        requests[3][3].onFinish();
        await nextTick();
        expect(props.dbStudents[0].name).toBe('Aktual 0');
        click(root, 'Hapus');
        await nextTick();
        expect(text(root)).toContain('riwayat akademik');
        click(root, 'Ya, Hapus Akun');
        expect(requests[4].slice(0, 2)).toEqual([
            'delete',
            '/tendik/mahasiswa/40',
        ]);
        requests[4][2].onError({
            student: 'Academic history prevents deletion',
        });
        requests[4][2].onFinish();
        await nextTick();
        expect(text(root)).toContain('Academic history prevents deletion');
        expect(props.dbStudents).toHaveLength(1);
    });
    test('pagination resets on search and clamps after refreshed rows shrink', async () => {
        const { root, props } = mount(rows(17));
        expect(text(root)).not.toContain('Aktual 16');
        click(root, 'Berikutnya');
        await nextTick();
        expect(text(root)).toContain('Aktual 8');
        const search = walk(root).find((el) =>
            el.placeholder?.startsWith('Cari'),
        );
        search['onUpdate:modelValue']('d0@example.test');
        await nextTick();
        expect(text(root)).toContain('Aktual 0');
        expect(text(root)).toContain('Halaman 1 dari 1');
        search['onUpdate:modelValue']('');
        await nextTick();
        click(root, 'Berikutnya');
        await nextTick();
        props.dbStudents = rows(1);
        await nextTick();
        expect(text(root)).toContain('Aktual 0');
        expect(text(root)).toContain('Halaman 1 dari 1');
        props.dbStudents = [];
        await nextTick();
        expect(text(root)).toContain('Halaman 1 dari 1');
        expect(
            walk(root).find(
                (el) =>
                    el.tagName === 'BUTTON' && text(el).trim() === 'Berikutnya',
            ).disabled,
        ).toBe(true);
        expect(
            walk(root).find(
                (el) =>
                    el.tagName === 'BUTTON' && text(el).trim() === 'Sebelumnya',
            ).disabled,
        ).toBe(true);
    });
    test('reset posts persisted ID once, reports server confirmation and recoverable failures', async () => {
        const { root, props } = mount(rows());
        click(root, 'Reset Password');
        await nextTick();
        click(root, 'Ya, Reset Password');
        click(root, 'Ya, Reset Password');
        await nextTick();
        expect(requests.length).toBe(1);
        expect(requests[0].slice(0, 2)).toEqual([
            'post',
            '/tendik/mahasiswa/40/reset-password',
        ]);
        expect(text(root)).not.toContain('berhasil');
        expect(
            walk(root).find(
                (el) =>
                    el.tagName === 'BUTTON' &&
                    text(el).trim() === 'Ya, Reset Password',
            ).disabled,
        ).toBe(true);
        props.dbStudents = [{ ...rows()[0], name: 'Actual refresh' }];
        await nextTick();
        expect(text(root)).toContain('Actual refresh');
        requests[0][3].onError({ username: 'Username belum tersedia' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Username belum tersedia');
        click(root, 'Ya, Reset Password');
        requests[1][3].onNetworkError();
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Koneksi');
        click(root, 'Ya, Reset Password');
        requests[2][3].onSuccess({
            props: { flash: { error: 'Server menolak reset' } },
        });
        requests[2][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Server menolak reset');
        click(root, 'Ya, Reset Password');
        requests[3][3].onSuccess({ props: { flash: {} } });
        requests[3][3].onFinish();
        await nextTick();
        expect(text(root)).not.toContain('berhasil');
        click(root, 'Ya, Reset Password');
        requests[4][3].onSuccess({
            props: { flash: { success: 'Reset dikonfirmasi server' } },
        });
        requests[4][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Reset dikonfirmasi server');
        props.dbStudents = [];
        await nextTick();
        expect(text(root)).not.toContain('Aktual 0');
        expect(
            walk(root).some(
                (el) => el.onClick && text(el).trim() === 'Ya, Reset Password',
            ),
        ).toBe(false);
    });
    test('live supported fields, search and refreshed props never use fixtures', async () => {
        const { root, props } = mount(rows());
        expect(text(root)).toContain('Aktual 0');
        expect(text(root)).not.toContain('Angkatan');
        expect(text(root)).not.toContain('Tahapan TA');
        props.dbStudents = [];
        await nextTick();
        expect(text(root)).not.toContain('Aktual 0');
        expect(text(root)).toContain('Tidak ada mahasiswa');
        props.dbStudents = [
            { ...rows()[0], name: 'Diperbarui', nim_nip: '009' },
        ];
        await nextTick();
        expect(text(root)).toContain('Diperbarui');
        const search = walk(root).find((el) =>
            el.placeholder?.startsWith('Cari'),
        );
        search['onUpdate:modelValue']('009');
        await nextTick();
        expect(text(root)).toContain('Diperbarui');
        search['onUpdate:modelValue']('absent');
        await nextTick();
        expect(text(root)).not.toContain('Diperbarui');
    });
});
