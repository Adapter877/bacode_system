<?php 
session_start();
include "admin/dbconfig.php"; 

// ตรวจสอบว่า SESSION มีข้อมูล user_id หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // ถ้ายังไม่ล็อกอินให้เปลี่ยนไปที่หน้า login
    exit();
}

// ดึงข้อมูลจากฐานข้อมูลโดยใช้ user_id จาก SESSION
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT name, username FROM user_info WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
if ($stmt_user === false) {
    die("ERROR: ไม่สามารถเตรียมคำสั่ง SQL ได้: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
mysqli_stmt_bind_result($stmt_user, $name, $username);
mysqli_stmt_fetch($stmt_user);
mysqli_stmt_close($stmt_user);

// หากมีการค้นหาชื่อกิจกรรมจากฟอร์ม
if (isset($_GET['search_name'])) {
    $search_name = $_GET['search_name'];

    // คำสั่ง SQL สำหรับค้นหากิจกรรมที่เกี่ยวข้องกับชื่อ
    $sql_search = "SELECT * FROM student_activities WHERE student_name LIKE ?";
    $stmt_search = mysqli_prepare($conn, $sql_search);
    if ($stmt_search === false) {
        die("ERROR: ไม่สามารถเตรียมคำสั่ง SQL ได้: " . mysqli_error($conn));
    }

    $search_term = "%" . $search_name . "%";
    mysqli_stmt_bind_param($stmt_search, "s", $search_term);
    mysqli_stmt_execute($stmt_search);
    $result_search = mysqli_stmt_get_result($stmt_search);
} else {
    $result_search = null; // ไม่มีการค้นหา
}
?>

<?php include "header.php"; ?>

<!-- Page Content -->
<div class="heading-page header-text">
  <section class="page-heading">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="text-content">
            <h4>Student Activities</h4>
            <h2>Search Activity by Student Name</h2>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<section class="about-us">
  <div class="container">

  <div class="row mb-5">
  <div class="col-lg-12">
    <form action="" method="GET">
      <div class="form-group">
        <label for="student_name"><strong>กรอกชื่อเพื่อค้นหากิจกรรม:</strong></label>
        <!-- ช่องกรอกชื่อแบบ readonly -->
        <input type="text" class="form-control" id="student_name" name="search_name" placeholder="กรอกชื่อเพื่อค้นหา" value="<?php echo htmlspecialchars($name); ?>" readonly>
      </div>
      <button type="submit" class="btn btn-primary mt-2">ค้นหา</button>
    </form>
  </div>
</div>


    <?php if ($result_search && mysqli_num_rows($result_search) > 0): ?>
      <div class="row">
        <div class="col-lg-12">
          <h4>ผลการค้นหา (ชื่อ: <span class="text-primary"><?php echo htmlspecialchars($search_name); ?></span>)</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="thead-dark">
                <tr>
                  <th>วันที่เข้าร่วม</th>
                  <th>ชื่อ - สกุล</th>
                  <th>รหัสนักศึกษา</th>
                  <th>หลักสูตร</th>
                  <th>บาโค้ด</th>
                  <th>ติดต่อ</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_search)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['date_joined']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['major']); ?></td>
                    <td><?php echo htmlspecialchars($row['barcode']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php elseif ($result_search && mysqli_num_rows($result_search) == 0): ?>
      <p>ไม่พบข้อมูลที่ตรงกับคำค้นหา</p>
    <?php endif; ?>

    <!-- แสดงข้อมูลของผู้ใช้ -->
    <div class="user-info">
      <h3>ข้อมูลผู้ใช้</h3>
      <p>ชื่อ: <?php echo htmlspecialchars($name); ?></p>
      <p>รหัสนักศึกษา: <?php echo htmlspecialchars($username); ?></p>
    </div>

  </div>
</section>

<?php include "footer.php"; ?>

<?php
mysqli_close($conn);
?>
