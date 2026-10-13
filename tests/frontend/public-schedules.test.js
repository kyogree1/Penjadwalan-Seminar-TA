import { expect, test } from 'bun:test';
import { existsSync, readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import * as Vue from 'vue';

const path = 'resources/js/Components/PublicSchedules.vue';
const node = (text = '') => ({ text, children: [], parent: null });
const renderer = Vue.createRenderer({
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

test('actual public schedule SFC renders both exam types and reacts to empty server props', async () => {
    expect(existsSync(path)).toBe(true);
    const { descriptor } = parse(readFileSync(path, 'utf8'), {
        filename: path,
    });
    const compiled = compileScript(descriptor, {
        id: 'public-schedules-test',
        inlineTemplate: true,
        templateOptions: { compilerOptions: { hoistStatic: false } },
        fs: {
            fileExists: existsSync,
            readFile: (file) => readFileSync(file, 'utf8'),
        },
    });
    const code = new Bun.Transpiler({ loader: 'ts' })
        .transformSync(compiled.content)
        .replace(
            /import\s+\{([^}]+)\}\s+from\s+["'][^"']+["'];?/g,
            (_, names) => `const {${names.replace(/\bas\b/g, ':')}} = imports;`,
        )
        .replace('export default', 'return');
    const Component = runInNewContext(`(function () { ${code} })()`, {
        imports: Vue,
    });
    const schedule = {
        student_name: 'Actual Student',
        title: '<script>private()</script>',
        type: 'sempro',
        date: '2026-11-01',
        start_time: '09:00:00',
        end_time: '10:30:00',
        room: 'Room A',
        mode: 'hybrid',
        supervisors: ['Actual Supervisor'],
        examiners: ['Actual Examiner'],
    };
    const props = Vue.reactive({
        schedules: [
            schedule,
            {
                ...schedule,
                type: 'sidang',
                room: null,
                mode: 'online',
                title: null,
                supervisors: [],
                examiners: [],
            },
        ],
    });
    const root = node();
    const app = renderer.createApp({
        setup: () => () => Vue.h(Component, props),
    });
    app.mount(root);
    expect(text(root)).toContain('Seminar Proposal');
    expect(text(root)).toContain('Sidang');
    expect(text(root)).toContain('Actual Student');
    expect(text(root)).toContain('2026-11-01');
    expect(text(root)).toContain('09:00');
    expect(text(root)).toContain('10:30');
    expect(text(root)).toContain('Room A');
    expect(text(root)).toContain('Hybrid');
    expect(text(root)).toContain('Online');
    expect(text(root)).toContain('Actual Supervisor');
    expect(text(root)).toContain('Actual Examiner');
    expect(text(root)).toContain('<script>private()</script>');
    props.schedules = [];
    await Vue.nextTick();
    expect(text(root)).toContain('Belum ada jadwal terpublikasi');
    expect(text(root)).not.toContain('Actual Student');
    expect(text(root)).not.toContain('Masuk');
    app.unmount();
});
