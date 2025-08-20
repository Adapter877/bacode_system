<?php
// admin/edit_user.php

session_start();
require_once __DIR__ . '/dbconfig.php';

/* ---------------- Guard: admin only ---------------- */
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 0) {
    header("Location: index.php");
    exit();
}

/* ---------------- Read target user id ---------------- */
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    header("Location: all_users.php");
    exit();
}

$error   = '';
$success = '';

/* ---------------- Load current user record ---------------- */
function load_user(mysqli $conn, int $id): ?array {
    $sql = "SELECT id, name, username, email, role FROM user_info WHERE id = ? LIMIT 1";
    if (!$st = mysqli_prepare($conn, $sql)) return null;
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($st);
    return $row ?: null;
}

$record = load_user($conn, $user_id);
if (!$record) {
    header("Location: all_users.php");
    exit();
}

/* ---------------- Handle Update ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id         = (int)($_POST['id'] ?? 0);
    $name       = trim($_POST['name'] ?? '');
    $u_name     = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = (string)($_POST['password'] ?? '');
    $role_sel   = (string)($_POST['select'] ?? '');

    if ($id !== $user_id) {
        $error = "คำขอไม่ถูกต้อง";
    } elseif ($name === '' || $u_name === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบ";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง";
    } elseif (!in_array($role_sel, ['0','1','3','4'], true)) {
        $error = "สิทธิ์ผู้ใช้ไม่ถูกต้อง";
    } else {
        // ตรวจชื่อผู้ใช้ซ้ำ (ยกเว้น id ปัจจุบัน)
        $sql = "SELECT 1 FROM user_info WHERE username = ? AND id <> ? LIMIT 1";
        if ($st = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($st, "si", $u_name, $id);
            mysqli_stmt_execute($st);
            $dup = mysqli_stmt_get_result($st);
            $exists = $dup && mysqli_fetch_row($dup);
            mysqli_stmt_close($st);
            if ($exists) {
                $error = "ชื่อผู้ใช้งานมีอยู่แล้ว";
            }
        } else {
            $error = "เกิดข้อผิดพลาดภายใน (เตรียมคำสั่งตรวจสอบชื่อผู้ใช้)";
        }
    }

    if ($error === '') {
        // สร้าง SQL แบบมีเงื่อนไข ถ้าระบุรหัสผ่านใหม่จึงเซ็ต
        if ($password !== '') {
            $hash = md5($password);
            $sql = "UPDATE user_info
                       SET name = ?, username = ?, email = ?, password = ?, role = ?
                     WHERE id = ?";
            $st = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($st, "ssssii", $name, $u_name, $email, $hash, $role_sel, $id);
        } else {
            $sql = "UPDATE user_info
                       SET name = ?, username = ?, email = ?, role = ?
                     WHERE id = ?";
            $st = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($st, "sssii", $name, $u_name, $email, $role_sel, $id);
        }

        if ($st && mysqli_stmt_execute($st)) {
            mysqli_stmt_close($st);
            header("Location: all_users.php");
            exit();
        } else {
            if ($st) mysqli_stmt_close($st);
            $error = "ไม่สามารถบันทึกข้อมูลได้: " . mysqli_error($conn);
        }
    }

    // โหลดค่าล่าสุดสำหรับแสดงฟอร์มในกรณีมี error
    $record = [
        'id'       => $id,
        'name'     => $name,
        'username' => $u_name,
        'email'    => $email,
        'role'     => (int)$role_sel,
    ];
}

/* ---------------- Present ---------------- */
include __DIR__ . "/header.php";
?>
<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <?php include __DIR__ . "/sidebar.php"; ?>
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">
            <div class="page-body">
              <div class="row">
                <div class="col-sm-12">
                  <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                      <h3 class="mb-0">แก้ไขผู้ใช้งาน</h3>
                      <?php if ($error !== ''): ?>
                        <span class="text-danger small"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="card-block">
                      <h4 class="sub-title">รายละเอียดข้อมูล</h4>
                      <form action="" method="POST" autocomplete="off">
                        <input type="hidden" name="id" value="<?php echo (int)$record['id']; ?>">

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">ชื่อ</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" name="name"
                                   value="<?php echo htmlspecialchars($record['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="กรอกชื่อของคุณ">
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">ชื่อผู้ใช้งาน</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" name="username"
                                   value="<?php echo htmlspecialchars($record['username'], ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="กรอกชื่อผู้ใช้งาน">
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">อีเมล</label>
                          <div class="col-sm-10">
                            <input type="email" class="form-control" name="email"
                                   value="<?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="กรอกอีเมลของคุณ">
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">รหัสผ่าน (เว้นว่างหากไม่เปลี่ยน)</label>
                          <div class="col-sm-10">
                            <input type="password" class="form-control" name="password" placeholder="รหัสผ่านใหม่">
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">สิทธิ์ผู้ใช้</label>
                          <div class="col-sm-10">
                            <select name="select" class="form-control">
                              <option value="" disabled>เลือกสิทธิ์</option>
                              <option value="0" <?php echo ((int)$record['role']===0?'selected':''); ?>>ผู้ดูแลระบบ</option>
                              <option value="1" <?php echo ((int)$record['role']===1?'selected':''); ?>>สโมสรนักศึกษา</option>
                              <option value="3" <?php echo ((int)$record['role']===3?'selected':''); ?>>นักศึกษา</option>
                              <option value="4" <?php echo ((int)$record['role']===4?'selected':''); ?>>ผู้บริหาร (รายงาน)</option>
                            </select>
                          </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary waves-effect waves-light">
                          ยืนยัน
                        </button>
                        <a href="all_users.php" class="btn btn-outline-secondary ml-2">ยกเลิก</a>
                      </form>
                    </div>
                  </div>
                </div>
                <div id="styleSelector"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include __DIR__ . "/footer.php"; ?>
  </div>
</div>
