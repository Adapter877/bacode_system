<?php
include "admin/dbconfig.php";
session_start();
$error = "";
$success = "";

// เมื่อกด submit
if (isset($_POST['submit'])) {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $confirm    = trim($_POST['confirm'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $major      = trim($_POST['major'] ?? '');

    // Validate เบื้องต้น
    if (strlen($password) < 8) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร";
    } elseif ($password !== $confirm) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง";
    } elseif ($username === '' || $student_id === '' || $major === '' || $name === '') {
        $error = "กรุณากรอกข้อมูลให้ครบ";
    } else {
        // ตรวจ username ซ้ำ
        $check_sql = "SELECT 1 FROM user_info WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = "ชื่อผู้ใช้งานนี้ถูกใช้แล้ว";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 3;
            $created_at = date('Y-m-d H:i:s');

            $insert_sql = "INSERT INTO user_info (username, password, email, name, role, created_at, student_id, major)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ssssisss", $username, $hash, $email, $name, $role, $created_at, $student_id, $major);

            if (mysqli_stmt_execute($stmt)) {
                header('Location: ./index.php');
                exit();
            } else {
                $error = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom Style -->
    <link rel="stylesheet" type="text/css" href="style.css">

    <style>
        .input-group { margin-bottom: 12px; }
        .input-group input {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .input-group input::placeholder { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body style="background-color:#1f2937;">
<section class="w-full min-h-screen flex items-center justify-center text-white">
  <div class="w-full max-w-md px-6">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 shadow-xl">
      <h2 class="text-center text-lg font-semibold tracking-wider mb-5">สมัครสมาชิก</h2>

      <?php if (!empty($error)): ?>
        <div class="mb-4 px-3 py-2 rounded bg-red-500/20 border border-red-500/40 text-sm">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="space-y-3">
          <div class="input-group">
            <input type="text" class="form-control" name="name" placeholder="ชื่อ-นามสกุล" required>
          </div>
          <div class="input-group">
            <input type="text" class="form-control" name="student_id" placeholder="รหัสนักศึกษา" required>
          </div>
          <div class="input-group">
            <input type="text" class="form-control" name="major" placeholder="สาขาวิชา" required>
          </div>
          <div class="input-group">
            <input type="email" class="form-control" name="email" placeholder="อีเมล" required>
          </div>
          <div class="input-group">
            <input type="text" class="form-control" name="username" placeholder="ชื่อผู้ใช้งาน" required>
          </div>
          <div class="input-group">
            <input type="password" class="form-control" name="password" placeholder="รหัสผ่าน 8 ตัวขึ้นไป" minlength="8" required>
          </div>
          <div class="input-group">
            <input type="password" class="form-control" name="confirm" placeholder="ยืนยันรหัสผ่าน" minlength="8" required>
          </div>
        </div>

        <div class="mt-5">
          <button type="submit" name="submit" class="w-full py-2 rounded-lg bg-white/20 hover:bg-white/30 border border-white/30 transition">
            สมัครสมาชิก
          </button>
        </div>

        <div class="mt-3">
          <a href="login.php" class="block w-full text-center py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 transition">
            ไปยังหน้าล็อกอิน
          </a>
        </div>
      </form>
    </div>
  </div>
</section>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
