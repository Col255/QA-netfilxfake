#!/usr/bin/env node
/**
 * Script kiểm toán nhanh toàn bộ hệ thống Data-Driven CSV (Design & Report)
 * giúp AI Agent nắm bắt trạng thái 5 phân hệ trong 1 giây mà không tốn token đọc 12 file CSV.
 */
const fs = require('fs');
const path = require('path');

const MODULES = ['auth', 'mov', 'int', 'vip', 'adm', 'master'];

function parseCsvLine(line) {
  const result = [];
  let cur = '';
  let inQuotes = false;
  for (let i = 0; i < line.length; i++) {
    const ch = line[i];
    if (inQuotes) {
      if (ch === '"') {
        if (i + 1 < line.length && line[i + 1] === '"') {
          cur += '"';
          i++;
        } else {
          inQuotes = false;
        }
      } else {
        cur += ch;
      }
    } else {
      if (ch === '"') {
        inQuotes = true;
      } else if (ch === ',') {
        result.push(cur);
        cur = '';
      } else {
        cur += ch;
      }
    }
  }
  result.push(cur);
  return result;
}

function readCsv(filePath) {
  if (!fs.existsSync(filePath)) return [];
  const raw = fs.readFileSync(filePath, 'utf8').replace(/^\uFEFF/, '');
  const lines = raw.split(/\r?\n/).filter(l => l.trim().length > 0);
  if (lines.length < 2) return [];
  const headers = parseCsvLine(lines[0]).map(h => h.trim());
  return lines.slice(1).map(l => {
    const vals = parseCsvLine(l);
    const obj = {};
    headers.forEach((h, i) => {
      obj[h] = (vals[i] || '').trim();
    });
    return obj;
  });
}

const baseDir = path.resolve(__dirname, '..', '..', '..', '..');
const designDir = path.join(baseDir, 'docs', 'Design');
const reportDir = path.join(baseDir, 'docs', 'Report');

console.log('='.repeat(98));
console.log(`${'MODULE'.padEnd(8)} | ${'DESIGN TCs'.padEnd(10)} | ${'REPORT TCs'.padEnd(10)} | ${'PASSED'.padEnd(8)} | ${'TAGS'.padEnd(6)} | PHÂN BỐ NHÃN (V / X / B / SEC / API)`);
console.log('-'.repeat(98));

for (const m of MODULES) {
  const dRows = readCsv(path.join(designDir, `design_testcase_${m}.csv`));
  const rRows = readCsv(path.join(reportDir, `report_run_testcase_${m}.csv`));
  const tags = new Set();

  for (const r of dRows) {
    String(r.Tag_Bao_Phu || '')
      .split(/[;,]/)
      .map(s => s.trim())
      .filter(Boolean)
      .forEach(t => tags.add(t));
  }

  const passed = rRows.filter(r => String(r.Trang_Thai || '').toUpperCase() === 'PASS').length;
  const tagArr = Array.from(tags);
  const vCnt = tagArr.filter(t => /^(MOV_)?V\d+/i.test(t)).length;
  const xCnt = tagArr.filter(t => /^(MOV_)?X\d+/i.test(t)).length;
  const bCnt = tagArr.filter(t => /^B\d+/i.test(t)).length;
  const sCnt = tagArr.filter(t => /^(MOV_)?SEC_/i.test(t)).length;
  const aCnt = tagArr.filter(t => /^API_/i.test(t)).length;

  const dist = `V:${vCnt} | X:${xCnt} | B:${bCnt} | SEC:${sCnt} | API:${aCnt}`;
  console.log(`${m.toUpperCase().padEnd(8)} | ${String(dRows.length).padEnd(10)} | ${String(rRows.length).padEnd(10)} | ${String(passed).padEnd(8)} | ${String(tags.size).padEnd(6)} | ${dist}`);
}
console.log('='.repeat(98));
