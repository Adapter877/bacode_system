<?php
session_start();

// ดึงจาก Session
$role_raw = $_SESSION['role'] ?? null;
$role_str = isset($role_raw) ? trim((string)$role_raw) : '';
$isAdmin  = ($role_str === '0' || (is_int($role_raw) && $role_raw === 0)); // เผื่อบางระบบเก็บเป็น int 0
$username = $_SESSION['username'] ?? null;

function make_initials($str) {
    $s = trim($str ?? '');
    if ($s === '') return 'U';
    $parts = preg_split('/\s+/', $s);
    if (count($parts) >= 2) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }
    return mb_strtoupper(mb_substr($s, 0, 2));
}
$display_name = $username ?: 'User';
$initials     = make_initials($display_name);

// ตั้ง title ตามไฟล์
$page = current(explode('.', basename($_SERVER['PHP_SELF'])));
if     ($page == 'index')        $active_title = "Blog by Arif";
elseif ($page == 'about')        $active_title = "About Us";
elseif ($page == 'blog')         $active_title = "Blog";
elseif ($page == 'post-details') $active_title = "post-details";
elseif ($page == 'contact')      $active_title = "Contact US";
elseif ($page == 'tag')          $active_title = "Tag";
elseif ($page == 'category')     $active_title = "category";
elseif ($page == 'search')       $active_title = "search";
else                             $active_title = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
  <head>
    <meta charset="utf-8">
    <title><?php echo $active_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-stand-blog.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <style>
      .background-header .navbar { min-height: 64px; }
      .background-header .navbar .container { display:flex; align-items:center; }
      .background-header .navbar-brand { display:flex; align-items:center; margin:0; }
      .background-header .navbar-brand h2{ margin:0; line-height:1; font-size:24px; }
      .navbar-nav .nav-item { display:flex; align-items:center; }
      .navbar-nav .nav-link{ display:flex; align-items:center; height:48px; padding:0 14px; line-height:1; }
      .profile-avatar{ width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; background:#111827; color:#fff; border:2px solid rgba(0,0,0,.08); }
      .navbar .dropdown-menu.profile-menu{ width:320px !important; max-width:90vw; padding:.75rem .75rem; box-sizing:border-box; }
      .dropdown-profile-header{ display:flex; align-items:center; gap:.5rem; margin-bottom:.5rem; }
      .dropdown-profile-header .profile-avatar{ width:40px; height:40px; }
      .dropdown-profile-header .name{ font-weight:700; font-size:1rem; }
      .profile-grid{ display:grid; grid-template-columns:110px 1fr; row-gap:.35rem; column-gap:.5rem; font-size:.92rem; }
      .profile-grid .label{ color:#6b7280; }
      .profile-grid .value{ color:#111827; word-break:break-word; }
      .dropdown-menu.profile-menu .dropdown-item{ padding:.5rem .6rem; border-radius:.375rem; }
      .dropdown-menu.profile-menu .dropdown-divider{ margin:.5rem 0; }
    </style>
  </head>

  <body>
    <header class="background-header">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="about.php"><h2>ระบบค้นหารหัสกิจกรรมนักศึกษา<em>.</em></h2></a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                  aria-controls="navbarResponsive" aria-expanded="false" aria-label="สลับเมนูนำทาง">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
<?php
$active_about = (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '';
$active_blog  = (basename($_SERVER['PHP_SELF']) == 'blog.php')  ? 'active' : '';
?>
              <li class="nav-item <?php echo $active_about; ?>"><a class="nav-link" href="about.php">ค้นหากิจกรรม</a></li>
              <li class="nav-item <?php echo $active_blog; ?>"><a class="nav-link" href="blog.php">ลงทะเบียนกิจกรรม</a></li>

<?php if (!$username): ?>
              <li class="nav-item"><a class="nav-link" href="admin/login.php">เข้าสู่ระบบ</a></li>
<?php else: ?>
  <?php if ($isAdmin): ?>
              <li class="nav-item"><a class="nav-link" href="admin/">จัดการข้อมูล</a></li>
  <?php endif; ?>

              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown"
                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="profile-avatar mr-2"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="d-none d-sm-inline"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-menu" aria-labelledby="profileDropdown">
                  <div class="dropdown-profile-header">
                    <span class="profile-avatar"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="name"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                  <div class="profile-grid mb-2">
                    <div class="label">ชื่อ-นามสกุล:</div>
                    <div class="value"><?php echo htmlspecialchars($_SESSION['full_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="label">รหัสนักศึกษา:</div>
                    <div class="value"><?php echo htmlspecialchars($_SESSION['student_id'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="label">สาขาวิชา:</div>
                    <div class="value"><?php echo htmlspecialchars($_SESSION['major'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="label">อีเมล:</div>
                    <div class="value"><?php echo htmlspecialchars($_SESSION['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="about.php">ตารางบันทึกกิจกรรม</a>
                  <a class="dropdown-item" href="#!">ดูชั่วโมงทั้งหมด</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="log_out.php">ออกจากระบบ</a>
                </div>
              </li>
<?php endif; ?>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <?php include "admin/dbconfig.php"; ?>

    <script src="assets/js/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
