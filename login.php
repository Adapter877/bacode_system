<?php
include "admin/dbconfig.php";
session_start();
$error = "";

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if (isset($_POST['submit'])) {
    // รับค่าจากฟอร์ม
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // เข้ารหัสรหัสผ่าน
    $pass = md5($password);

    // ตรวจสอบข้อมูลผู้ใช้จากฐานข้อมูล
    $check_sql = "SELECT * FROM user_info WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $pass);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีข้อมูลตรงกันให้เริ่ม session
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];  // เก็บ id ของผู้ใช้ใน session
        $_SESSION['username'] = $user['username'];  // เก็บชื่อผู้ใช้ใน session

        header('Location: index.php');  // ไปที่หน้า index.php หลังจากเข้าสู่ระบบสำเร็จ
        exit();
    } else {
        $error = "Invalid username or password.";
    }

    mysqli_stmt_close($stmt);
}
?>
<!-- ส่วนของฟอร์มล็อกอิน -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Style -->
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body style="background-image: url('https://v3.tailwindcss.com/_next/static/media/blog-post-form-dark@90.5b274bea.jpg'); background-size: cover; background-position: center; background-attachment: fixed;" class="min-h-screen flex items-center justify-center text-white">
<center>
<section class="login w-full max-w-lg px-4">
    <div class="login-card bg-white/20 backdrop-blur-xl border border-white/30 rounded-2xl shadow-2xl p-10 ring-1 ring-white/10">
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
            <?php if (!empty($error)) : ?>
                <div class="mb-4 text-center text-red-300 font-semibold drop-shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>
            <img src="https://www.uru.ac.th/images/logouruWfooter.png" alt="โลโก้ URU" class="mx-auto mb-6 w-24 h-auto drop-shadow-xl backdrop-blur-sm">

            <div class="text-center mb-6">
            <h3 class="text-3xl font-bold drop-shadow-lg text-white">ระบบค้นหารหัสกิจกรรม</h3>
            </div>
            <hr class="mb-6 border-white/20" />

            <div class="mb-4">
                <input type="text" class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30 focus:outline-none focus:ring-2 focus:ring-blue-400 shadow-inner" placeholder="Username" name="username" required>
            </div>

            <div class="mb-6">
                <input type="password" class="w-full px-4 py-3 bg-white/10 text-white placeholder-white/70 rounded-lg border border-white/30 focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-inner" placeholder="Password" name="password" required>
            </div>

            <button type="submit" name="submit" class="w-full py-3 bg-white/20 text-white font-semibold rounded-lg shadow-md hover:bg-white/30 transition duration-300 border border-white/30 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>

            <a href="register.php" class="block w-full text-center mt-4 py-3 bg-white/10 text-white font-semibold rounded-lg shadow-md hover:bg-white/20 transition duration-300 border border-white/30 hover:shadow-xl">
                Register
            </a>
        </form>
    </div>
</section>
</center>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

