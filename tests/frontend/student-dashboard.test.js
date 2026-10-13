import { expect, test } from 'bun:test';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import { createRenderer, defineComponent, h, nextTick, reactive } from 'vue';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';
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
    usePage: () => ({ props: { auth: { user: { name: 'Student' } } } }),
    Head: wrapper,
    Link: wrapper,
    Card: wrapper,
    StatCard: stat,
    PageHeaderBox: wrapper,
};
function compile(path) {
    const { descriptor } = parse(readFileSync(path, 'utf8'), {
        filename: path,
    });
    const compiled = compileScript(descriptor, {
        id: path,
        inlineTemplate: true,
        templateOptions: { compilerOptions: { hoistStatic: false } },
    });
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
    return runInNewContext(`(function () { ${code} })()`, { imports });
}

const node = (text = '') => ({ text, children: [], parent: null, props: {} });
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
    patchProp: (el, key, previous, next) => {
        el.props[key] = next;
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

test('assigned team renders approved supervisors and examiners then clears on refresh', async () => {
    const { props, root } = mount();
    expect(text(root)).toContain('Dosen Pembimbing & Penguji');
    expect(text(root)).toContain('Pembimbing belum ditetapkan');
    expect(text(root)).toContain('Penguji belum ditetapkan');
    props.dbApprovedTitle = {
        id: 4,
        status: 'disetujui',
        judul_ta: 'Research',
        pembimbing1: { name: 'Title Supervisor One' },
        pembimbing2: { name: 'Title Supervisor Two' },
    };
    props.dbJadwalList = [exam(1, '2026-12-31')];
    await nextTick();
    for (const name of [
        'Title Supervisor One',
        'Title Supervisor Two',
        'Examiner One',
        'Examiner Two',
    ])
        expect(text(root)).toContain(name);
    Object.assign(props, empty());
    await nextTick();
    expect(text(root)).not.toContain('Title Supervisor One');
    expect(text(root)).not.toContain('Examiner Two');
    expect(text(root)).toContain('Pembimbing belum ditetapkan');
});

const all = (el) => [el, ...el.children.flatMap(all)];
const empty = () => ({
    dbJudul: null,
    dbSempro: null,
    dbSidang: null,
    dbJadwal: null,
    dbJadwalList: [],
    dbApprovedTitle: null,
});
const exam = (id, date, type = 'sempro') => ({
    id,
    mahasiswa_id: 1,
    tipe: type,
    tanggal: date,
    jam_mulai: '09:00:00',
    jam_selesai: '10:30:00',
    status: 'terpublikasi',
    gelombang: 2,
    catatan: 'Bawa presentasi',
    ruangan: { nama_ruangan: 'Ruang Aktual', gedung: 'Gedung A' },
    pembimbing1: { name: 'Supervisor One' },
    pembimbing2: { name: 'Supervisor Two' },
    penguji1: { name: 'Examiner One' },
    penguji2: { name: 'Examiner Two' },
});
function mount() {
    for (const name of ['StudentLecturers']) {
        const path = `resources/js/Components/${name}.vue`;
        if (Bun.file(path).size) imports[name] = compile(path);
    }
    const Dashboard = compile('resources/js/pages/Dashboard.vue');
    const props = reactive(empty());
    const root = node();
    renderer
        .createApp({ render: () => h(Dashboard, { ...props }) })
        .mount(root);
    return { props, root };
}
test('student dashboard omits schedule section and uses compact lecturer cards', async () => {
    const { props, root } = mount();
    props.dbJadwalList = [exam(1, '2026-12-31')];
    await nextTick();
    expect(text(root)).not.toContain('Jadwal Saya');
    expect(text(root)).not.toContain('2026-12-31');
    expect(text(root)).not.toContain('Jadwal terpublikasi terbaru');
    expect(text(root)).toContain('Examiner One');
    expect(
        all(root).some((el) => el.props.class?.includes('rounded-xl border')),
    ).toBe(true);
    props.dbJadwalList = [];
    await nextTick();
    expect(text(root)).not.toContain('Examiner One');
});
