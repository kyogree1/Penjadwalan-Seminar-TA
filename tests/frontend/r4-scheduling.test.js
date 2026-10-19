let fetchStub;
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
    'resources/js/pages/Kaprodi/Penjadwalan.vue',
    'utf8',
);
const { descriptor } = parse(
    source.replace("'@/types/scheduling'", "'../../types/scheduling'"),
    { filename: 'resources/js/pages/Kaprodi/Penjadwalan.vue' },
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
        patch: (...args) => requests.push(['patch', ...args]),
        get: (...args) => requests.push(['get', ...args]),
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
    AbortController,
    Error,
    fetch: (...args) => fetchStub(...args),
    document: { cookie: 'XSRF-TOKEN=csrf' },
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
    const el = walk(root).find((el) => el.onClick && text(el).trim() === label);
    expect(el).toBeDefined();
    el.onClick();
};
const period = {
    id: 7,
    name: 'Real period',
    tipe: 'sempro',
    gelombang: 1,
    capacity: 10,
    start_date: '2026-11-01',
    end_date: '2026-11-02',
    is_open: true,
    slots: [
        {
            tanggal: '2026-11-01',
            jam_mulai: '09:00:00',
            jam_selesai: '10:30:00',
        },
    ],
};
const row = {
    id: 42,
    period_id: 7,
    pendaftaran_id: 90,
    mahasiswa_id: 300,
    tipe: 'sempro',
    penguji_1_id: 11,
    penguji_2_id: 12,
    tanggal: '2026-11-01',
    jam_mulai: '09:00:00',
    jam_selesai: '10:30:00',
    status: 'terpublikasi',
    mahasiswa: { name: 'Actual student' },
    penguji1: { name: 'Actual examiner' },
    penguji2: { name: 'Second examiner' },
};
const mount = () => {
    requests.length = 0;
    const props = reactive({
        periods: [period],
        selectedPeriodId: 7,
        penjadwalanList: [row],
        dosenOptions: [
            { id: 11, name: 'Actual examiner' },
            { id: 12, name: 'Second examiner' },
        ],
        capabilities: {
            lecturerKbk: false,
            availability: false,
            lecturerQuota: false,
        },
    });
    const root = node();
    const app = renderer.createApp({
        render: () => h(Directory, { ...props }),
    });
    app.mount(root);
    return { root, props, app };
};
const flush = async () => {
    await new Promise((resolve) => setTimeout(resolve, 0));
    await nextTick();
};
describe('real scheduling SFC', () => {
    test('CSV download is a real period-scoped anchor; XLSX/PDF stay unavailable', async () => {
        const { root, props, app } = mount();
        const download = () =>
            walk(root).find(
                (el) =>
                    el.tagName === 'A' && text(el).includes('Unduh jadwal CSV'),
            );
        expect(download().href).toBe('/kaprodi/penjadwalan/export?period_id=7');
        expect(download().onClick).toBeUndefined();
        expect(text(root)).toContain('XLSX dan PDF belum tersedia');
        props.selectedPeriodId = null;
        await nextTick();
        expect(download().href).toBe('/kaprodi/penjadwalan/export');
        app.unmount();
    });
    test('reload and empty props preserve server state without fixtures', async () => {
        const { root, props, app } = mount();
        expect(text(root)).toContain('Actual student');
        expect(text(root)).not.toContain('0.985');
        props.penjadwalanList = [];
        props.periods = [];
        await nextTick();
        expect(text(root)).not.toContain('Actual student');
        expect(text(root)).toContain('Belum ada');
        app.unmount();
    });
    test('generate previews partial outcomes and saves only after confirmation', async () => {
        const calls = [];
        fetchStub = async (url, options) => {
            calls.push([url, options]);
            return {
                ok: true,
                json: async () => ({
                    outcome: 'partial',
                    success: false,
                    period_id: 7,
                    schedule: [
                        {
                            ...row,
                            nama: 'Preview student',
                            supervisor_ids: [10],
                            judul: 'Actual topic',
                            kbk: 'AI',
                        },
                    ],
                    unscheduled: [{ pendaftaran_id: 91, tipe: 'sempro' }],
                    fitness: 0.25,
                    kbk_match: 0,
                    workload_balance: 0.5,
                    generations: 10,
                    logs: ['Real log'],
                    limitations: ['Metadata unavailable'],
                }),
            };
        };
        const { root, app } = mount();
        click(root, 'Generate');
        await flush();
        expect(calls[0][0]).toBe('/kaprodi/penjadwalan/generate-ga');
        expect(JSON.parse(calls[0][1].body).period_id).toBe(7);
        expect(text(root)).toContain('partial');
        expect(text(root)).toContain('Preview student');
        expect(requests).toHaveLength(0);
        click(root, 'Publikasikan preview');
        expect(requests[0].slice(0, 2)).toEqual([
            'post',
            '/kaprodi/penjadwalan/simpan',
        ]);
        expect(requests[0][2].schedules[0].pendaftaran_id).toBe(90);
        expect(requests[0][2].schedules[0].mahasiswa_id).toBe(300);
        requests[0][3].onError({ schedules: 'Konflik booking' });
        requests[0][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Konflik booking');
        expect(text(root)).toContain('Preview student');
        click(root, 'Publikasikan preview');
        requests[1][3].onSuccess({ props: { flash: {} } });
        await nextTick();
        expect(text(root)).toContain('Penyimpanan belum dikonfirmasi.');
        expect(text(root)).toContain('Preview student');
        requests[1][3].onSuccess({
            props: { flash: { success: 'Server published' } },
        });
        requests[1][3].onFinish();
        await nextTick();
        expect(text(root)).toContain('Server published');
        expect(text(root)).not.toContain('Preview student');
        app.unmount();
    });
    test('network errors, period navigation and abort on unmount are real', async () => {
        fetchStub = async () => {
            throw new Error('Network offline');
        };
        const { root, app } = mount();
        click(root, 'Generate');
        await flush();
        expect(text(root)).toContain('Network offline');
        fetchStub = async () => {
            throw 'Unexpected rejection';
        };
        click(root, 'Generate');
        await flush();
        expect(text(root)).toContain('Generate gagal. Coba lagi.');
        const select = walk(root).find((el) => el.id === 'selected-period');
        select['onUpdate:modelValue'](7);
        select.onChange({ target: select });
        await nextTick();
        expect(requests[0].slice(0, 2)).toEqual([
            'get',
            '/kaprodi/penjadwalan',
        ]);
        let signal;
        fetchStub = (_url, options) => {
            signal = options.signal;
            return new Promise(() => {});
        };
        click(root, 'Generate');
        await nextTick();
        app.unmount();
        expect(signal.aborted).toBe(true);
    });
});
