<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">

        <!-- Start Dashboard -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">แดชบอร์ด</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="index.php">
                    <span class="pcoded-micon"><i class="ti-home"></i></span>
                    <span class="pcoded-mtext">แดชบอร์ด</span>
                </a>
            </li>
        </ul>

        <!-- Start Posts -->
        <div class="pcoded-navigatio-lavel">โพสต์ทั้งหมด</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="all_posts.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">โพสต์ทั้งหมด</span>
                </a>
            </li>
            <li>
                <a href="add_post.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">เพิ่มโพสต์</span>
                </a>
            </li>
        </ul>

        <?php
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT role FROM user_info WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $role);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($role == 3) {
                header("Location: ../index.php");
                exit();
            }
        } else {
            header("Location: login.php");
            exit();
        }
        ?>

        <!-- Start Users -->
        <div class="pcoded-navigatio-lavel">ผู้ใช้งาน</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="all_users.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">ผู้ใช้งานทั้งหมด</span>
                </a>
            </li>
            <li>
                <a href="add_user.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">เพิ่มผู้ใช้งาน</span>
                </a>
            </li>
        </ul>

        <!-- Start Barcodes -->
        <div class="pcoded-navigatio-lavel">บาร์โค้ด</div>
        <ul class="pcoded-item pcoded-left-item">
            <li>
                <a href="all_bacode.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">บาร์โค้ดทั้งหมด</span>
                </a>
            </li>
            <li>
                <a href="add_bacode.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">เพิ่มบาร์โค้ดด้วย (PDF)</span>
                </a>
            </li>
            <li>
                <a href="from_bacode.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i></span>
                    <span class="pcoded-mtext">เพิ่มบาร์โค้ด</span>
                </a>
            </li>
        </ul>

    </div>
</nav>
