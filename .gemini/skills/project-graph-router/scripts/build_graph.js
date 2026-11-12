#!/usr/bin/env node
/**
 * PROJECT KNOWLEDGE GRAPH COMPRESSOR & SKILL ROUTER ENGINE
 * Nén toàn bộ thư mục dự án thành đồ thị JSON (.gemini/skills/project-graph-router/project-graph.json)
 * chứa sẵn: Tọa độ dòng PHP Logic (StartLine-EndLine), Biến POST/GET/SESSION, Bảng SQL,
 * Hàm định nghĩa, Luồng Redirect/Include (Edges), Độ phức tạp V(G) và Ánh xạ Test Case.
 */
const fs = require('fs');
const path = require('path');

const ROOT_DIR = path.resolve(__dirname, '..', '..', '..', '..');
const GRAPH_PATH = path.join(__dirname, 'project-graph.json');

const MODULE_MAP = {
  'register.php': 'AUTH',
  'login.php': 'AUTH',
  'admin_login.php': 'AUTH',
  'logout.php': 'AUTH',
  'profile.php': 'AUTH',
  'edit_profile.php': 'AUTH',
  'change_password.php': 'AUTH',
  'index.php': 'MOV',
  'movies.php': 'MOV',
  'search.php': 'MOV',
  'watch.php': 'MOV/INT',
  'view_counter.php': 'INT',
  'comments.php': 'INT',
  'watch_history.php': 'INT',
  'pricing.php': 'VIP',
  'checkout.php': 'VIP',
  'process_payment.php': 'VIP',
  'payment_return.php': 'VIP',
  'order_history.php': 'VIP',
  'manage_vip.php': 'VIP/ADM',
  'add_movie.php': 'ADM',
  'edit_movie.php': 'ADM',
  'manage_movies.php': 'ADM',
  'orders.php': 'ADM/VIP',
  'customers.php': 'ADM',
  'statistics.php': 'ADM',
  'admin_dashboard.php': 'ADM',
  'db.php': 'CORE',
  'header.php': 'CORE',
  'footer.php': 'CORE',
  'admin_sidebar.php': 'CORE',
  'user_sidebar.php': 'CORE'
};

const SKILL_ROUTER_RULES = [
  {
    intent: 'White-Box Testing / CFG / Cyclomatic Complexity V(G) / Basis Paths / Nhánh if-else',
    recommendedSkill: 'php-whitebox-cfg',
    command: 'node ".gemini/skills/php-whitebox-cfg/scripts/analyze_cfg.js" <file_or_--summary>'
  },
  {
    intent: 'Phân tích lỗi tĩnh Qodana / Đọc file qodana.sarif.json',
    recommendedSkill: 'qodana-sarif-digest',
    command: 'node ".gemini/skills/qodana-sarif-digest/scripts/parse_sarif.js" <sarif_path>'
  },
  {
    intent: 'Kiểm tra Test Case CSV / Độ phủ (V, X, B, SEC, API) / Báo cáo Pass-Fail',
    recommendedSkill: 'qa-data-driven-sync',
    command: 'node ".gemini/skills/qa-data-driven-sync/scripts/audit_coverage.js"'
  },
  {
    intent: 'Tra cứu nhanh file, dòng code cần đọc, biến POST/GET, bảng SQL, liên kết trang',
    recommendedSkill: 'project-graph-router',
    command: 'node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query <keyword>'
  }
];

function uniq(arr) {
  return Array.from(new Set(arr));
}

