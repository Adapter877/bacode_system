<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">

        <!-- Start Dashboard -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">Dashboard</div>
        <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true">
            <li>
                <a href="index.php">
                    <span class="pcoded-micon"><i class="ti-home"></i><b></b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">Dashboard</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        <!-- Start Posts -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">Posts</div>
        <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true">
            <li>
                <a href="all_posts.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">All Posts</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li>
                <a href="add_post.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">Add Posts</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        <?php
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // เชื่อมต่อกับฐานข้อมูลและตรวจสอบ role
            $sql = "SELECT role FROM user_info WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $role);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // ถ้า role = 3 ให้ทำการ redirect ไปหน้า index.php
            if ($role == 3) {
                header("Location: ../index.php");
                exit();
            }
        } else {
            // ถ้าไม่พบ user_id ใน session ให้ redirect ไปหน้า login
            header("Location: login.php");
            exit();
        }
        ?>

        <!-- Start Categories -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">Categories</div>
        <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true">
            <li>
                <a href="all_categories.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">All Categories</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li>
                <a href="add_categories.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">Add Categories</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        <!-- Start Tags -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">Tags</div>
        <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true">
            <li>
                <a href="all_tags.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">All Tags</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li>
                <a href="add_tags.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">Add Tags</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        <!-- Start Users -->
        <div class="pcoded-navigatio-lavel" data-i18n="nav.category.forms" menu-title-theme="theme1">Users</div>
        <ul class="pcoded-item pcoded-left-item" item-border="true" item-border-style="none" subitem-border="true">
            <li>
                <a href="all_users.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">All Users</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li>
                <a href="add_user.php">
                    <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                    <span class="pcoded-mtext" data-i18n="nav.form-components.main">Add User</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

    </div>
</nav>
