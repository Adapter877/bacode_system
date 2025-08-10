<?php
// admin/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/dbconfig.php';

/**
 * ป้องกันการรีไดเรกต์ซ้ำ:
 * - หน้าไหนต้องการควบคุมสิทธิ์เอง (เช่น report.php) ให้ define('SKIP_SIDEBAR_REDIRECT', true);
 */
if (!defined('SKIP_SIDEBAR_REDIRECT')) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    $role = isset($_SESSION['role']) ? (int)$_SESSION['role'] : null;

    // ให้เข้าโซนอะแดมินได้สำหรับ role 0,1 และผู้บริหาร 4
    $ALLOW_IN_ADMIN = [0, 1, 4];
    if (!in_array($role, $ALLOW_IN_ADMIN, true)) {
        header('Location: ../index.php');
        exit();
    }
} else {
    // ถึงจะข้ามรีไดเรกต์ แต่ถ้าไม่มี session เลย ก็ควรบังคับล็อกอิน
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

$role = isset($_SESSION['role']) ? (int)$_SESSION['role'] : null;
$username = $_SESSION['username'] ?? '';

// ฟังก์ชันช่วยเช็ค role
function canManagePosts($role)  { return in_array((int)$role, [0,1], true); } // แอดมิน/สโมสร
function isSuperAdmin($role)    { return (int)$role === 0; }
function isExecutive($role)     { return (int)$role === 4; }

?>
<nav class="pcoded-navbar">
  <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
  <div class="pcoded-inner-navbar main-menu">

    <!-- Dashboard -->
    <div class="pcoded-navigatio-lavel" menu-title-theme="theme1">แดชบอร์ด</div>
    <ul class="pcoded-item pcoded-left-item" item-border="true" subitem-border="true">
      <li>
        <a href="index.php">
          <span class="pcoded-micon"><i class="ti-home"></i></span>
          <span class="pcoded-mtext">แดชบอร์ด</span>
          <span class="pcoded-mcaret"></span>
        </a>
      </li>
    </ul>

    <!-- Posts (เฉพาะ role 0,1) -->
    <?php if (canManagePosts($role)): ?>
      <div class="pcoded-navigatio-lavel" menu-title-theme="theme1">โพสต์ทั้งหมด</div>
      <ul class="pcoded-item pcoded-left-item" item-border="true" subitem-border="true">
        <li>
          <a href="all_posts.php">
            <span class="pcoded-micon"><i class="ti-layers"></i></span>
            <span class="pcoded-mtext">โพสต์ทั้งหมด</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
        <li>
          <a href="add_post.php">
            <span class="pcoded-micon"><i class="ti-layers"></i></span>
            <span class="pcoded-mtext">เพิ่มโพสต์</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
      </ul>
    <?php endif; ?>

    <!-- Users (เฉพาะ super admin 0) -->
    <?php if (isSuperAdmin($role)): ?>
      <div class="pcoded-navigatio-lavel" menu-title-theme="theme1">ผู้ใช้งาน</div>
      <ul class="pcoded-item pcoded-left-item" item-border="true" subitem-border="true">
        <li>
          <a href="all_users.php">
            <span class="pcoded-micon"><i class="ti-user"></i></span>
            <span class="pcoded-mtext">ผู้ใช้งานทั้งหมด</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
        <li>
          <a href="add_user.php">
            <span class="pcoded-micon"><i class="ti-plus"></i></span>
            <span class="pcoded-mtext">เพิ่มผู้ใช้งาน</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
      </ul>
    <?php endif; ?>

    <!-- Barcode (0,1) -->
    <?php if (canManagePosts($role)): ?>
      <div class="pcoded-navigatio-lavel" menu-title-theme="theme1">บาร์โค้ด</div>
      <ul class="pcoded-item pcoded-left-item" item-border="true" subitem-border="true">
        <li>
          <a href="all_bacode.php">
            <span class="pcoded-micon"><i class="ti-barcode"></i></span>
            <span class="pcoded-mtext">บาร์โค้ดทั้งหมด</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
        <li>
          <a href="add_bacode.php">
            <span class="pcoded-micon"><i class="ti-upload"></i></span>
            <span class="pcoded-mtext">เพิ่มบาร์โค้ดด้วย (PDF)</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
        <li>
          <a href="from_bacode.php">
            <span class="pcoded-micon"><i class="ti-plus"></i></span>
            <span class="pcoded-mtext">เพิ่มบาร์โค้ด</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
      </ul>
    <?php endif; ?>

    <!-- Executive Report (เฉพาะ role 4) -->
    <?php if (isExecutive($role)): ?>
      <div class="pcoded-navigatio-lavel" menu-title-theme="theme1">รายงานผู้บริหาร</div>
      <ul class="pcoded-item pcoded-left-item" item-border="true" subitem-border="true">
        <li>
          <a href="/report.php">
            <span class="pcoded-micon"><i class="ti-pie-chart"></i></span>
            <span class="pcoded-mtext">Executive Report</span>
            <span class="pcoded-mcaret"></span>
          </a>
        </li>
      </ul>
    <?php endif; ?>

  </div>
</nav>
