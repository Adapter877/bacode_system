<?php
// report.php — หน้ารีพอร์ต (ใช้ navbar/footer เดิม) + ฟิลเตอร์ + กราฟ + Export CSV

// **อย่า echo อะไร ก่อนส่วน Export CSV ด้านล่าง**

require_once __DIR__ . '/admin/dbconfig.php';
mysqli_set_charset($conn, 'utf8mb4');

/* ----------------- Helpers ----------------- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function q_all(mysqli $conn, string $sql, string $types = '', array $params = []) : array {
  if ($types && $params) {
    $st = mysqli_prepare($conn, $sql); if(!$st) return [];
    mysqli_stmt_bind_param($st, $types, ...$params);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    mysqli_stmt_close($st);
    return $rows;
  } else {
    $res = mysqli_query($conn, $sql);
    if (!$res) return [];
    $out = [];
    while($r = mysqli_fetch_assoc($res)) $out[] = $r;
    mysqli_free_result($res);
    return $out;
  }
}
function q_scalar(mysqli $conn, string $sql, string $types = '', array $params = []) {
  $rows = q_all($conn, $sql, $types, $params);
  if (!$rows) return null;
  $first = array_shift($rows);
  return $first ? array_shift($first) : null;
}
function wherePlus(string $baseWhere, string $extra): string {
  return $baseWhere ? ($baseWhere . ' AND ' . $extra) : ('WHERE ' . $extra);
}

/* ----------------- Read filters ----------------- */
$start_date = isset($_GET['start']) ? trim($_GET['start']) : '';
$end_date   = isset($_GET['end'])   ? trim($_GET['end'])   : '';
$activity   = isset($_GET['activity']) ? trim($_GET['activity']) : '';
$major      = isset($_GET['major'])    ? trim($_GET['major'])    : '';

if ($start_date === '' && $end_date === '') {
  $start_date = date('Y-m-d', strtotime('-12 months'));
  $end_date   = date('Y-m-d');
}

/* ----------------- Build WHERE (prepared) ----------------- */
$where  = [];
$types  = '';
$params = [];

if ($start_date !== '') { $where[] = 'date_joined >= ?'; $types.='s'; $params[]=$start_date; }
if ($end_date   !== '') { $where[] = 'date_joined <= ?'; $types.='s'; $params[]=$end_date; }
if ($activity   !== '') { $where[] = 'activity_name = ?'; $types.='s'; $params[]=$activity; }
if ($major      !== '') { $where[] = 'major = ?';         $types.='s'; $params[]=$major; }

$where_sql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* ----------------- Export CSV (ต้องทำก่อนมี output ใดๆ) ----------------- */
if (isset($_GET['export']) && $_GET['export']==='csv') {
  $rows = q_all(
    $conn,
    "SELECT student_name, student_id, major, activity_name, activity_hours, barcode, contact, date_joined, created_at
     FROM student_activities
     $where_sql
     ORDER BY created_at DESC",
    $types, $params
  );
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=activities_report_'.date('Ymd_His').'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['student_name','student_id','major','activity_name','activity_hours','barcode','contact','date_joined','created_at']);
  foreach ($rows as $r) {
    fputcsv($out, [
      $r['student_name'], $r['student_id'], $r['major'], $r['activity_name'],
      $r['activity_hours'], $r['barcode'], $r['contact'], $r['date_joined'], $r['created_at']
    ]);
  }
  fclose($out);
  exit;
}

/* ----------------- Options (filters) ----------------- */
$activity_opts = q_all($conn, "SELECT DISTINCT activity_name FROM student_activities WHERE activity_name<>'' ORDER BY activity_name ASC");
$major_opts    = q_all($conn, "SELECT DISTINCT major FROM student_activities WHERE major<>'' ORDER BY major ASC");

/* ----------------- KPIs (เคารพฟิลเตอร์) ----------------- */
// Users (ทั้งระบบ)
$total_users    = (int) q_scalar($conn, "SELECT COUNT(*) FROM user_info");
$total_admins   = (int) q_scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role=0");
$total_clubs    = (int) q_scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role=1");
$total_students = (int) q_scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role=3");
$total_execs    = (int) q_scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role=4");

