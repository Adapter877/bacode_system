<?php
include "admin/dbconfig.php";
session_start();
$error = "";

/* รายชื่อสาขา SCI URU (ภาษาไทย) */
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

/* เก็บค่าที่ผู้ใช้กรอกไว้เผื่อเกิด error จะได้ไม่ต้องพิมพ์ใหม่ */
$username   = trim($_POST['username']    ?? '');
$email      = trim($_POST['email']       ?? '');
$name       = trim($_POST['name']        ?? '');
$student_id = trim($_POST['student_id']  ?? '');
$major      = trim($_POST['major']       ?? ''); // จะรับจาก select
$password   = trim($_POST['password']    ?? '');
$confirm    = trim($_POST['confirm']     ?? '');

/* เมื่อกด submit */
if (isset($_POST['submit'])) {
    if ($password !== $confirm) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง";
    } elseif ($username === '' || $student_id === '' || $major === '' || $name === '') {
        $error = "กรุณากรอกข้อมูลให้ครบ";
    } elseif (!in_array($major, $majors_all, true)) {
        $error = "กรุณาเลือกสาขาให้ถูกต้องตามรายการที่กำหนด";
    } else {
        // เช็กความยาวรหัสผ่านอย่างน้อย 8 ตัวอักษร
        if (strlen($password) < 8) {
            $error = "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร";
        } else {
            // ใช้ MD5 ตามที่ร้องขอ (หมายเหตุ: ไม่ปลอดภัยสำหรับโปรดักชัน)
            $pass = md5($password);
            $role = 3; // นักศึกษา
            $created_at = date('Y-m-d H:i:s');

            // ตรวจ username ซ้ำ
            $check_sql = "SELECT 1 FROM user_info WHERE username = ?";
            $stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $error = "ชื่อผู้ใช้งานนี้ถูกใช้แล้ว";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);

                $insert_sql = "INSERT INTO user_info (username, password, email, name, role, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($stmt, "ssssis", $username, $pass, $email, $name, $role, $created_at);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: login.php');
                    exit();
                } else {
                    $error = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <title>สมัครสมาชิก</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Style -->
    <!-- Custom Style (inline) -->
    <style>
    select,
    .choices__inner,
    .choices__list--dropdown,
    .choices__list[aria-expanded] {
      background-color: #64748b !important;
      color: #fff !important;
      border-radius: 0.5rem !important;
      border: 1px solid #64748b !important;
    }
    .choices__item--selectable.is-highlighted,
    .choices__item--selectable:hover {
      background-color: #374151 !important;
      color: #fff !important;
    }
    .choices__placeholder {
      color: #94a3b8 !important;
    }
    </style>
</head>
<body style="background-color: #1f2937; background-size: cover; background-position: center; background-attachment: fixed;" class="min-h-screen flex items-center justify-center text-white">
<center>
<section class="w-full px-4" style="max-width: 720px;">
    <div class="bg-white/20 backdrop-blur-xl border border-white/30 rounded-2xl shadow-2xl p-10 ring-1 ring-white/10">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <?php if (!empty($error)) : ?>
                <div class="mb-4 text-center text-red-300 font-semibold drop-shadow-sm"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <img src="https://www.uru.ac.th/images/logouruWfooter.png" alt="โลโก้ URU" class="mx-auto mb-6 w-24 h-auto drop-shadow-xl backdrop-blur-sm">

            <div class="text-center mb-6">
                <h3 class="text-3xl font-bold drop-shadow-lg text-white">สมัครสมาชิก</h3>
                <p class="text-white/80 text-sm mt-1">กรอกข้อมูลให้ครบถ้วนเพื่อสร้างบัญชีของคุณ</p>
            </div>
            <hr class="mb-6 border-white/20" />

            <!-- ฟอร์ม -->
            <!-- ฟอร์ม -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="md:col-span-2">
    <input type="text" name="name"
      value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
      placeholder="ชื่อ-นามสกุล" required>
  </div>

  <div>
    <input type="text" name="student_id"
      value="<?= htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8'); ?>"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
      placeholder="รหัสนักศึกษา" required>
  </div>

  <!-- สาขา: select ให้สูงเท่า input + ไอคอนลูกศร -->
  <div class="relative">
    <select name="major"
      class="h-12 w-full px-4 pr-10 bg-white/10 text-white rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner appearance-none"
      required>
      <option value="" class="text-gray-800">เลือกสาขาวิชา</option>
      <?php foreach ($majors_all as $m): ?>
        <option value="<?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?>"
          <?= ($major === $m) ? 'selected' : ''; ?>>
          <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <!-- ไอคอนลูกศร -->
    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white/80"
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 9l-7 7-7-7"/>
    </svg>
  </div>

  <div>
    <input type="email" name="email"
      value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
      placeholder="อีเมล" required>
  </div>

  <div>
    <input type="text" name="username"
      value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
      placeholder="ชื่อผู้ใช้งาน" required>
  </div>

  <div>
    <input type="password" name="password" minlength="8"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-inner"
      placeholder="รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)" required>
  </div>

  <div>
    <input type="password" name="confirm" minlength="8"
      class="h-12 w-full px-4 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
             focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-inner"
      placeholder="ยืนยันรหัสผ่าน" required>
  </div>
</div>

<!-- ปุ่ม ให้สูงเท่ากันเช่นกัน -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-3">
  <button type="submit" name="submit"
    class="h-12 w-full flex items-center justify-center bg-white/20 text-white font-semibold rounded-lg shadow-md
           hover:bg-white/30 transition duration-300 border border-white/30 hover:shadow-xl
           focus:outline-none focus:ring-2 focus:ring-blue-500">
    สมัครสมาชิก
  </button>

  <a href="login.php"
     class="h-12 w-full flex items-center justify-center bg-white/10 text-white font-semibold rounded-lg shadow-md
            hover:bg-white/20 transition duration-300 border border-white/30 hover:shadow-xl
            focus:outline-none focus:ring-2 focus:ring-blue-500">
    ไปยังหน้าล็อกอิน
  </a>
</div>

</section>
</center>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
