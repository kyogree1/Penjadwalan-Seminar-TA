import { expect, test } from 'bun:test';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { compileScript, parse } from '@vue/compiler-sfc';
import * as Vue from 'vue';

globalThis.Document = class {};
globalThis.ShadowRoot = class {};

const mount = (name, props = {}) => {
    const source = readFileSync(`resources/js/pages/Auth/${name}.vue`, 'utf8');
    const compiled = compileScript(parse(source).descriptor, {
        id: name,
        inlineTemplate: true,
    });
    const calls = [];
    let form;
    const wrapper = Vue.defineComponent({
        setup:
            (_, { slots }) =>
            () =>
                Vue.h('div', slots.default?.()),
    });
    const imports = {
        ...Vue,
        Head: wrapper,
        Link: wrapper,
        usePage: () => ({ props: { flash: { success: 'Check your inbox' } } }),
        useForm: (values) =>
            (form = Vue.reactive({
                ...values,
                processing: false,
                errors: {},
                post: (...args) => calls.push(args),
                reset: (...keys) =>
                    keys.forEach((key) => {
                        form[key] = '';
                    }),
            })),
    };
    const code = new Bun.Transpiler({ loader: 'ts' })
        .transformSync(compiled.content)
        .replace(
            /import\s+\{([^}]+)\}\s+from\s+["'][^"']+["'];?/g,
            (_, names) => `const {${names.replace(/\bas\b/g, ':')}} = imports;`,
        )
        .replace('export default', 'return');
    const component = runInNewContext(`(function () { ${code} })()`, {
        imports,
    });
    const node = (text = '') => ({
        text,
        children: [],
        parent: null,
        addEventListener() {},
        removeEventListener() {},
        getRootNode: () => ({ activeElement: null }),
    });
    const renderer = Vue.createRenderer({
        createElement: (tag) =>
            Object.assign(node(), { tag, tagName: tag.toUpperCase() }),
        createText: node,
        createComment: node,
        setText: (el, value) => {
            el.text = value;
        },
        setElementText: (el, value) => {
            el.text = value;
            el.children = [];
        },
        patchProp: (el, key, _previous, value) => {
            el[key] = value;
        },
        insert: (el, parent) => {
            el.parent = parent;
            parent.children.push(el);
        },
        remove: () => {},
        parentNode: (el) => el.parent,
        nextSibling: () => null,
    });
    const root = node();
    renderer.createApp(component, props).mount(root);
    const walk = (el) => [el, ...el.children.flatMap(walk)];
    return { root, walk, form, calls };
};

test('actual forgot-password form posts email and blocks duplicate submits', async () => {
    const { root, walk, form, calls } = mount('ForgotPassword');
    form.email = 'owner@example.test';
    walk(root)
        .find((el) => el.tag === 'form')
        .onSubmit({ preventDefault() {} });
    expect(calls[0][0]).toBe('/forgot-password');
    expect(form.email).toBe('owner@example.test');
    form.processing = true;
    await Vue.nextTick();
    expect(walk(root).find((el) => el.tag === 'button').disabled).toBe(true);
});

test('actual reset form submits token, confirmation and clears secrets on finish', async () => {
    const { root, walk, form, calls } = mount('ResetPassword', {
        email: 'owner@example.test',
        token: 'mail-token',
    });
    form.password = 'Example!Password902';
    form.password_confirmation = form.password;
    walk(root)
        .find((el) => el.tag === 'form')
        .onSubmit({ preventDefault() {} });
    expect(calls[0][0]).toBe('/reset-password');
    expect(form.token).toBe('mail-token');
    expect(form.email).toBe('owner@example.test');
    calls[0][1].onFinish();
    expect(form.password).toBe('');
    expect(form.password_confirmation).toBe('');
    form.errors.email = 'Expired link';
    await Vue.nextTick();
    expect(walk(root).some((el) => el.text.includes('Expired link'))).toBe(
        true,
    );
});

test('login offers the real guest first-access route', () => {
    const source = readFileSync('resources/js/pages/Auth/Login.vue', 'utf8');
    expect(source).toContain('href="/forgot-password"');
});
