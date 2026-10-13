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
for (const pageName of ['Ruangan', 'Dashboard', 'Arsip']) {
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
    const text = (el) => [el.text, ...el.children.map(text)].join(' ');

    const walk = (el) => [el, ...el.children.flatMap(walk)];
    const click = (root, label) => {
        const el = walk(root).find(
            (el) =>
                el.tagName === 'BUTTON' &&
                el.onClick &&
                text(el).trim() === label,
        );
        expect(el).toBeDefined();
        el.onClick();
    };
    const room = {
        id: 40,
        nama_ruangan: 'Actual Room',
        gedung: null,
        lantai: '2',
        kapasitas: 12,
        status: 'tidak_tersedia',
        keterangan: 'Perawatan',
    };
    const mount = (input) => {
        requests.length = 0;
        const props = reactive(input);
        const root = node();
        renderer
            .createApp({ render: () => h(Directory, { ...props }) })
            .mount(root);
        return { root, props };
    };
    if (pageName === 'Ruangan')
        describe('rooms actual SFC', () => {
            test('renders reactive persisted room fields without invented facilities or occupancy', async () => {
                const { root, props } = mount({ dbRuanganList: [room] });
                expect(text(root)).toContain('Actual Room');
                expect(text(root)).toContain('Tidak tersedia');
                expect(text(root)).toContain('Perawatan');
                expect(text(root)).not.toContain('Agenda Pemakaian');
                props.dbRuanganList = [];
                await nextTick();
                expect(text(root)).not.toContain('Actual Room');
                expect(text(root)).toContain('Belum ada ruangan');
            });
            test('CRUD sends persisted fields and IDs once, with server-only confirmation and recoverable failures', async () => {
                const { root, props } = mount({ dbRuanganList: [room] });
                click(root, 'Tambah Ruangan');
                await nextTick();
                const input = walk(root).find(
                    (el) => el.name === 'nama_ruangan',
                );
                expect(input).toBeDefined();
                input['onUpdate:modelValue']('New Room');
                click(root, 'Simpan');
                click(root, 'Simpan');
                expect(requests.length).toBe(1);
                expect(requests[0].slice(0, 2)).toEqual([
                    'post',
                    '/tendik/ruangan',
                ]);
                expect(requests[0][2]).toEqual({
                    nama_ruangan: 'New Room',
                    gedung: null,
                    lantai: null,
                    kapasitas: 30,
                    status: 'tersedia',
                    keterangan: null,
                });
                expect(text(root)).not.toContain('berhasil');
                requests[0][3].onError({ nama_ruangan: 'Nama ditolak' });
                requests[0][3].onFinish();
                await nextTick();
                expect(text(root)).toContain('Nama ditolak');
                click(root, 'Simpan');
                requests[1][3].onNetworkError();
                requests[1][3].onFinish();
                await nextTick();
                expect(text(root)).toContain('Koneksi');
                click(root, 'Simpan');
                requests[2][3].onSuccess({
                    props: { flash: { error: 'Server menolak' } },
                });
                requests[2][3].onFinish();
                await nextTick();
                expect(text(root)).toContain('Server menolak');
                click(root, 'Simpan');
                requests[3][3].onSuccess({ props: { flash: {} } });
                requests[3][3].onFinish();
                await nextTick();
                expect(text(root)).toContain(
                    'Konfirmasi server belum diterima',
                );
                click(root, 'Simpan');
                requests[4][3].onSuccess({
                    props: { flash: { success: 'Dikonfirmasi server' } },
                });
                requests[4][3].onFinish();
                await nextTick();
                expect(text(root)).toContain('Dikonfirmasi server');
                expect(props.dbRuanganList).toHaveLength(1);
                click(root, 'Edit');
                await nextTick();
                click(root, 'Simpan');
                expect(requests[5].slice(0, 2)).toEqual([
                    'put',
                    '/tendik/ruangan/40',
                ]);
                expect(requests[5][2]).toEqual(roomPayload());
                requests[5][3].onSuccess({
                    props: { flash: { success: 'Updated server' } },
                });
                requests[5][3].onFinish();
                await nextTick();
                click(root, 'Hapus');
                await nextTick();
                expect(text(root)).toContain(
                    'tidak lagi memiliki referensi ruangan',
                );
                click(root, 'Ya, Hapus');
                click(root, 'Ya, Hapus');
                expect(requests[6].slice(0, 2)).toEqual([
                    'delete',
                    '/tendik/ruangan/40',
                ]);
                expect(props.dbRuanganList).toHaveLength(1);
                requests[6][2].onHttpException();
                requests[6][2].onFinish();
                await nextTick();
                expect(text(root)).toContain('Permintaan gagal');
                props.dbRuanganList = [];
                await nextTick();
                expect(text(root)).toContain(
                    'Data ruangan tidak lagi tersedia',
                );
                const before = requests.length;
                click(root, 'Ya, Hapus');
                expect(requests.length).toBe(before);
            });
        });
    const roomPayload = () => ({
        nama_ruangan: room.nama_ruangan,
        gedung: room.gedung,
        lantai: room.lantai,
        kapasitas: room.kapasitas,
        status: room.status,
        keterangan: room.keterangan,
    });
    if (pageName === 'Dashboard')
        test('dashboard displays supported server stats reactively and no fixture print/month metrics', async () => {
            const { root, props } = mount({
                stats: {
                    antreanVerifikasi: 7,
                    antreanSempro: 4,
                    antreanSidang: 3,
                    totalRuangan: 9,
                    ruanganTersedia: 5,
                    totalMahasiswa: 13,
                },
            });
            expect(text(root)).toContain('Antrean Verifikasi: 7');
            expect(text(root)).toContain('Total Ruangan: 9');
            expect(text(root)).toContain('Ruangan Tersedia: 5');
            expect(text(root)).toContain('Akun Mahasiswa: 13');
            expect(text(root)).toContain('4 Sempro');
            expect(text(root)).toContain('3 Sidang');
            expect(text(root)).not.toContain('Bulan ini');
            expect(text(root)).not.toContain('28 Dokumen');
            props.stats = {
                antreanVerifikasi: 0,
                antreanSempro: 0,
                antreanSidang: 0,
                totalRuangan: 0,
                ruanganTersedia: 0,
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
