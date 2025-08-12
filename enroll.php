<?php
session_start();
include "admin/dbconfig.php";

// ต้องล็อกอินก่อน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ---------- โหลดข้อมูลผู้ใช้ ---------- */
$user_id = intval($_SESSION['user_id']);
$sql_user = "SELECT id, username, name, email, student_id, major FROM user_info WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$name       = $user['name']       ?? '';
$username   = $user['username']   ?? '';
$email      = $user['email']      ?? '';
$student_id = $user['student_id'] ?? $username; // fallback
$major      = $user['major']      ?? '';

/* ---------- รับ post_id เพื่อดึงชื่อกิจกรรม ---------- */
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$activity_name = '';
if ($post_id > 0) {
    $sql_post = "SELECT posts_title FROM posts WHERE posts_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql_post);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    $activity_name = $post['posts_title'] ?? '';
}

/* ---------- ค่าคงที่สาขา คณะวิทยาศาสตร์ ---------- */
$majors_all = [
    "วิทยาการคอมพิวเตอร์",
    "เทคโนโลยีสารสนเทศ",
    "วิทยาศาสตร์สิ่งแวดล้อม",
    "ชีววิทยา",
    "เคมี",
    "ฟิสิกส์",
    "คณิตศาสตร์ประยุกต์",
    "สถิติ",
    "วิทยาศาสตร์ทั่วไป",
    "วิทยาศาสตร์การกีฬา",
    "อาชีวอนามัยและความปลอดภัย",
    "เกษตรศาสตร์",
    "เทคโนโลยีการอาหาร",
];

$error = "";
$success = "";

/* ---------- Submit -> บันทึก ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $student_name_f  = trim($_POST['student_name']  ?? $name);
    $student_id_f    = trim($_POST['student_id']    ?? $student_id);
    $major_f         = trim($_POST['major']         ?? $major);
    $activity_name_f = trim($_POST['activity_name'] ?? $activity_name);
    $contact         = trim($_POST['contact']       ?? $email);

    if ($student_name_f === '' || $student_id_f === '' || $activity_name_f === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!in_array($major_f, $majors_all, true)) {
        $error = "กรุณาเลือกสาขาวิชาที่ถูกต้อง";
    } else {
        // ไม่รับชั่วโมงกิจกรรมจากผู้ใช้ -> ตั้งเป็น 0
        $activity_hours = 0;
        // barcode เว้นว่าง รออัปเดตภายหลัง
        $barcode = "";

        /* บันทึก date_joined เป็นเวลาปัจจุบัน (TIMESTAMP/DATETIME) ด้วย NOW() */
        $sql_ins = "INSERT INTO student_activities
            (student_name, student_id, major, activity_name, activity_hours, barcode, contact, date_joined)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql_ins);
        if ($stmt === false) {
            $error = "ไม่สามารถบันทึกข้อมูล (เตรียมคำสั่งล้มเหลว): " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssiss",
                $student_name_f, $student_id_f, $major_f, $activity_name_f, $activity_hours, $barcode, $contact
            );
            if (mysqli_stmt_execute($stmt)) {
                $success = "บันทึกข้อมูลเรียบร้อย";
                echo "<script>setTimeout(function(){ window.location='about.php'; }, 900);</script>";
            } else {
                $error = "ไม่สามารถบันทึกข้อมูล: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

/* ---------- header ---------- */
include "header.php";
?>
<style>
html, body { height: 100%; }
.main-wrapper{ display:flex; flex-direction:column; min-height:100vh; }
.content-wrapper{ flex:1; display:flex; align-items:center; justify-content:center; }
</style>

<div class="main-wrapper">
  <div class="content-wrapper">
    <div class="container my-5">
      <div class="row justify-content-center w-100">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <span>สมัครเข้าร่วมกิจกรรม</span>
              <?php if ($activity_name): ?>
                <span class="badge badge-light"><?php echo htmlspecialchars($activity_name); ?></span>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
              <?php endif; ?>
              <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
              <?php endif; ?>

              <form method="post" action="">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="student_name" class="form-control"
                           value="<?php echo htmlspecialchars($name); ?>" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label>รหัสนักศึกษา</label>
                    <input type="text" name="student_id" class="form-control"
                           value="<?php echo htmlspecialchars($student_id); ?>" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>สาขาวิชา</label>
                    <select name="major" class="form-control" required>
                      <option value="">-- เลือกสาขาวิชา --</option>
                      <?php foreach ($majors_all as $m): ?>
                        <option value="<?php echo htmlspecialchars($m); ?>"
                          <?php echo ($major === $m) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($m); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <label>อีเมลติดต่อ</label>
                    <input type="email" name="contact" class="form-control"
                           value="<?php echo htmlspecialchars($email); ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label>ชื่อกิจกรรม</label>
                  <input type="text" name="activity_name" class="form-control"
                         value="<?php echo htmlspecialchars($activity_name); ?>" readonly>
                </div>

                <!-- เอาช่อง 'ชั่วโมงกิจกรรม' ออก และ 'วันที่เข้าร่วม' ใช้เวลาปัจจุบันอัตโนมัติ -->
                <div class="small text-muted mb-3">
                  ระบบจะบันทึก <strong>วันที่/เวลาเข้าร่วม</strong> เป็นเวลาปัจจุบันอัตโนมัติเมื่อกดยืนยัน
                </div>

                <div class="d-flex justify-content-between">
                  <a href="blog.php" class="btn btn-outline-secondary">ย้อนกลับ</a>
                  <button type="submit" name="submit" class="btn btn-primary">ยืนยันการสมัคร</button>
                </div>
              </form>
            </div>
          </div>

          <div class="small text-muted mt-2 text-center">
            หลังบันทึกสำเร็จ ระบบจะพาคุณไปยังหน้า “ตารางบันทึกกิจกรรม” อัตโนมัติ
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include "footer.php"; ?>
</div>

<?php mysqli_close($conn); ?>
