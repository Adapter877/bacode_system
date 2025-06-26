<?php
session_start();
$role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="TemplateMo">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    
    <?php
             
            
             if ( current(explode('.',basename($_SERVER['PHP_SELF']))) == 'index') {
 
               $active_class = "Blog by Arif";
               
             }
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'about') {
               $active_class = "About Us";
             } 
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'blog') {
               $active_class ="Blog";
             } 
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'post-details') {
               $active_class = "post-details";
             } 
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'contact') {
               $active_class = "Contact US";
             } 
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'tag') {
               $active_class = "Tag";
             } 
             elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'category') {
               $active_class = "category";
             } 
              elseif (current(explode('.',basename($_SERVER['PHP_SELF']))) == 'search') {
               $active_class = "search";
             } 
                          
             else{
 
               echo basename($_SERVER['PHP_SELF']);
               
             }
        ?>
        
        <title><?php echo $active_class; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-stand-blog.css">
    <link rel="stylesheet" href="assets/css/owl.css">

  </head>

  <body>

    <!-- ***** Preloader Start ***** -->
    <!-- <div id="preloader">
        <div class="jumper">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>   -->
    <!-- ***** Preloader End ***** -->

    <!-- Header -->
    <header class="background-header">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="about.php"><h2>ระบบค้นหารหัสกิจกรรมนักศึกษา<em>.</em></h2></a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="สลับเมนูนำทาง">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">            
<?php

if ( basename($_SERVER['PHP_SELF']) == 'index.php') {
  $active_class = "active";
}
elseif (basename($_SERVER['PHP_SELF']) == 'about.php') {
  $active_class1 = "active";
} 
elseif (basename($_SERVER['PHP_SELF']) == 'blog.php') {
  $active_class2 = "active";
} 
elseif (basename($_SERVER['PHP_SELF']) == 'contact.php') {
  $active_class4 = "active";
} 
else{
  $active_class = "";
  $active_class1 = "";
}
?>
<!-- <li class="nav-item <?php echo  $active_class; ?>">
  <a class="nav-link" href="index.php">หน้าหลัก
    <span class="sr-only">(หน้าปัจจุบัน)</span>
  </a>
</li> -->
<li class="nav-item <?php echo  $active_class1; ?>">
  <a class="nav-link" href="about.php">ค้นหากิจกรรม</a>
</li>
<li class="nav-item <?php echo  $active_class2; ?>">
  <a class="nav-link" href="blog.php">ลงทะเบียนกิจกรรม</a>
</li>
<!--   <li class="nav-item <?php echo  $active_class4; ?>">
  <a class="nav-link" href="contact.php">ติดต่อเรา</a>
</li>-->

<?php
if (!$username) {
    // ถ้ายังไม่ได้เข้าสู่ระบบ
    echo '<li class="nav-item"><a class="nav-link" href="admin/login.php">เข้าสู่ระบบ</a></li>';
} else {
    // ถ้าเข้าสู่ระบบแล้ว
    if (intval($role) === 0) {
        // เฉพาะ admin หรือ role = 0 เท่านั้นที่แสดงปุ่ม "จัดการข้อมูล"
        echo '<li class="nav-item"><a class="nav-link" href="admin/">จัดการข้อมูล</a></li>';
    }
    // แสดงออกจากระบบสำหรับทุกคนที่เข้าสู่ระบบแล้ว
    echo '<li class="nav-item"><a class="nav-link" href="log_out.php">ออกจากระบบ</a></li>';
}
?>
            </ul>
          </div>
        </div>
      </nav>
     
    </header>
    <?php include "admin/dbconfig.php";
?>