// Activities (กรอง)
$total_acts   = (int) (q_scalar($conn, "SELECT COUNT(*) FROM student_activities $where_sql", $types, $params) ?? 0);
$total_hours  = (float) (q_scalar($conn, "SELECT COALESCE(SUM(activity_hours),0) FROM student_activities $where_sql", $types, $params) ?? 0);

$acts_today   = (int) (q_scalar($conn,
  "SELECT COUNT(*) FROM student_activities ".wherePlus($where_sql, "DATE(date_joined)=CURDATE()"),
  $types, $params
) ?? 0);

$barcodes_assigned = (int) (q_scalar($conn,
  "SELECT COUNT(*) FROM student_activities ".wherePlus($where_sql, "(barcode IS NOT NULL AND barcode <> '')"),
  $types, $params
) ?? 0);

$barcodes_pending  = (int) (q_scalar($conn,
  "SELECT COUNT(*) FROM student_activities ".wherePlus($where_sql, "(barcode IS NULL OR barcode = '')"),
  $types, $params
) ?? 0);

/* ----------------- Charts data (กรอง) ----------------- */
$top_activities = q_all(
  $conn,
  "SELECT activity_name, COUNT(*) AS cnt
   FROM student_activities
   ".wherePlus($where_sql, "activity_name <> ''")."
   GROUP BY activity_name
   ORDER BY cnt DESC
   LIMIT 10",
  $types, $params
);

$hours_by_major = q_all(
  $conn,
  "SELECT major, COALESCE(SUM(activity_hours),0) AS hours
   FROM student_activities
   ".wherePlus($where_sql, "major <> ''")."
   GROUP BY major
   ORDER BY hours DESC
   LIMIT 10",
  $types, $params
);

$by_month = q_all(
  $conn,
  "SELECT DATE_FORMAT(date_joined, '%Y-%m') AS ym, COUNT(*) AS cnt
   FROM student_activities
   $where_sql
   GROUP BY ym
   ORDER BY ym ASC",
  $types, $params
);

$recent_50 = q_all(
  $conn,
  "SELECT id, student_name, student_id, major, activity_name, activity_hours, barcode, contact, date_joined, created_at
   FROM student_activities
   $where_sql
   ORDER BY created_at DESC
   LIMIT 50",
  $types, $params
);

/* ----------------- To JS ----------------- */
$chart_top_act_labels  = array_map(fn($r)=>$r['activity_name'], $top_activities);
$chart_top_act_values  = array_map(fn($r)=>(int)$r['cnt'],     $top_activities);
$chart_major_labels    = array_map(fn($r)=>$r['major'],        $hours_by_major);
$chart_major_values    = array_map(fn($r)=>(float)$r['hours'], $hours_by_major);
$chart_month_labels    = array_map(fn($r)=>$r['ym'],           $by_month);
$chart_month_values    = array_map(fn($r)=>(int)$r['cnt'],     $by_month);

