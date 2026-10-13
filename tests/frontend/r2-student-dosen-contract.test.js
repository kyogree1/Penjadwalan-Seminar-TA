import { describe, expect, test } from 'bun:test';
import { readFileSync, existsSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';

const requests = [];
const page = Vue.reactive({
    props: {
        auth: {
            user: { name: 'Aktual', nim_nip: 'NIM aktual', role: 'dosen' },
        },
    },
});
function setup(path, props) {
    const source = readFileSync(
        `resources/js/pages/${path}.vue`,
        'utf8',
    ).replace(
        /'@\/types\//g,
        path.includes('/') ? "'../../types/" : "'../types/",
    );
    const { descriptor } = parse(source, {
        filename: `resources/js/pages/${path}.vue`,
    });
    const compiled = compileScript(descriptor, {
        id: path,
        fs: {
            fileExists: existsSync,
            readFile: (p) => readFileSync(p, 'utf8'),
        },
    });
    const imports = {
        ...Vue,
        ...Icons,
        // This setup-only harness intentionally has no mounted lifecycle.
        onMounted() {},
        onUnmounted() {},
        usePage: () => page,
        useForm: (values) =>
            Vue.reactive({
                ...values,
                errors: {},
                processing: false,
                post: (...args) => requests.push(args),
                patch: (...args) => requests.push(args),
                clearErrors() {},
                setError(field, error) {
                    this.errors[field] = error;
                },
            }),
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
            (_, name) => `const ${name} = {};`,
        )
        .replace('export default', 'return');
    const component = runInNewContext(`(function () { ${code} })()`, {
        imports,
    });
    return component.setup(props, { expose() {} });
}

// Mount the compiled title template so option bindings and native v-model run.
function mountTitle(props) {
    const { descriptor } = parse(
        readFileSync('resources/js/pages/Pendaftaran/Judul.vue', 'utf8'),
        { filename: 'resources/js/pages/Pendaftaran/Judul.vue' },
    );
    const compiled = compileScript(descriptor, {
        id: 'title-select-regression',
        inlineTemplate: true,
        templateOptions: { compilerOptions: { hoistStatic: false } },
    });
    const wrapper = Vue.defineComponent({
        setup:
            (_, { slots }) =>
            () =>
                Vue.h('div', [slots.default?.(), slots.action?.()]),
    });
    let posted;
    const imports = {
        ...Vue,
        ...Icons,
        Head: wrapper,
        Card: wrapper,
        PageHeaderBox: wrapper,
        StatusBadge: wrapper,
        Button: Vue.defineComponent({
            setup:
                (_, { slots }) =>
                () =>
                    Vue.h('button', slots.default?.()),
        }),
        usePage: () => page,
        useForm: (values) =>
            Vue.reactive({
                ...values,
                errors: {},
                processing: false,
                post(url) {
                    posted = {
                        url,
                        pembimbing_1: this.pembimbing_1,
                        pembimbing_2: this.pembimbing_2,
                    };
                },
            }),
    };
    const code = new Bun.Transpiler({ loader: 'ts' })
        .transformSync(compiled.content)
        .replace(
            /import\s+\{([^}]+)\}\s+from\s+["'][^"']+["'];?/g,
            (_, names) => `const {${names.replace(/\bas\b/g, ':')}} = imports;`,
        )
        .replace(
            /import\s+(\w+)\s+from\s+["'][^"']+["'];?/g,
            (_, name) => `const ${name} = imports.${name};`,
        )
        .replace('export default', 'return');
    const component = runInNewContext(`(function () { ${code} })()`, {
        imports,
    });
    const node = (text = '') => ({
        text,
        children: [],
        parent: null,
        value: '',
        get options() {
            const result = [];
            const visit = (el) => {
                if (el.tagName === 'OPTION') result.push(el);
                el.children.forEach(visit);
            };
            this.children.forEach(visit);
            return result;
        },
        addEventListener(event, fn) {
            this[event] = fn;
        },
        removeEventListener() {},
        getRootNode: () => ({ activeElement: null }),
    });
    const renderer = Vue.createRenderer({
        createElement: (tag) =>
            Object.assign(node(), { tagName: tag.toUpperCase() }),
        createText: node,
        createComment: () => node(),
        setText: (el, text) => {
            el.text = text;
        },
        setElementText: (el, text) => {
            el.text = text;
            el.children = [];
        },
        patchProp: (el, key, _old, value) => {
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
    const root = node();
    const app = renderer.createApp(component, props);
    app.mount(root);
    const find = (predicate) => {
        const visit = (el) =>
            predicate(el) ? el : el.children.map(visit).find(Boolean);
        return visit(root);
    };
    return { find, posted: () => posted, unmount: () => app.unmount() };
}

describe('title native select contract', () => {
    test('relation-only title hydration uses IDs and never falls back to lecturer names', async () => {
        const props = Vue.reactive({
            pengajuan: {
                status: 'ditolak',
                pembimbing_1: { id: 41, name: 'Same lecturer name' },
                pembimbing_2: { id: 72, name: 'Same lecturer name' },
            },
            dosenList: [],
        });
        const state = setup('Pendaftaran/Judul', props);
        expect(state.form.pembimbing_1).toBe('41');
        expect(state.form.pembimbing_2).toBe('72');
        props.pengajuan = {
            status: 'revisi',
            pembimbing_1: { id: 72, name: 'Same lecturer name' },
        };
        await Vue.nextTick();
        expect(state.form.pembimbing_1).toBe('72');
        expect(state.form.pembimbing_2).toBe('');
    });
    test('actual title select submits lecturer IDs even when display names duplicate', async () => {
        const view = mountTitle({
            pengajuan: null,
            dosenList: [
                { id: 41, name: 'Same lecturer name' },
                { id: 72, name: 'Same lecturer name' },
            ],
        });
        for (const [id, index] of [
            ['pembimbing_1', 1],
            ['pembimbing_2', 2],
        ]) {
            const select = view.find((el) => el.id === id);
            select.options.forEach((option, i) => {
                option.selected = i === index;
            });
            select.change({ target: select });
        }
        await Vue.nextTick();
        view.find((el) => el.tagName === 'FORM').onSubmit({
            preventDefault() {},
        });
        expect(view.posted()).toEqual({
            url: '/pendaftaran/judul',
            pembimbing_1: '41',
            pembimbing_2: '72',
        });
        view.unmount();
    });
});

describe('Track H recovery and pagination', () => {
    test('examiner document controls consume only authorized server links', () => {
        const source = readFileSync(
            'resources/js/pages/Dosen/Penilaian.vue',
            'utf8',
        );
        expect(source).toContain('v-for="document in schedule.documents"');
        expect(source).toContain(':href="document.url"');
        expect(source).toContain('Dokumen privat belum tersedia');
        expect(source).not.toContain('/storage/');
    });
    test('student recovery and portal copy do not invent unavailable capabilities', () => {
        const title = readFileSync(
            'resources/js/pages/Pendaftaran/Judul.vue',
            'utf8',
        );
        expect(title).not.toContain('e-TTD');
        expect(title).not.toContain('tidak bisa melakukan pengajuan lagi');
        const catalogue = readFileSync(
            'resources/js/pages/Katalog/Index.vue',
            'utf8',
        );
        expect(catalogue).not.toContain('Anda belum menyelesaikan sidang');
        const dashboard = readFileSync(
            'resources/js/pages/Kaprodi/Dashboard.vue',
            'utf8',
        );
        expect(dashboard).not.toContain('SK Pembimbing');
        expect(dashboard).not.toContain('menyimpan periode akademik');
        const logbook = readFileSync(
            'resources/js/pages/Pendaftaran/Bimbingan.vue',
            'utf8',
        );
        expect(logbook).toContain(':disabled="currentPage >= totalPages"');
    });
    test('rejected title can resubmit and pending title stays locked', () => {
        requests.length = 0;
        const props = Vue.reactive({
            pengajuan: { status: 'ditolak' },
            dosenList: [],
        });
        const state = setup('Pendaftaran/Judul', props);
        expect(state.canEditForm.value).toBe(true);
        state.submitForm();
        expect(requests[0][0]).toBe('/pendaftaran/judul');
        props.pengajuan.status = 'menunggu';
        expect(state.canEditForm.value).toBe(false);
    });
    test('academic revision permits correction with retained files but active exams stay locked', () => {
        requests.length = 0;
        const props = Vue.reactive({
            sempro: {
                status: 'verifikasi_tendik',
                status_akademik: 'revisi',
                catatan: 'Admin',
                catatan_kaprodi: 'Academic correction',
                proposal_file: 'proposal.pdf',
                turnitin_file: 'turnitin.pdf',
                lembar_kehadiran_file: 'attendance.pdf',
            },
        });
        const state = setup('Pendaftaran/Sempro', props);
        expect(state.canEdit.value).toBe(true);
        expect(state.academicNote.value).toBe('Academic correction');
        expect(state.revisionNote.value).toBe('Admin');
        state.submitSempro();
        expect(requests[0][0]).toBe('/pendaftaran/sempro');
        for (const status of ['terjadwal', 'selesai']) {
            props.sempro.status = status;
            expect(state.canEdit.value).toBe(false);
        }
    });
    test('logbook resets filters and page size and clamps refreshed empty results', async () => {
        const props = Vue.reactive({
            bimbinganList: Array.from({ length: 25 }, (_, id) => ({
                id,
                tanggal: '2026-10-01',
                dosen: 'Dosen',
                rangkuman: `Entry ${id}`,
                keterangan: 'Topic',
                status: 'process',
            })),
            dosenList: [],
        });
        const state = setup('Pendaftaran/Bimbingan', props);
        state.currentPage.value = 3;
        state.searchQuery.value = 'Entry 1';
        await Vue.nextTick();
        expect(state.currentPage.value).toBe(1);
        state.searchQuery.value = '';
        await Vue.nextTick();
        state.currentPage.value = 3;
        state.entriesPerPage.value = 20;
        await Vue.nextTick();
        expect(state.currentPage.value).toBe(1);
        state.currentPage.value = 2;
        props.bimbinganList = props.bimbinganList.slice(0, 1);
        await Vue.nextTick();
        expect(state.currentPage.value).toBe(1);
        props.bimbinganList = [];
        await Vue.nextTick();
        expect(state.totalPages.value).toBe(1);
        expect(state.paginatedList.value).toEqual([]);
    });
});

describe('R2 actual page reactive contracts', () => {
    test('registration feedback reflects only server flash', () => {
        for (const path of [
            'Pendaftaran/Judul',
            'Pendaftaran/Sempro',
            'Pendaftaran/Sidang',
            'Pendaftaran/Bimbingan',
        ]) {
            page.props.flash = {};
            const state = setup(
                path,
                Vue.reactive({
                    pengajuan: null,
                    sempro: null,
                    sidang: null,
                    defaultJudul: null,
                    dosenList: [],
                    bimbinganList: [],
                }),
            );
            expect(state.feedback.value.success).toBeUndefined();
            page.props.flash = { success: 'Server confirmed' };
            expect(state.feedback.value.success).toBe('Server confirmed');
        }
    });
    test('student logbook refresh and empty lecturer options never substitute fixtures', () => {
        const props = Vue.reactive({
            bimbinganList: [
                {
                    id: 8,
                    tanggal: '2026-10-01',
                    dosen: { id: 4, name: 'Aktual' },
                    rangkuman: 'Server',
                    keterangan: 'Topik',
                    status: 'delivered',
                },
            ],
            dosenList: [],
        });
        const state = setup('Pendaftaran/Bimbingan', props);
        expect(state.bimbinganList.value[0].id).toBe(8);
        expect(state.dosenOptions.value).toEqual([]);
        props.bimbinganList = [];
        expect(state.bimbinganList.value).toEqual([]);
    });
    test('student logbook keeps failed form open and only clears after server success', () => {
        requests.length = 0;
        const state = setup(
            'Pendaftaran/Bimbingan',
            Vue.reactive({ bimbinganList: [], dosenList: [] }),
        );
        state.form.tanggal = '2026-10-11';
        state.form.dosen = '4';
        state.form.rangkuman = 'Actual summary';
        state.form.keterangan = 'Actual topic';
        state.showModal.value = true;
        let resets = 0;
        state.form.reset = () => {
            resets++;
        };
        state.submitBimbingan();
        expect(requests[0][0]).toBe('/pendaftaran/bimbingan');
        requests[0][1].onNetworkError();
        expect(state.requestError.value).toContain('Koneksi gagal');
        expect(state.showModal.value).toBe(true);
        expect(state.form.rangkuman).toBe('Actual summary');
        requests[0][1].onSuccess({ props: { flash: {} } });
        expect(state.requestError.value).toContain('Konfirmasi server');
        expect(resets).toBe(0);
        requests[0][1].onSuccess({
            props: { flash: { success: 'Server confirmed' } },
        });
        expect(state.showModal.value).toBe(false);
        expect(resets).toBe(1);
        expect(state.bimbinganList.value).toEqual([]);
    });
    test('title submission waits for server props, and empty lecturer options stay empty', () => {
        requests.length = 0;
        const props = Vue.reactive({ pengajuan: null, dosenList: [] });
        const state = setup('Pendaftaran/Judul', props);
        expect(state.availableDosenList.value).toEqual([]);
        state.submitForm();
        expect(state.applicationStatus.value).toBe('draft');
        expect(requests[0][0]).toBe('/pendaftaran/judul');
        props.pengajuan = { id: 5, judul_ta: 'Server', status: 'menunggu' };
        expect(state.applicationStatus.value).toBe('diajukan');
    });
    test('registration schedules do not resurrect fixtures after empty refresh', () => {
        const props = Vue.reactive({
            registrantsData: [{ name: 'Aktual', nim: 'real' }],
            jadwalSempro: [],
            jadwalSidang: [],
        });
        const state = setup('Mahasiswa/PendaftaranJadwal', props);
        expect(state.activeRegistrants.value[0].name).toBe('Aktual');
        props.registrantsData = [];
        expect(state.activeRegistrants.value).toEqual([]);
        expect(state.schedules.value).toEqual([]);
    });
    test('dosen dashboard uses server stats and refreshes zero counts', () => {
        const props = Vue.reactive({
            stats: { pendingBimbingan: 7, jadwalUjiCount: 3 },
        });
        const state = setup('Dosen/Dashboard', props);
        expect(state.stats.value.pendingBimbingan).toBe(7);
        props.stats = { pendingBimbingan: 0, jadwalUjiCount: 0 };
        expect(state.stats.value.pendingBimbingan).toBe(0);
    });
    test('assessment posts quantifiable scores and waits for refreshed server records', () => {
        requests.length = 0;
        page.props.flash = {};
        const props = Vue.reactive({
            dbJadwalUji: [
                {
                    id: 9,
                    dapat_menilai: true,
                    status: 'terpublikasi',
                    penilaian_saya: null,
                },
            ],
        });
        const state = setup('Dosen/Penilaian', props);
        state.selectExam(props.dbJadwalUji[0]);
        state.form.nilai = 82.5;
        state.form.catatan = 'Actual note';
        state.submit();
        expect(requests[0][0]).toBe('/dosen/penilaian/9');
        expect(state.form.nilai).toBe(82.5);
        expect(props.dbJadwalUji[0].penilaian_saya).toBeNull();
        expect(state.feedback.value.success).toBeUndefined();
        props.dbJadwalUji = [
            {
                id: 9,
                dapat_menilai: false,
                penilaian_saya: { nilai: 82.5, catatan: 'Actual note' },
            },
        ];
        expect(state.schedules.value[0].penilaian_saya.nilai).toBe(82.5);
        state.submit();
        expect(requests.length).toBe(1);
    });
    test('assessment exposes current server schedules instead of fictitious candidates', () => {
        const props = Vue.reactive({
            dbJadwalUji: [
                {
                    id: 9,
                    mahasiswa: { name: 'Aktual' },
                    status: 'terpublikasi',
                },
            ],
        });
        const state = setup('Dosen/Penilaian', props);
        expect(state.schedules.value[0].id).toBe(9);
        props.dbJadwalUji = [];
        expect(state.schedules.value).toEqual([]);
    });
    test('profile identity follows server refresh without invented signature/contact', () => {
        const props = Vue.reactive({
            user: {
                name: 'Aktual',
                email: 'real@example.test',
                role: 'dosen',
                nim_nip: 'NIP aktual',
            },
        });
        const state = setup('Profile/Index', props);
        expect(state.profile.value.name).toBe('Aktual');
        props.user = {
            name: 'Baru',
            email: 'new@example.test',
            role: 'tendik',
        };
        expect(state.profile.value.name).toBe('Baru');
        expect(state.profile.value.phone).toBeUndefined();
    });
    test('profile submission targets server and never claims success locally', () => {
        requests.length = 0;
        page.props.flash = {};
        const state = setup(
            'Profile/Index',
            Vue.reactive({
                user: {
                    id: 4,
                    name: 'Original',
                    email: 'real@example.test',
                    role: 'dosen',
                },
            }),
        );
        state.form.name = 'Updated';
        state.submit();
        expect(requests[0][0]).toBe('/profile');
        expect(state.feedback.value.success).toBeUndefined();
        expect(
            Object.keys(state.form).filter((key) =>
                ['name', 'email', 'phone', 'role'].includes(key),
            ),
        ).toEqual(['name', 'email']);
    });
    test('file upload limits match server field limits', () => {
        const sempro = setup(
            'Pendaftaran/Sempro',
            Vue.reactive({ sempro: null, defaultJudul: null }),
        );
        sempro.setFile('turnitin_file', {
            target: {
                files: [
                    {
                        name: 'file.pdf',
                        type: 'application/pdf',
                        size: 6 * 1024 * 1024,
                    },
                ],
                value: 'file.pdf',
            },
        });
        expect(sempro.form.turnitin_file).toBeNull();
        expect(sempro.fileErrors.value.turnitin_file).toContain('5 MB');
        const sidang = setup(
            'Pendaftaran/Sidang',
            Vue.reactive({ sidang: null, defaultJudul: null }),
        );
        sidang.form.turnitin_file = {
            name: 'file.pdf',
            type: 'application/pdf',
            size: 6 * 1024 * 1024,
        };
        expect(sidang.validationErrors.value.turnitin_file).toContain('5 MB');
    });
    test('shell does not consume fixture notifications as real account activity', () => {
        const source = readFileSync(
            'resources/js/layouts/partials/Topbar.vue',
            'utf8',
        );
        expect(source).not.toContain('useNotifications');
        expect(source).toContain('Notifikasi server belum tersedia');
    });
    test('catalogue has no fabricated publications when no server catalogue exists', () => {
        const state = setup('Katalog/Index', {});
        expect(state.filteredSkripsiList.value).toEqual([]);
    });
    test('sempro status and form refresh from server without a fictional title', async () => {
        const props = Vue.reactive({ sempro: null, defaultJudul: null });
        const state = setup('Pendaftaran/Sempro', props);
        expect(state.form.judul_ta).toBe('');
        props.sempro = {
            id: 4,
            judul_ta: 'Server',
            bentuk_ta: 'Skripsi',
            status: 'ditolak',
            catatan: 'Berkas ditolak',
        };
        await Vue.nextTick();
        expect(state.status.value).toBe('ditolak');
        expect(state.form.judul_ta).toBe('Server');
    });
    test('sidang form does not substitute a fictional title and refreshes from server', async () => {
        const props = Vue.reactive({ sidang: null, defaultJudul: null });
        const state = setup('Pendaftaran/Sidang', props);
        expect(state.form.judul_ta).toBe('');
        props.sidang = {
            id: 5,
            judul_ta: 'Server Sidang',
            lokasi_mitra: 'Aktual',
            status: 'menunggu',
        };
        await Vue.nextTick();
        expect(state.form.judul_ta).toBe('Server Sidang');
    });
    test('student dashboard preserves authoritative empty roadmaps after refresh', () => {
        const props = Vue.reactive({
            dbSemproSteps: [{ name: 'Aktual', status: 'pending' }],
            dbSidangSteps: [],
            dbJadwal: null,
            dbTotalBimbinganAcc: 0,
        });
        const state = setup('Dashboard', props);
        expect(state.semproSteps.value[0].name).toBe('Aktual');
        props.dbSemproSteps = [];
        expect(state.semproSteps.value).toEqual([]);
        expect(state.sidangSteps.value).toEqual([]);
        props.dbSempro = { id: 7, judul_ta: 'Judul server', status: 'ditolak' };
        expect(state.currentStatusCards.value[0].badgeText).toBe('ditolak');
        props.dbSempro = null;
        expect(state.currentStatusCards.value).toEqual([]);
    });
});
