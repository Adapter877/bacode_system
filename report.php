<?php
// report.php — หน้ารีพอร์ต (ไม่จำกัดสิทธิ์เข้าใช้ตามที่ร้องขอ)
session_start();

// ใช้ header/footer สาธารณะของหน้าเว็บหลัก เพื่อเลี่ยง redirect จากส่วน admin
include __DIR__ . '/header.php';

// ให้แน่ใจว่ามีตัวแปร $conn
if (!isset($conn)) {
  require_once __DIR__ . '/admin/dbconfig.php';
}
mysqli_set_charset($conn, 'utf8mb4');

// ---------- Helper ----------
function scalar(mysqli $conn, string $sql) {
  $res = mysqli_query($conn, $sql);
  if (!$res) return null;
  $row = mysqli_fetch_row($res);
  return $row ? $row[0] : null;
}
function fetch_all_assoc(mysqli $conn, string $sql) {
  $res = mysqli_query($conn, $sql);
  if (!$res) return [];
  $out = [];
  while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
  mysqli_free_result($res);
  return $out;
}

// ---------- KPIs ----------
$total_users        = (int) scalar($conn, "SELECT COUNT(*) FROM user_info");
$total_admins       = (int) scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role = 0");
$total_clubs        = (int) scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role = 1");
$total_students     = (int) scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role = 3");
$total_execs        = (int) scalar($conn, "SELECT COUNT(*) FROM user_info WHERE role = 4");

$total_acts         = (int) scalar($conn, "SELECT COUNT(*) FROM student_activities");
$total_hours_raw    = scalar($conn, "SELECT COALESCE(SUM(activity_hours),0) FROM student_activities");
$total_hours        = $total_hours_raw ? (float)$total_hours_raw : 0.0;

$acts_today         = (int) scalar($conn, "SELECT COUNT(*) FROM student_activities WHERE DATE(date_joined)=CURDATE()");
$barcodes_assigned  = (int) scalar($conn, "SELECT COUNT(*) FROM student_activities WHERE barcode IS NOT NULL AND barcode <> ''");
$barcodes_pending   = (int) scalar($conn, "SELECT COUNT(*) FROM student_activities WHERE barcode IS NULL OR barcode = ''");

