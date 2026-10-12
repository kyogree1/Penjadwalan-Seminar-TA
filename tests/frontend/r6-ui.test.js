import { expect, test } from 'bun:test';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import * as Vue from 'vue';
import * as Icons from 'lucide-vue-next';
import { dialog } from '../../resources/js/lib/dialog';

const node = (text = '') => ({
    text,
    children: [],
    parent: null,
    props: {},
    tagName: 'INPUT',
    addEventListener() {},
    removeEventListener() {},
    getRootNode: () => ({ activeElement: null }),
    setAttribute() {},
    removeAttribute() {},
});
const renderer = Vue.createRenderer({
    createElement: (tag) => ({ ...node(), tag }),
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
        el.props[key] = value;
    },
    insert: (el, parent) => {
        el.parent = parent;
        parent.children.push(el);
    },
    remove: (el) => {
        el.parent.children.splice(el.parent.children.indexOf(el), 1);
    },
    parentNode: (el) => el.parent,
    nextSibling: () => null,
});
const text = (el) => [el.text, ...el.children.map(text)].join(' ');
const walk = (el) => [el, ...el.children.flatMap(walk)];
const wrapper = Vue.defineComponent({
    setup:
        (_, { slots }) =>
        () =>
            Vue.h('div', slots.default?.()),
});
function mount(path, additions = {}) {
    const { descriptor } = parse(readFileSync(path, 'utf8'), {
        filename: path,
    });
    const compiled = compileScript(descriptor, {
        id: 'r6',
        inlineTemplate: true,
    });
    const imports = {
        ...Vue,
        ...Icons,
        Head: wrapper,
        Link: wrapper,
        PageHeaderBox: wrapper,
        useTheme: () => ({
            isDarkMode: Vue.ref(false),
            toggleDarkMode() {},
            initializeTheme() {},
        }),
        ...additions,
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
            (_, name) => `const ${name} = imports.${name};`,
        )
        .replace(/import\.meta\.env\.DEV/g, 'false')
        .replace('export default', 'return');
    const component = runInNewContext(`(function () { ${code} })()`, {
        imports,
    });
    const root = node();
    renderer.createApp(component).mount(root);
    return root;
}

test('actual monitoring SFC exposes unavailable data instead of fixtures and inactive export', () => {
    const root = mount('resources/js/pages/Koordinator/Monitoring.vue');
    expect(text(root)).toContain('Monitoring terpadu belum tersedia');
    expect(text(root)).toContain('Ekspor laporan belum tersedia');
    expect(text(root)).not.toContain('Akmal');
    expect(text(root)).not.toContain('124');
    expect(walk(root).filter((el) => el.tag === 'button')).toHaveLength(0);
});

test('actual login SFC renders honest schedule state, labelled credentials and pending/error states', async () => {
    const form = Vue.reactive({
        email: '',
        password: '',
        remember: false,
        demo_role: '',
        processing: false,
        errors: {},
        post() {},
        reset() {},
    });
    const root = mount('resources/js/pages/Auth/Login.vue', {
        useForm: () => form,
    });
    expect(text(root)).toContain('Jadwal publik belum tersedia');
    expect(text(root)).not.toContain('Realtime Sync');
    expect(text(root)).not.toContain('Tejo');
    expect(walk(root).some((el) => el.props.for === 'login-identifier')).toBe(
        true,
    );
    expect(walk(root).some((el) => el.props.for === 'login-password')).toBe(
        true,
    );
    form.processing = true;
    form.errors.email = 'Kredensial ditolak';
    await Vue.nextTick();
    expect(text(root)).toContain('Memproses...');
    expect(text(root)).toContain('Kredensial ditolak');
    expect(
        walk(root).find((el) => el.props.type === 'submit').props.disabled,
    ).toBe(true);
    expect(walk(root).some((el) => el.props.role === 'alert')).toBe(true);
});

test('shared dialog traps both tab directions, recovers escaped focus and restores opener/overflow', async () => {
    const listeners = {};
    const oldDocument = globalThis.document;
    const documentMock = {
        activeElement: null,
        body: { style: { overflow: 'auto' } },
        addEventListener: (key, fn) => {
            listeners[key] = fn;
        },
        removeEventListener: (key) => {
            delete listeners[key];
        },
    };
    const button = () => ({
        isConnected: true,
        focus() {
            documentMock.activeElement = this;
        },
        closest: () => null,
        getClientRects: () => [1],
    });
    const opener = button(),
        first = button(),
        last = button();
    documentMock.activeElement = opener;
    const el = {
        attrs: {},
        setAttribute(key, value) {
            this.attrs[key] = value;
        },
        querySelectorAll: () => [first, last],
        contains: (item) => [first, last, el].includes(item),
        focus() {
            documentMock.activeElement = this;
        },
    };
    globalThis.document = documentMock;
    try {
        dialog.mounted(el, { value: 'Persetujuan Akademik' });
        await Promise.resolve();
        expect(documentMock.activeElement).toBe(first);
        expect(el.attrs.role).toBe('dialog');
        expect(el.attrs['aria-label']).toBe('Persetujuan Akademik');
        expect(documentMock.body.style.overflow).toBe('hidden');
        let prevented = false;
        listeners.keydown({
            key: 'Tab',
            shiftKey: true,
            preventDefault() {
                prevented = true;
            },
        });
        expect(prevented).toBe(true);
        expect(documentMock.activeElement).toBe(last);
        listeners.keydown({ key: 'Tab', shiftKey: false, preventDefault() {} });
        expect(documentMock.activeElement).toBe(first);
        listeners.focusin({ target: opener });
        expect(documentMock.activeElement).toBe(first);
        dialog.unmounted(el);
        expect(documentMock.activeElement).toBe(opener);
        expect(documentMock.body.style.overflow).toBe('auto');
        expect(Object.keys(listeners)).toHaveLength(0);
    } finally {
        globalThis.document = oldDocument;
    }
});

test('catalogue surfaces use the shared semantic navy token', () => {
    const source = readFileSync('resources/js/pages/Katalog/Index.vue', 'utf8');
    expect(source).not.toMatch(/dark:bg-\[#0e1626\]/i);
    expect(source.match(/dark:bg-navy-surface/g)).toHaveLength(5);
});

test('shell help has no simulated AI, invented deadline or notification activity', () => {
    const source = readFileSync(
        'resources/js/Components/AcademicChatbot.vue',
        'utf8',
    );
    expect(source).not.toContain('setTimeout');
    expect(source).not.toContain('Akmal');
    expect(source).not.toContain('30 Oktober');
    expect(source).toContain('/panduan');
});
