// Run: bun tests/penjadwalan-logic.check.js
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import ts from 'typescript';
import { computed, ref } from 'vue';

const source = fs.readFileSync(
    'resources/js/pages/Koordinator/Penjadwalan.vue',
    'utf8',
);
const script = source
    .match(/<script setup lang="ts">([\s\S]*?)<\/script>/)[1]
    .replace(/import[\s\S]*?from\s+['"][^'"]+['"];\s*/g, '');
const checks = `
const originalCount = periods.value.length;
const valid = { type: 'Sempro', academicYear: '2027/2028', semester: 'Genap', wave: 1, quota: 10, startDate: '2027-01-01', endDate: '2027-01-31' };
assert.equal(validatePeriodInput(valid, periods.value), '');
for (const invalid of [{academicYear:'2027/2029'}, {wave:0}, {wave:1.5}, {quota:0}, {quota:1.5}, {startDate:'2027-02-30'}, {endDate:'2026-12-31'}]) {
    assert.notEqual(validatePeriodInput({...valid,...invalid}, periods.value), '');
}
openCreatePeriod();
periodDraft.value = {...valid};
savePeriod();
assert.equal(periods.value.length, originalCount + 1);
const created = periods.value.at(-1);
assert.equal(created.isOpen, false);
togglePeriod(created); assert.equal(created.isOpen,true);
togglePeriod(created); assert.equal(created.isOpen,false);
openCreatePeriod(); periodDraft.value = {...valid}; savePeriod();
assert.ok(periodError.value); assert.equal(periods.value.length, originalCount+1);
openEditPeriod(created); periodDraft.value.quota=15; savePeriod();
assert.equal(periodError.value,''); assert.equal(periods.value.at(-1).quota,15);
openEditPeriod(periods.value.at(-1)); periodDraft.value={...periods.value[0]}; savePeriod();
assert.ok(periodError.value);
viewResults(periods.value[1]); assert.equal(filteredSessions.value.length,3);
viewResults(periods.value[3]); assert.equal(filteredSessions.value.length,2);
viewResults(created); assert.equal(filteredSessions.value.length,0);
const session = scheduleSessions.value[0];
const before = JSON.parse(JSON.stringify(session));
openExaminerOverride(session);
examinerDraft.value=['','']; saveExaminerOverride(); assert.ok(examinerError.value);
examinerDraft.value=[session.examiners[0],session.examiners[0]]; saveExaminerOverride(); assert.ok(examinerError.value);
examinerDraft.value=[session.supervisors[0],session.examiners[0]]; saveExaminerOverride(); assert.ok(examinerError.value);
assert.deepEqual(JSON.parse(JSON.stringify(session)),before);
examinerDraft.value=['Dr. Muhammad Rizal, M.Kom.','Dewi Ratnasari, S.T., M.T.'];
saveExaminerOverride(); assert.equal(examinerError.value,'');
assert.deepEqual({...JSON.parse(JSON.stringify(session)), examiners:before.examiners},before);
assert.notDeepEqual(session.examiners,before.examiners);
`;
const compiled = ts.transpile(script + checks, {
    target: ts.ScriptTarget.ES2022,
    module: ts.ModuleKind.None,
});
vm.runInNewContext(compiled, { ref, computed, assert });
assert.equal(
    /GaMetricCard|Algoritma Genetika|bestFitness|runOptimization/.test(source),
    false,
);
console.log(
    'PASS: actual SFC create/edit/duplicate/date/year/quota validation, open/close, period filtering, examiner override and schedule retention.',
);