/* ----------------- เริ่ม Output: ใช้ navbar/footer เดิม ----------------- */
include __DIR__ . '/header.php';
?>
<style>
  html, body { height: 100%; }
  main#report { padding: 24px 0 48px; }
  .muted { color: #6b7280; font-size: .92rem; }
  .filter-card .form-control { min-height: 38px; }
  .filter-actions .btn { min-width: 120px; }
  .kpi .display-6 { font-weight: 700; }
  .card .card-header { font-weight:600; }
  .table-responsive { max-height: 60vh; overflow: auto; }
  .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
  .code { font-family: ui-monospace, Menlo, Consolas, "Liberation Mono", monospace; }
  .badge-soft { background: #eef2ff; color:#1e3a8a; }
</style>

<main id="report" class="container">

  <div class="mb-4">
    <h2 class="h4 mb-1">รายงานภาพรวมกิจกรรมนักศึกษา</h2>
    <div class="muted">สรุปผู้ใช้ กิจกรรม ชั่วโมง และสถานะบาร์โค้ด (ข้อมูลตามตัวกรองด้านล่าง)</div>
  </div>

  <!-- Filters -->
  <div class="card shadow-sm mb-3 filter-card">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="get">
        <div class="col-md-3">
          <label class="form-label">วันที่เริ่ม</label>
          <input type="date" name="start" class="form-control" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">วันที่สิ้นสุด</label>
          <input type="date" name="end" class="form-control" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">กิจกรรม</label>
          <select name="activity" class="form-control">
            <option value="">— ทั้งหมด —</option>
            <?php foreach($activity_opts as $opt): ?>
              <option value="<?= esc($opt['activity_name']) ?>" <?= $activity===$opt['activity_name']?'selected':'' ?>>
                <?= esc($opt['activity_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">สาขา</label>
          <select name="major" class="form-control">
            <option value="">— ทั้งหมด —</option>
            <?php foreach($major_opts as $opt): ?>
              <option value="<?= esc($opt['major']) ?>" <?= $major===$opt['major']?'selected':'' ?>>
                <?= esc($opt['major']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 d-flex gap-2 filter-actions mt-3">
          <button class="btn btn-primary"><i class="fa fa-filter"></i> ใช้ตัวกรอง</button>
          <a class="btn btn-light" href="report.php"><i class="fa fa-undo"></i> ล้างตัวกรอง</a>
          <a class="btn btn-success ml-auto"
             href="<?= 'report.php?'.http_build_query(array_filter(['start'=>$start_date,'end'=>$end_date,'activity'=>$activity,'major'=>$major])).'&export=csv' ?>">
            <i class="fa fa-download"></i> ส่งออก CSV
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 kpi">
    <div class="col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="muted">ผู้ใช้ทั้งหมด</div>
          <div class="display-6"><?= number_format($total_users) ?></div>
          <div class="small text-muted">
            แอดมิน: <?= $total_admins ?> · สโมสร: <?= $total_clubs ?> · นักศึกษา: <?= $total_students ?> · ผู้บริหาร: <?= $total_execs ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="muted">กิจกรรมที่บันทึก</div>
          <div class="display-6"><?= number_format($total_acts) ?></div>
          <div class="small text-muted">วันนี้: <?= number_format($acts_today) ?></div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="muted">ชั่วโมงรวม</div>
          <div class="display-6"><?= number_format($total_hours, 1) ?></div>
          <div class="small text-muted">ชั่วโมง</div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="muted">สถานะบาร์โค้ด</div>
          <div class="display-6"><?= number_format($barcodes_assigned) ?></div>
          <div class="small text-muted">กำหนดแล้ว / รอ: <?= number_format($barcodes_pending) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-3 mt-2">
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-dark text-white">Top 10 กิจกรรม (ตามจำนวนผู้เข้าร่วม)</div>
        <div class="card-body">
          <canvas id="chartTopAct" height="220"></canvas>
          <?php if (!$top_activities): ?><div class="text-center text-muted small mt-2">ไม่มีข้อมูลตามตัวกรอง</div><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-dark text-white">ชั่วโมงรวมตามสาขาวิชา (Top 10)</div>
        <div class="card-body">
          <canvas id="chartMajor" height="220"></canvas>
          <?php if (!$hours_by_major): ?><div class="text-center text-muted small mt-2">ไม่มีข้อมูลตามตัวกรอง</div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">จำนวนกิจกรรมรายเดือน</div>
        <div class="card-body">
          <canvas id="chartByMonth" height="140"></canvas>
          <?php if (!$by_month): ?><div class="text-center text-muted small mt-2">ไม่มีข้อมูลตามตัวกรอง</div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent table -->
  <div class="card shadow-sm mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="font-weight-semibold">รายการล่าสุด (50 รายการ)</span>
      <span class="badge badge-soft"><?= esc($start_date) ?> → <?= esc($end_date) ?></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>ชื่อ-สกุล</th>
              <th>รหัสนักศึกษา</th>
              <th>สาขา</th>
              <th>ชื่อกิจกรรม</th>
              <th>ชั่วโมง</th>
              <th>บาร์โค้ด</th>
              <th>ติดต่อ</th>
              <th>วันที่เข้าร่วม</th>
              <th>บันทึกเมื่อ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent_50)): ?>
              <tr><td colspan="10" class="text-center py-4">ไม่มีข้อมูล</td></tr>
            <?php else: $i=1; foreach ($recent_50 as $r): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($r['student_name'] ?? '-') ?></td>
                <td class="code"><?= esc($r['student_id'] ?? '-') ?></td>
                <td><?= esc($r['major'] ?? '-') ?></td>
                <td><?= esc($r['activity_name'] ?? '-') ?></td>
                <td><?= esc($r['activity_hours'] ?? '0') ?></td>
                <td class="code"><?= esc($r['barcode'] ?? '') ?></td>
                <td><?= esc($r['contact'] ?? '-') ?></td>
                <td><?= $r['date_joined'] ? esc(date('d/m/Y', strtotime($r['date_joined']))) : '-' ?></td>
                <td><?= $r['created_at'] ? esc(date('d/m/Y H:i', strtotime($r['created_at']))) : '-' ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<!-- โหลดเฉพาะสคริปต์ที่หน้านี้ต้องใช้ (Navbar/Bootstrap มาจาก header/footer เดิมอยู่แล้ว) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const topActLabels = <?= json_encode($chart_top_act_labels, JSON_UNESCAPED_UNICODE) ?>;
const topActValues = <?= json_encode($chart_top_act_values, JSON_UNESCAPED_UNICODE) ?>;
const majorLabels  = <?= json_encode($chart_major_labels,   JSON_UNESCAPED_UNICODE) ?>;
const majorValues  = <?= json_encode($chart_major_values,   JSON_UNESCAPED_UNICODE) ?>;
const monthLabels  = <?= json_encode($chart_month_labels,   JSON_UNESCAPED_UNICODE) ?>;
const monthValues  = <?= json_encode($chart_month_values,   JSON_UNESCAPED_UNICODE) ?>;

function wrapLabel(label, maxLen = 28) {
  if (!label) return '';
  const words = String(label).split(/\s+/);
  const lines = [];
  let cur = '';
  for (const w of words) {
    if ((cur + ' ' + w).trim().length <= maxLen) cur = (cur ? cur + ' ' : '') + w;
    else { if (cur) lines.push(cur); cur = w; }
  }
  if (cur) lines.push(cur);
  return lines;
}

function drawTopActivities(id, labels, data) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;

  el.height = Math.max(220, labels.length * 34 + 40);
  Chart.register(ChartDataLabels);

  const total = data.reduce((a,b)=>a+b,0);

  new Chart(el, {
    type: 'bar',
    data: {
      labels: labels.map(l => wrapLabel(l, 28)),
      datasets: [{ data, borderWidth: 1 }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { right: 12 } },
      scales: {
        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.06)' } },
        y: { grid: { display: false } }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const v = ctx.parsed.x || 0, pct = total ? (v*100/total) : 0;
              return ` ${v.toLocaleString('th-TH')} คน (${pct.toFixed(1)}%)`;
            }
          }
        },
        datalabels: {
          anchor: 'end',
          align: 'right',
          clamp: true,
          formatter: (v) => {
            const pct = total ? (v*100/total) : 0;
            return `${v.toLocaleString('th-TH')} คน (${pct.toFixed(1)}%)`;
          },
          color: '#111',
          font: { weight: 600 }
        }
      }
    }
  });
}

