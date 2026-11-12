#!/usr/bin/env node
/**
 * Script phân tích tự động Control Flow Graph (CFG) & Cyclomatic Complexity V(G)
 * cho các file PHP trong dự án Netfix2 (tiết kiệm 90% token cho AI Agent).
 */
const fs = require('fs');
const path = require('path');

const MODULES = {
  AUTH: ['netfix2/register.php', 'netfix2/login.php', 'netfix2/admin_login.php', 'netfix2/profile.php', 'netfix2/edit_profile.php', 'netfix2/change_password.php'],
  MOV: ['netfix2/index.php', 'netfix2/movies.php', 'netfix2/search.php', 'netfix2/watch.php'],
  INT: ['netfix2/api/view_counter.php', 'netfix2/comments.php', 'netfix2/watch_history.php'],
  VIP: ['netfix2/pricing.php', 'netfix2/checkout.php', 'netfix2/process_payment.php', 'netfix2/payment_return.php'],
  ADM: ['netfix2/add_movie.php', 'netfix2/manage_movies.php', 'netfix2/orders.php', 'netfix2/customers.php', 'netfix2/statistics.php']
};

const DECISION_PATTERN = /\b(if|elseif|while|for|foreach|case|catch)\b\s*(\([^{;]*\))?/i;

function extractPhpBlocks(content) {
  const lines = content.split(/\r?\n/);
  let inPhp = false;
  const phpLines = [];
  lines.forEach((line, idx) => {
    if (line.includes('<?php') || line.includes('<?=')) {
      inPhp = true;
    }
    if (inPhp) {
      const stripped = line.trim();
      if (!(stripped.startsWith('<?php echo') && stripped.endsWith('?>'))) {
        phpLines.push({ lineNo: idx + 1, text: stripped });
      }
    }
    if (line.includes('?>')) {
      inPhp = false;
    }
  });
  return phpLines;
}

function analyzePhpFile(filepath) {
  if (!fs.existsSync(filepath)) return null;
  const content = fs.readFileSync(filepath, 'utf8');
  const phpLines = extractPhpBlocks(content);
  const decisions = [];
  let compoundOps = 0;

  for (const { lineNo, text } of phpLines) {
    if (text.startsWith('//') || text.startsWith('#') || text.startsWith('/*') || text.startsWith('*')) {
      continue;
    }
    const codeNoStr = text.replace(/(["'])(?:(?=(\\?))\2.)*?\1/g, '""');
    const m = codeNoStr.match(DECISION_PATTERN);
    if (m) {
      const kw = m[1].toLowerCase();
      const matches = codeNoStr.match(/(&&|\|\||\band\b|\bor\b)/gi);
      const andOr = matches ? matches.length : 0;
      compoundOps += andOr;
      let cleanExpr = text.replace(/<\?php/g, '').replace(/\?>/g, '').trim();
      if (cleanExpr.length > 95) cleanExpr = cleanExpr.slice(0, 92) + '...';
      decisions.push({
        nodeId: `D${decisions.length + 1}`,
        line: lineNo,
        keyword: kw,
        compound: andOr,
        code: cleanExpr
      });
    }
  }

  return {
    file: filepath,
    decisions,
    predicateCount: decisions.length,
    compoundOps,
    vgBranch: decisions.length + 1,
    vgMcdc: decisions.length + compoundOps + 1
  };
}

function printSummary() {
  console.log('='.repeat(88));
  console.log(`${'PHÂN HỆ'.padEnd(8)} | ${'FILE PHP'.padEnd(28)} | ${'SỐ NHÁNH (P)'.padEnd(12)} | ${'V(G) NHÁNH (C1)'.padEnd(15)} | V(G) MC/DC (C2)`);
  console.log('-'.repeat(88));
  for (const [mod, files] of Object.entries(MODULES)) {
    for (const fp of files) {
      const info = analyzePhpFile(fp);
      if (info) {
        console.log(`${mod.padEnd(8)} | ${fp.padEnd(28)} | ${String(info.predicateCount).padEnd(12)} | ${String(info.vgBranch).padEnd(15)} | ${info.vgMcdc}`);
      }
    }
  }
  console.log('='.repeat(88));
}

function printFileDetail(filepath) {
  const info = analyzePhpFile(filepath);
  if (!info) {
    console.error(`Không tìm thấy file: ${filepath}`);
    process.exit(1);
  }
  console.log(`# PHÂN TÍCH WHITE-BOX CFG: ${info.file}`);
  console.log(`- Tổng số nút điều kiện rẽ nhánh (P): ${info.predicateCount}`);
  console.log(`- Số toán tử logic kết hợp (&&, ||): ${info.compoundOps}`);
  console.log(`- Độ phức tạp Cyclomatic theo Nhánh V(G) = P + 1 = ${info.vgBranch} (Cần tối thiểu ${info.vgBranch} Basis Paths cho độ phủ C1)`);
  console.log(`- Độ phức tạp Cyclomatic theo Điều kiện con V(G)_MCDC = ${info.vgMcdc}\n`);

  console.log('## 1. Bảng các Nút Điều kiện (Decision Nodes)');
  console.log('| Node | Dòng | Loại | Số ĐK phụ (&&/||) | Biểu thức Điều kiện trong Code |');
  console.log('| :---: | :---: | :---: | :---: | :--- |');
  for (const d of info.decisions) {
    const safeCode = d.code.replace(/\|/g, '\\|');
    console.log(`| **${d.nodeId}** | L${d.line} | \`${d.keyword}\` | ${d.compound} | \`${safeCode}\` |`);
  }

  console.log('\n## 2. Sơ đồ Mermaid CFG (Tóm tắt các nút quyết định)');
  console.log('```mermaid');
  console.log('flowchart TD');
  console.log('    START(["Bắt đầu (Entry)"])');
  let prev = 'START';
  for (const d of info.decisions.slice(0, 15)) {
    let lbl = d.code.replace(/"/g, "'").replace(/\{/g, '').trim();
    if (lbl.length > 45) lbl = lbl.slice(0, 42) + '...';
    console.log(`    ${d.nodeId}{"${d.nodeId} (L${d.line}): ${lbl}"}`);
    console.log(`    ${prev} --> ${d.nodeId}`);
    prev = d.nodeId;
  }
  console.log(`    ${prev} --> END(["Kết thúc (Exit)"])`);
  console.log('```');
}

const arg = process.argv[2];
if (!arg || arg === '--summary') {
  printSummary();
} else {
  printFileDetail(arg);
}
