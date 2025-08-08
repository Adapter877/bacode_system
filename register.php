<?php
include "admin/dbconfig.php";
session_start();
$error = "";

// เมื่อกด submit
if (isset($_POST['submit'])) {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $confirm    = trim($_POST['confirm'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $major      = trim($_POST['major'] ?? '');

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

        if ($result && mysqli_num_rows($result) > 0) {
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
                header('Location: login.php');
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
    <title>สมัครสมาชิก</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Style -->
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body style="background-color: #1f2937; background-size: cover; background-position: center; background-attachment: fixed;" class="min-h-screen flex items-center justify-center text-white">
<center>
<section class="w-full px-4" style="max-width: 720px;"><!-- ขยายกว้างขึ้น -->
    <div class="bg-white/20 backdrop-blur-xl border border-white/30 rounded-2xl shadow-2xl p-10 ring-1 ring-white/10">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <?php if (!empty($error)) : ?>
                <div class="mb-4 text-center text-red-300 font-semibold drop-shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>

            <img src="https://www.uru.ac.th/images/logouruWfooter.png" alt="โลโก้ URU" class="mx-auto mb-6 w-24 h-auto drop-shadow-xl backdrop-blur-sm">

            <div class="text-center mb-6">
                <h3 class="text-3xl font-bold drop-shadow-lg text-white">สมัครสมาชิก</h3>
                <p class="text-white/80 text-sm mt-1">กรอกข้อมูลให้ครบถ้วนเพื่อสร้างบัญชีของคุณ</p>
            </div>
            <hr class="mb-6 border-white/20" />

            <!-- จัดสองคอลัมน์บนจอกว้าง แต่ยังคงโทนเดียวกับ login -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="name"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
                        placeholder="ชื่อ-นามสกุล" required>
                </div>

                <div>
                    <input type="text" name="student_id"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
                        placeholder="รหัสนักศึกษา" required>
                </div>

                <div>
                    <input type="text" name="major"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
                        placeholder="สาขาวิชา" required>
                </div>

                <div>
                    <input type="email" name="email"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
                        placeholder="อีเมล" required>
                </div>

                <div>
                    <input type="text" name="username"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner"
                        placeholder="ชื่อผู้ใช้งาน" required>
                </div>

                <div>
                    <input type="password" name="password" minlength="8"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-inner"
                        placeholder="รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)" required>
                </div>

                <div>
                    <input type="password" name="confirm" minlength="8"
                        class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30
                               focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-inner"
                        placeholder="ยืนยันรหัสผ่าน" required>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                <button type="submit" name="submit"
                    class="w-full py-3 bg-white/20 text-white font-semibold rounded-lg shadow-md
                           hover:bg-white/30 transition duration-300 border border-white/30 hover:shadow-xl
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    สมัครสมาชิก
                </button>

                <a href="login.php"
                    class="w-full text-center py-3 bg-white/10 text-white font-semibold rounded-lg shadow-md
                           hover:bg-white/20 transition duration-300 border border-white/30 hover:shadow-xl
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    ไปยังหน้าล็อกอิน
                </a>
            </div>
        </form>
    </div>
</section>
</center>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