// Top 10 กิจกรรมยอดนิยม
$top_activities = fetch_all_assoc($conn, "
  SELECT activity_name, COUNT(*) AS cnt
  FROM student_activities
  WHERE activity_name <> ''
  GROUP BY activity_name
  ORDER BY cnt DESC
  LIMIT 10
");

// ชั่วโมงตามสาขา (Top 10)
$hours_by_major = fetch_all_assoc($conn, "
  SELECT major, COALESCE(SUM(activity_hours),0) AS hours
  FROM student_activities
  WHERE major <> ''
  GROUP BY major
  ORDER BY hours DESC
  LIMIT 10
");

// กิจกรรมรายเดือน 12 เดือนล่าสุด
$by_month = fetch_all_assoc($conn, "
  SELECT DATE_FORMAT(date_joined, '%Y-%m') AS ym, COUNT(*) AS cnt
  FROM student_activities
  WHERE date_joined >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY ym
  ORDER BY ym ASC
");

// ล่าสุด 20 รายการ
$recent_20 = fetch_all_assoc($conn, "
  SELECT id, student_name, student_id, major, activity_name, activity_hours, barcode, contact, date_joined, created_at
  FROM student_activities
  ORDER BY created_at DESC
  LIMIT 20
");

// เตรียมข้อมูล chart
$chart_top_act_labels  = array_map(fn($r) => $r['activity_name'], $top_activities);
$chart_top_act_values  = array_map(fn($r) => (int)$r['cnt'],     $top_activities);

$chart_major_labels    = array_map(fn($r) => $r['major'],        $hours_by_major);
$chart_major_values    = array_map(fn($r) => (float)$r['hours'], $hours_by_major);

$chart_month_labels    = array_map(fn($r) => $r['ym'],           $by_month);
$chart_month_values    = array_map(fn($r) => (int)$r['cnt'],     $by_month);
?>
<style>
  /* พื้นที่หน้า */
  html, body { height: 100%; }
  main#report { padding: 24px 0; }
  /* การ์ด KPI */
  .kpi .display-6 { font-weight: 700; }
  .muted { color: #6b7280; font-size: .9rem; }
  /* ตาราง */
  .table-responsive { max-height: 60vh; overflow: auto; }
  .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
  .code { font-family: ui-monospace, Menlo, Consolas, "Liberation Mono", monospace; }
</style>

<main id="report" class="container">
  <div class="mb-4">
    <h2 class="h4 mb-1">รายงานภาพรวมระบบกิจกรรมนักศึกษา</h2>
    <div class="muted">สรุปข้อมูลผู้ใช้ กิจกรรม ชั่วโมง และสถานะบาร์โค้ด</div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 kpi">
    <div class="col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="muted">ผู้ใช้ทั้งหมด</div>
          <div class="display-6"><?= number_format($total_users) ?></div>
          <div class="small text-muted">
            แอดมิน: <?= $total_admins ?> | สโมสร: <?= $total_clubs ?> | นักศึกษา: <?= $total_students ?> | ผู้บริหาร: <?= $total_execs ?>
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
          <div class="small text-muted">กำหนดแล้ว / รอดำเนินการ: <?= number_format($barcodes_pending) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-3 mt-1">
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-dark text-white">Top 10 กิจกรรม (ตามจำนวนผู้เข้าร่วม)</div>
        <div class="card-body">
          <canvas id="chartTopAct" height="220"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-dark text-white">ชั่วโมงรวมตามสาขาวิชา (Top 10)</div>
        <div class="card-body">
          <canvas id="chartMajor" height="220"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">จำนวนกิจกรรมรายเดือน (12 เดือนล่าสุด)</div>
        <div class="card-body">
          <canvas id="chartByMonth" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent table -->
  <div class="card shadow-sm mt-3">
    <div class="card-header bg-dark text-white">รายการล่าสุด (20 รายการ)</div>
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
              <th>วันที่เข้าร่วม</th>
              <th>บันทึกเมื่อ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent_20)): ?>
              <tr><td colspan="9" class="text-center py-4">ไม่มีข้อมูล</td></tr>
            <?php else: ?>
              <?php $i=1; foreach ($recent_20 as $r): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($r['student_name'] ?? '-') ?></td>
                  <td class="code"><?= htmlspecialchars($r['student_id'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['major'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['activity_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['activity_hours'] ?? '0') ?></td>
                  <td class="code"><?= htmlspecialchars($r['barcode'] ?? '') ?></td>
                  <td><?= $r['date_joined'] ? htmlspecialchars(date('d/m/Y', strtotime($r['date_joined']))) : '-' ?></td>
                  <td><?= $r['created_at'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($r['created_at']))) : '-' ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const topActLabels = <?= json_encode($chart_top_act_labels, JSON_UNESCAPED_UNICODE) ?>;
const topActValues = <?= json_encode($chart_top_act_values, JSON_UNESCAPED_UNICODE) ?>;
const majorLabels  = <?= json_encode($chart_major_labels,   JSON_UNESCAPED_UNICODE) ?>;
const majorValues  = <?= json_encode($chart_major_values,   JSON_UNESCAPED_UNICODE) ?>;
const monthLabels  = <?= json_encode($chart_month_labels,   JSON_UNESCAPED_UNICODE) ?>;
const monthValues  = <?= json_encode($chart_month_values,   JSON_UNESCAPED_UNICODE) ?>;

function barChart(el, labels, data, title='') {
  const ctx = document.getElementById(el);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label: title, data }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}
function lineChart(el, labels, data, title='') {
  const ctx = document.getElementById(el);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{ label: title, data, tension: .25 }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

barChart('chartTopAct', topActLabels, topActValues, 'จำนวนผู้เข้าร่วม');
barChart('chart
