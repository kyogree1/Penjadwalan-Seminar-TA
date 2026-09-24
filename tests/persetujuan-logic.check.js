// Run: bun tests/persetujuan-logic.check.js
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import ts from 'typescript';
import { computed, ref } from 'vue';

const source = fs.readFileSync(
    'resources/js/pages/Kaprodi/Persetujuan.vue',
    'utf8',
);
const script = source
    .match(/<script setup lang="ts">([\s\S]*?)<\/script>/)[1]
    .replace(/import[\s\S]*?from\s+['"][^'"]+['"];\s*/g, '');
const checks = `
activeSection.value = 'sidang';
assert.equal(filteredAcademicSubmissions.value.length, 3);
academicSearch.value = 'siti';
assert.equal(filteredAcademicSubmissions.value.length, 1);
academicAngkatan.value = '2022';
assert.equal(filteredAcademicSubmissions.value.length, 0);
academicSearch.value = ''; academicAngkatan.value = 'Semua';
openAcademicDetail(sidangSubmissions.value[1]);
updateAcademicStatus('Disetujui Akademik');
assert.equal(sidangSubmissions.value[1].status, 'Perlu Revisi');
openAcademicDetail(sidangSubmissions.value[0]);
academicNote.value = '  '; updateAcademicStatus('Perlu Revisi');
assert.equal(sidangSubmissions.value[0].status, 'Menunggu Validasi');
academicNote.value = ' Lengkapi catatan '; updateAcademicStatus('Perlu Revisi');
assert.equal(showAcademicModal.value, false);
openAcademicDetail(sidangSubmissions.value[0]);
assert.equal(academicNote.value, 'Lengkapi catatan');
updateAcademicStatus('Disetujui Akademik');
assert.equal(sidangSubmissions.value[0].status, 'Disetujui Akademik');
activeSection.value = 'sempro';
openAcademicDetail(semproSubmissions.value[0]);
assert.equal(academicNote.value, '');
assert.equal(academicDecision.value, 'Sempro');
assert.equal(filteredAcademicSubmissions.value.length, 3);
`;
const compiled = ts.transpile(script + checks, {
    target: ts.ScriptTarget.ES2022,
    module: ts.ModuleKind.None,
});
vm.runInNewContext(compiled, { ref, computed, assert });
console.log(
    'PASS: approval filtering, eligibility guard, revision notes, reopen and tab isolation.',
);