function drawMajorHours(id, labels, data) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;

  el.height = Math.max(220, labels.length * 30 + 40);
  Chart.register(ChartDataLabels);

  new Chart(el, {
    type: 'bar',
    data: {
      labels: labels.map(l => wrapLabel(l, 26)),
      datasets: [{ data, borderWidth: 1 }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { right: 12 } },
      scales: {
        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' } },
        y: { grid: { display: false } }
      },
      plugins: {
        legend: { display: false },
        datalabels: {
          anchor: 'end',
          align: 'right',
          clamp: true,
          formatter: (v) => `${(+v).toLocaleString('th-TH', {maximumFractionDigits:1})} ชม.`,
          color: '#111',
          font: { weight: 600 }
        },
        tooltip: {
          callbacks: {
            label: (ctx) => ` ${(+ctx.parsed.x).toLocaleString('th-TH', {maximumFractionDigits:1})} ชั่วโมง`
          }
        }
      }
    }
  });
}

function drawMonthly(id, labels, data) {
  const el = document.getElementById(id);
  if (!el || !labels.length) return;

  new Chart(el, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        data,
        tension: .25,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false }},
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.06)' } },
        x: { grid: { display: false } }
      }
    }
  });
}

drawTopActivities('chartTopAct', topActLabels, topActValues);
drawMajorHours('chartMajor', majorLabels, majorValues);
drawMonthly('chartByMonth', monthLabels, monthValues);
</script>
<?php include __DIR__ . '/footer.php'; ?>
