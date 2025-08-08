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
$user_id = $_SESSION['user_id'];
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
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$name       = $user['name'] ?? '';
$username   = $user['username'] ?? '';
$email      = $user['email'] ?? '';
$student_id = $user['student_id'] ?? $username;
$major      = $user['major'] ?? '';

// -------- ดึงกิจกรรมของผู้ใช้ ----------
$activities   = [];
$total_hours  = 0;
$count_acts   = 0;

$sql_act = "SELECT id, student_name, student_id, major, activity_name, activity_hours, barcode, contact, date_joined
            FROM student_activities
            WHERE student_id = ? OR student_id = ?
            ORDER BY date_joined DESC";
$stmt = mysqli_prepare($conn, $sql_act);
if ($stmt === false) {
    die("❌ Prepare failed (activities): " . mysqli_error($conn) . " | SQL: " . $sql_act);
}
mysqli_stmt_bind_param($stmt, "ss", $username, $student_id);
if (!mysqli_stmt_execute($stmt)) {
    die("❌ Execute failed (activities): " . mysqli_error($conn));
}
$result = mysqli_stmt_get_result($stmt);
if ($result === false) {
    die("❌ Get result failed (activities): " . mysqli_error($conn));
}
while ($row = mysqli_fetch_assoc($result)) {
    $activities[]  = $row;
    $total_hours  += floatval($row['activity_hours']);
    $count_acts++;
}
mysqli_stmt_close($stmt);

// header
include "header.php";
?>

<!-- Sticky footer CSS สำหรับหน้านี้ -->
<style>
  html, body { height: 100%; }
  body { min-height: 100vh; display: flex; flex-direction: column; }
  main#page { flex: 1 0 auto; } /* ดันคอนเทนต์กินพื้นที่ที่เหลือ ให้ footer อยู่ล่าง */
</style>

<main id="page">
  <section class="container my-5">
    <div class="mb-3">
      <h2 class="h4 mb-1">ผลการค้นหากิจกรรมล่าสุด: <span class="fw-bold"><?php echo htmlspecialchars($name); ?></span></h2>
      <div class="text-muted small">
        รหัสนักศึกษา: <?php echo htmlspecialchars($student_id); ?>
        | สาขา: <?php echo htmlspecialchars($major ?: '-'); ?>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header bg-dark text-white">ตารางกิจกรรม</div>
      <div class="card-body p-0">
        <?php if ($count_acts > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-dark">
              <tr>
                <th>วันที่เข้าร่วม</th>
                <th>ชื่อกิจกรรม</th>
                <th>ชั่วโมงกิจกรรมที่ได้รับ</th>
                <th>หลักสูตร</th>
                <th>บาร์โค้ด</th>
                <th>ติดต่อ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activities as $a): ?>
              <tr>
                <td><?php echo htmlspecialchars($a['date_joined']); ?></td>
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
          <div class="p-3">ไม่พบกิจกรรมสำหรับผู้ใช้นี้</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="fw-semibold">จำนวนกิจกรรม</div>
            <div class="display-6"><?php echo $count_acts; ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="fw-semibold">ชั่วโมงรวม</div>
            <div class="display-6"><?php echo number_format($total_hours,1); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="fw-semibold">กิจกรรมล่าสุด</div>
            <div><?php echo $count_acts > 0 ? htmlspecialchars($activities[0]['activity_name']) : '-'; ?></div>
            <div class="text-muted small"><?php echo $count_acts > 0 ? htmlspecialchars($activities[0]['date_joined']) : ''; ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include "footer.php"; ?>
<?php mysqli_close($conn); ?>
