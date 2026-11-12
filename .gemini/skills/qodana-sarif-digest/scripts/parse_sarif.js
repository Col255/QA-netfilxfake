#!/usr/bin/env node
/**
 * Script tóm tắt siêu tốc file Qodana SARIF JSON (>1MB) thành bảng lỗi White-Box
 * giúp tiết kiệm 99% token cho AI Agent.
 */
const fs = require('fs');
const path = require('path');

const sarifPath = process.argv[2];
if (!sarifPath || !fs.existsSync(sarifPath)) {
  console.error('Cách dùng: node parse_sarif.js <path_to_qodana.sarif.json>');
  process.exit(1);
}

const data = JSON.parse(fs.readFileSync(sarifPath, 'utf8'));
const run = (data.runs && data.runs[0]) || {};
const results = run.results || [];
const sanity = (run.properties && run.properties['qodana.sanity.results']) || [];

console.log(`# TÓM TẮT KẾT QUẢ QODANA SARIF (${path.basename(sarifPath)})`);
console.log(`- Tổng số cảnh báo chính (Results): **${results.length}**`);
console.log(`- Tổng số cảnh báo Sanity (PHP version/syntax): **${sanity.length}**\n`);

const actionable = [];
let connWarnings = 0;

for (const r of results) {
  const rule = r.ruleId || 'Unknown';
  const msg = (r.message && r.message.text) || '';
  const level = (r.level || 'note').toUpperCase();
  const phys = (r.locations && r.locations[0] && r.locations[0].physicalLocation) || {};
  const uri = (phys.artifactLocation && phys.artifactLocation.uri) || 'unknown';
  const line = (phys.region && phys.region.startLine) || '?';

  if (rule === 'PhpUndefinedVariableInspection' && msg.includes("'$conn'")) {
    connWarnings++;
    continue;
  }
  actionable.push({ level, rule, loc: `${uri}:${line}`, msg });
}

console.log(`- Cảnh báo biến \`$conn\` (do include \`db.php\`): **${connWarnings}** vị trí`);
console.log(`- Cảnh báo Logic / Control-Flow / Data-Flow trọng tâm: **${actionable.length}** vị trí\n`);
console.log('| Mức độ | Rule ID | Vị trí File:Dòng | Nội dung Cảnh báo (White-Box / Static) |');
console.log('| :---: | :--- | :--- | :--- |');
for (const item of actionable) {
  console.log(`| **${item.level}** | \`${item.rule}\` | \`${item.loc}\` | ${item.msg} |`);
}