function scanPhpFile(relPath) {
  const fullPath = path.join(ROOT_DIR, relPath);
  const content = fs.readFileSync(fullPath, 'utf8');
  const lines = content.split(/\r?\n/);

  // Tìm khoảng dòng chứa PHP Backend Logic chính (trước thẻ <!DOCTYPE html> hoặc <html hoặc <style>)
  let mainPhpEndLine = lines.length;
  for (let i = 0; i < lines.length; i++) {
    if (/^(<!DOCTYPE|<html|<head|<title|<style|<div)/i.test(lines[i].trim()) && i > 5) {
      mainPhpEndLine = i;
      break;
    }
  }

  // Trích xuất hàm PHP kèm dòng bắt đầu - kết thúc
  const functions = [];
  lines.forEach((line, idx) => {
    const m = line.match(/function\s+([a-zA-Z0-9_]+)\s*\(([^)]*)\)/);
    if (m) {
      functions.push({ name: m[1], params: m[2].trim(), line: idx + 1 });
    }
  });

  // Trích xuất biến $_POST, $_GET, $_SESSION
  const postParams = uniq([...content.matchAll(/\$_POST\[['"]([^'"]+)['"]\]/g)].map(m => m[1]));
  const getParams = uniq([...content.matchAll(/\$_GET\[['"]([^'"]+)['"]\]/g)].map(m => m[1]));
  const sessionKeys = uniq([...content.matchAll(/\$_SESSION\[['"]([^'"]+)['"]\]/g)].map(m => m[1]));

  // Trích xuất cạnh đồ thị (Edges): includes, redirects, SQL tables, fetch/action links
  const includes = uniq([...content.matchAll(/\b(?:include|require|include_once|require_once)\s*\(?['"]([^'"]+)['"]\)?/g)].map(m => m[1]));
  const redirects = uniq([...content.matchAll(/Location:\s*([^\s"'$;]+)/gi)].map(m => m[1]));
  const formActions = uniq([...content.matchAll(/<form[^>]*action=['"]([^'"]+)['"]/gi)].map(m => m[1]));
  const sqlTables = uniq(
    [...content.matchAll(/\b(?:FROM|INTO|UPDATE|JOIN)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/gi)]
      .map(m => m[1].toLowerCase())
      .filter(t => ['users', 'movies', 'episodes', 'comments', 'vip_orders', 'watch_history', 'packages'].includes(t))
  );

  // Đếm nhanh số nút điều kiện trong khối PHP logic
  let decisions = 0;
  let inPhp = false;
  for (let i = 0; i < lines.length; i++) {
    const l = lines[i];
    if (l.includes('<?php') || l.includes('<?=')) inPhp = true;
    if (inPhp) {
      const stripped = l.trim();
      if (!stripped.startsWith('//') && !stripped.startsWith('*') && !(stripped.startsWith('<?php echo') && stripped.endsWith('?>'))) {
        const clean = stripped.replace(/(["'])(?:(?=(\\?))\2.)*?\1/g, '""');
        if (/\b(if|elseif|while|for|foreach|case|catch)\b/i.test(clean)) {
          decisions++;
        }
      }
    }
    if (l.includes('?>')) inPhp = false;
  }

  const baseName = path.basename(relPath);
  return {
    path: relPath.replace(/\\/g, '/'),
    module: MODULE_MAP[baseName] || 'OTHER',
    totalLines: lines.length,
    readHint: {
      phpBackendLines: [1, mainPhpEndLine],
      htmlTemplateLines: mainPhpEndLine < lines.length ? [mainPhpEndLine + 1, lines.length] : null,
      advice: `Chỉ cần đọc dòng 1-${mainPhpEndLine} bằng read_file để lấy toàn bộ logic PHP (tiết kiệm ${Math.max(0, lines.length - mainPhpEndLine)} dòng HTML/CSS).`
    },
    whitebox: {
      decisionCount: decisions,
      vgBranch: decisions + 1
    },
    inputs: {
      POST: postParams,
      GET: getParams,
      SESSION: sessionKeys
    },
    functions,
    edges: {
      includes,
      redirectsTo: redirects,
      formSubmitsTo: formActions,
      sqlTables
    }
  };
}

function buildGraph() {
  const phpFiles = [];
  const netfixDir = path.join(ROOT_DIR, 'netfix2');

  for (const entry of fs.readdirSync(netfixDir, { withFileTypes: true })) {
    if (entry.isFile() && entry.name.endsWith('.php')) {
      phpFiles.push(path.join('netfix2', entry.name));
    } else if (entry.isDirectory()) {
      const subDir = path.join(netfixDir, entry.name);
      for (const sub of fs.readdirSync(subDir)) {
        if (sub.endsWith('.php')) {
          phpFiles.push(path.join('netfix2', entry.name, sub));
        }
      }
    }
  }

  const nodes = {};
  for (const f of phpFiles) {
    const info = scanPhpFile(f);
    nodes[info.path] = info;
  }

  const testSuites = {
    AUTH: { csvDesign: 'docs/Design/design_testcase_auth.csv', csvReport: 'docs/Report/report_run_testcase_auth.csv', codecept: ['tests/login_test.js'], runner: 'docs/testrun/test_runner_auth.py' },
    MOV: { csvDesign: 'docs/Design/design_testcase_mov.csv', csvReport: 'docs/Report/report_run_testcase_mov.csv', codecept: ['tests/movies_search_test.js', 'tests/search_security_test.js', 'tests/streaming_vip_test.js'], runner: 'docs/testrun/test_runner_mov.py' },
    INT: { csvDesign: 'docs/Design/design_testcase_int.csv', csvReport: 'docs/Report/report_run_testcase_int.csv', codecept: ['tests/Interactions & Engagement.js', 'tests/view_counter_test.js'], runner: 'docs/testrun/test_runner_int.py' },
    VIP: { csvDesign: 'docs/Design/design_testcase_vip.csv', csvReport: 'docs/Report/report_run_testcase_vip.csv', codecept: ['tests/vip_test.js'], runner: 'docs/testrun/test_runner_vip.py' },
    ADM: { csvDesign: 'docs/Design/design_testcase_adm.csv', csvReport: 'docs/Report/report_run_testcase_adm.csv', codecept: ['tests/admin_management_test.js', 'tests/admin_movies_rbac_test.js'], runner: 'docs/testrun/test_runner_adm.py' },
    MASTER: { csvDesign: 'docs/Design/design_testcase_master.csv', csvReport: 'docs/Report/report_run_testcase_master.csv', codecept: ['tests/master_all_modules_test.js'], runner: 'docs/testrun/test_runner_master.py' }
  };

  const graph = {
    generatedAt: new Date().toISOString(),
    project: 'QA Netflix Fake (Netfix2)',
    skillRouter: SKILL_ROUTER_RULES,
    testSuites,
    nodes
  };

  fs.writeFileSync(GRAPH_PATH, JSON.stringify(graph, null, 2), 'utf8');
  return graph;
}

function loadOrBuildGraph() {
  if (!fs.existsSync(GRAPH_PATH)) {
    return buildGraph();
  }
  return JSON.parse(fs.readFileSync(GRAPH_PATH, 'utf8'));
}

function printOverview(graph) {
  console.log('='.repeat(95));
  console.log('BỘ ĐỊNH TUYẾN SKILL (SKILL ROUTER) - KIỂM TRA TRƯỚC KHI DÙNG SKILL:');
  console.log('-'.repeat(95));
  for (const r of graph.skillRouter) {
    console.log(`• [${r.recommendedSkill.padEnd(20)}] -> ${r.intent}`);
    console.log(`  Lệnh: ${r.command}`);
  }
  console.log('='.repeat(95));
  console.log(`${'FILE NODE'.padEnd(28)} | ${'MODULE'.padEnd(8)} | ${'DÒNG LOGIC PHP'.padEnd(16)} | ${'V(G)'.padEnd(5)} | BẢNG SQL & INPUT CHÍNH`);
  console.log('-'.repeat(95));
  for (const n of Object.values(graph.nodes)) {
    const logicRange = `L${n.readHint.phpBackendLines[0]}-L${n.readHint.phpBackendLines[1]}/${n.totalLines}`;
    const sqlStr = n.edges.sqlTables.length ? `SQL:[${n.edges.sqlTables.join(',')}] ` : '';
    const postStr = n.inputs.POST.length ? `POST:[${n.inputs.POST.slice(0, 4).join(',')}] ` : '';
    const getStr = n.inputs.GET.length ? `GET:[${n.inputs.GET.slice(0, 4).join(',')}]` : '';
    console.log(`${n.path.padEnd(28)} | ${n.module.padEnd(8)} | ${logicRange.padEnd(16)} | ${String(n.whitebox.vgBranch).padEnd(5)} | ${sqlStr}${postStr}${getStr}`);
  }
  console.log('='.repeat(95));
}

function queryGraph(graph, keyword) {
  const kw = String(keyword || '').toLowerCase();
  const matchedNodes = Object.values(graph.nodes).filter(n => {
    return (
      n.path.toLowerCase().includes(kw) ||
      n.module.toLowerCase().includes(kw) ||
      n.edges.sqlTables.some(t => t.includes(kw)) ||
      n.inputs.POST.some(p => p.toLowerCase().includes(kw)) ||
      n.inputs.GET.some(g => g.toLowerCase().includes(kw)) ||
      n.functions.some(fn => fn.name.toLowerCase().includes(kw))
    );
  });

  console.log(`# KẾT QUẢ TRA CỨU ĐỒ THỊ DỰ ÁN NÉN CHO TỪ KHÓA: "${keyword}" (${matchedNodes.length} nodes)`);
  if (graph.testSuites[keyword.toUpperCase()]) {
    console.log('\n## Ánh xạ Bộ Test Data-Driven của Phân hệ:');
    console.log(JSON.stringify(graph.testSuites[keyword.toUpperCase()], null, 2));
  }

  for (const n of matchedNodes) {
    console.log(`\n### 📌 Node: \`${n.path}\` (Phân hệ: **${n.module}** | V(G) = **${n.whitebox.vgBranch}**)`);
    console.log(`- **Chỉ dẫn đọc tiết kiệm Token:** ${n.readHint.advice}`);
    console.log(`- **Inputs:** POST=${JSON.stringify(n.inputs.POST)} | GET=${JSON.stringify(n.inputs.GET)} | SESSION=${JSON.stringify(n.inputs.SESSION)}`);
    console.log(`- **Edges (Liên kết):** Includes=${JSON.stringify(n.edges.includes)} | Redirects=${JSON.stringify(n.edges.redirectsTo)} | SQL Tables=${JSON.stringify(n.edges.sqlTables)}`);
    if (n.functions.length > 0) {
      console.log(`- **Hàm định nghĩa:** ${n.functions.map(f => `${f.name}(${f.params}) [L${f.line}]`).join(', ')}`);
    }
  }
}

const mode = process.argv[2] || '--overview';
if (mode === '--build') {
  const g = buildGraph();
  console.log(`Đã nén thành công ${Object.keys(g.nodes).length} file PHP và 6 bộ Test Suite vào đồ thị: ${GRAPH_PATH}`);
  printOverview(g);
} else if (mode === '--query') {
  const g = loadOrBuildGraph();
  queryGraph(g, process.argv[3] || '');
} else {
  const g = loadOrBuildGraph();
  printOverview(g);
}
