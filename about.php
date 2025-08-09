<?php
session_start();
include "admin/dbconfig.php";

// ตรวจการเชื่อมต่อฐานข้อมูล
if (!$conn || $conn === false) {
    die("❌ Database connection failed: " . mysqli_connect_error() .
        " (โปรดตรวจ admin/dbconfig.php: host, user, password, database)");
}

// ต้องล็อกอินก่อน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// -------- ดึงข้อมูลผู้ใช้ ----------
$user_id = (int)$_SESSION['user_id'];
$sql_user = "SELECT id, username, name, email, student_id, major FROM user_info WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql_user);
if ($stmt === false) {
    die("❌ Prepare failed (user): " . mysqli_error($conn) . " | SQL: " . $sql_user);
}
mysqli_stmt_bind_param($stmt, "i", $user_id);
if (!mysqli_stmt_execute($stmt)) {
    die("❌ Execute failed (user): " . mysqli_error($conn));
}
$res  = mysqli_stmt_get_result($stmt);
if ($res === false) {
    die("❌ Get result failed (user): " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($res) ?: [];
mysqli_stmt_close($stmt);

$name       = trim($user['name'] ?? '');
$username   = trim($user['username'] ?? '');
$email      = trim($user['email'] ?? '');
$student_id = trim($user['student_id'] ?? $username);
$major      = trim($user['major'] ?? '');

// normalize student_id (ลบช่องว่าง/ขีดทิ้ง)
$student_id_norm = preg_replace('/\D+/', '', $student_id);
$username_norm   = preg_replace('/\D+/', '', $username);

// -------- ดึงกิจกรรมของผู้ใช้ ----------
$activities   = [];
$total_hours  = 0.0;
$count_acts   = 0;

// ค้นหาแบบยืดหยุ่น: จาก student_id (norm), username (norm), email, และชื่อ (partial)
$sql_act = "
SELECT id, student_name, student_id, major, activity_name, activity_hours, barcode, contact,
       date_joined, created_at
FROM student_activities
WHERE
    TRIM(REPLACE(REPLACE(student_id,' ',''),'-','')) IN (?, ?)  /* เทียบแบบ normalize */
    OR contact = ?                                             /* อีเมล */
    OR student_name LIKE CONCAT('%', ?, '%')                   /* ชื่อแบบ partial */
ORDER BY COALESCE(date_joined, created_at) DESC
LIMIT 200
";
$stmt = mysqli_prepare($conn, $sql_act);
if ($stmt === false) {
    die("❌ Prepare failed (activities): " . mysqli_error($conn) . " | SQL: " . $sql_act);
}
mysqli_stmt_bind_param($stmt, "ssss", $student_id_norm, $username_norm, $email, $name);
if (!mysqli_stmt_execute($stmt)) {
    die("❌ Execute failed (activities): " . mysqli_error($conn));
}
$result = mysqli_stmt_get_result($stmt);
if ($result === false) {
    die("❌ Get result failed (activities): " . mysqli_error($conn));
}
while ($row = mysqli_fetch_assoc($result)) {
    $activities[]  = $row;
    $total_hours  += (float)$row['activity_hours'];
    $count_acts++;
}
mysqli_stmt_close($stmt);

// ถ้ายังไม่พบ แสดง fallback: ล่าสุดในระบบ 50 รายการ (เพื่อยืนยันว่าตารางมีข้อมูล)
$fallback_rows = [];
if ($count_acts === 0) {
    $sql_fb = "
      SELECT id, student_name, student_id, major, activity_name, activity_hours, barcode, contact,
             date_joined, created_at
      FROM student_activities
      ORDER BY COALESCE(date_joined, created_at) DESC
      LIMIT 50
    ";
    $qfb = mysqli_query($conn, $sql_fb);
    if ($qfb) {
        while ($r = mysqli_fetch_assoc($qfb)) {
            $fallback_rows[] = $r;
        }
    }
}

// header
include "header.php";
?>

<!-- Sticky footer CSS สำหรับหน้านี้ -->
<style>
  html, body { height: 100%; }
  body { min-height: 100vh; display: flex; flex-direction: column; }
  main#page { flex: 1 0 auto; }
</style>

<main id="page">
  <section class="container my-5">

    <!-- หัวข้อ -->
    <div class="mb-3 text-center">
      <h2 class="h4 mb-1">
        ผลการค้นหากิจกรรมล่าสุด: 
        <span class="fw-bold"><?php echo htmlspecialchars($name ?: $username); ?></span>
      </h2>
      <div class="text-muted small">
        รหัสนักศึกษา: <?php echo htmlspecialchars($student_id ?: '-'); ?> |
        สาขา: <?php echo htmlspecialchars($major ?: '-'); ?>
      </div>
    </div>

    <!-- ตารางกิจกรรม -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-dark text-white text-center">ตารางกิจกรรม</div>
      <div class="card-body p-0">
        <?php if ($count_acts > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped mb-0 text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>วันที่เข้าร่วม</th>
                <th>ชื่อกิจกรรม</th>
                <th>ชั่วโมงกิจกรรม</th>
                <th>หลักสูตร</th>
                <th>บาร์โค้ด</th>
                <th>ติดต่อ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activities as $a): ?>
              <tr>
                <td><?php echo htmlspecialchars($a['date_joined'] ?: $a['created_at']); ?></td>
                <td><?php echo htmlspecialchars($a['activity_name']); ?></td>
                <td><?php echo htmlspecialchars($a['activity_hours']); ?></td>
                <td><?php echo htmlspecialchars($a['major']); ?></td>
                <td><?php echo htmlspecialchars($a['barcode']); ?></td>
                <td><?php echo htmlspecialchars($a['contact']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="p-3 text-center">ไม่พบกิจกรรมสำหรับผู้ใช้นี้</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- การ์ดสรุป -->
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm text-center">
          <div class="card-body">
            <div class="fw-semibold">จำนวนกิจกรรม</div>
            <div class="display-6"><?php echo $count_acts; ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm text-center">
          <div class="card-body">
            <div class="fw-semibold">ชั่วโมงรวม</div>
            <div class="display-6"><?php echo number_format($total_hours,1); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm text-center">
          <div class="card-body">
            <div class="fw-semibold">กิจกรรมล่าสุด</div>
            <div><?php echo $count_acts > 0 ? htmlspecialchars($activities[0]['activity_name']) : '-'; ?></div>
            <div class="text-muted small">
              <?php echo $count_acts > 0 ? htmlspecialchars($activities[0]['date_joined']) : ''; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>
</main>



<?php include "footer.php"; ?>
<?php mysqli_close($conn); ?>
