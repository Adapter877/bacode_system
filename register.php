<?php 
include "admin/dbconfig.php";
session_start();
$error = "";
$success = "";

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if (isset($_POST['submit'])) {
    // ใช้ isset() เพื่อให้มั่นใจว่า POST คีย์มีค่า
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm = isset($_POST['confirm']) ? trim($_POST['confirm']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    // ตรวจสอบว่า password และ confirm password ตรงกันหรือไม่
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // เข้ารหัสรหัสผ่าน
        $pass = md5($password);

        // ตรวจสอบว่า username ซ้ำหรือไม่
        $check_sql = "SELECT * FROM user_info WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = "Username already exists.";
        } else {
            // เพิ่มข้อมูลผู้ใช้ใหม่ในฐานข้อมูล
            $created_at = date('Y-m-d H:i:s');  // บันทึกวันที่และเวลา
            $role = 3;  // กำหนด role เป็น 3 ตามที่ต้องการ

            $insert_sql = "INSERT INTO user_info (username, password, email, name, role, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $pass, $email, $name, $role, $created_at);

            if (mysqli_stmt_execute($stmt)) {
                // เมื่อการสมัครสำเร็จให้รีไดเร็กต์ไปยังหน้า index.php
                $success = "Registration successful! You can now log in.";
                header('Location: ./index.php');
                exit();
            } else {
                $error = "Error occurred: " . mysqli_error($conn);  // แสดงข้อผิดพลาดหากมี
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sign Up</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    
    <style>
    /* Glassmorphism style with background image */
    html, body {
        height: 100%;
        margin: 0;
        font-family: 'Arial', sans-serif;
    }

    body {
        background-color: #1f2937;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .input-group input::placeholder {
    color: rgba(255, 255, 255, 0.9); /* สีขาวชัด */
    opacity: 1; /* ค่า default บางครั้งอาจเบลอ ให้แน่ใจว่าแสดงเต็ม */
}

    .signup-card {
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.15) 0%,
            rgba(255, 255, 255, 0.05) 100%
        );
        border-radius: 20px;
        padding: 40px;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.2);
        width: 100%;
        max-width: 400px;
        transition: all 0.3s ease;
    }

    .signup-card:hover {
        box-shadow: 0 10px 40px rgba(255, 255, 255, 0.2);
        transform: scale(1.01);
    }

    .input-group {
        margin-bottom: 20px;
    }

    .input-group input {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 16px;
        width: 100%;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }

    .input-group input:focus {
        border-color: #ffffff;
        background: rgba(255, 255, 255, 0.3);
        outline: none;
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.4);
    }

    h3 {
        color: white;
        text-align: center;
    }

    button {
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 12px;
        width: 100%;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover {
        background-color: rgba(255, 255, 255, 0.3);
        transform: scale(1.02);
    }

</style>

</head>
<body>
    
</center>
    <section class="signup">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="signup-card card-block auth-body mr-auto ml-auto">
                        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST" class="md-float-material">
                            <h3 class="text-center"><?php echo $error; ?></h3>
                            <h3 class="text-center"><?php echo $success; ?></h3>
                            <div>           <center>
                            <img src="https://www.uru.ac.th/images/logouruWfooter.png" alt="โลโก้ URU" class="mx-auto mb-4" style="width: 120px; max-width: 100%; height: auto; filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3)); backdrop-filter: blur(4px),">
                            </center></div>
                            <div class="auth-box">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-left txt-primary text-white">สมัครสมาชิก</h3>
                                    </div>
                                </div>
                                <hr />

                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="ชื่อผู้ใช้งาน" name="username" required>
                                </div>

                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="รหัสผ่าน" name="password" required>
                                </div>

                                <div class="input-group">
                                    <input type="password" class="form-control" placeholder="ยืนยันรหัสผ่าน" name="confirm" required>
                                </div>

                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="อีเมล" name="email" required>
                                </div>

                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="ชื่อ-นามสกุล" name="name" required>
                                </div>

                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" name="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                            สมัครสมาชิก
                                        </button>
                                        <button type="button" onclick="window.location.href='login.php';" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20" style="margin-top: 5px;">
    ไปยังหน้าเข้าสู่ระบบ
</button>


                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
    </center>
</body>


</html>
