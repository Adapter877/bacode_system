<?php
include "header.php";

$error = [];
$msg = "";

// เช็คว่ามีการ submit form หรือยัง
if (isset($_POST['submit'])) {
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $major = mysqli_real_escape_string($conn, $_POST['major']);
    $activity_name = mysqli_real_escape_string($conn, $_POST['activity_name']);
    $activity_hours = mysqli_real_escape_string($conn, $_POST['activity_hours']);
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $date_joined = mysqli_real_escape_string($conn, $_POST['date_joined']);

    // ตรวจสอบ student_id และ activity_name ซ้ำ
    $check_sql = "SELECT activity_name FROM student_activities WHERE student_id = '$student_id'";
    $check_query = mysqli_query($conn, $check_sql);

    $duplicate = false;
    while ($row = mysqli_fetch_assoc($check_query)) {
        if ($row['activity_name'] === $activity_name) {
            $duplicate = true;
            break;
        }
    }

    if ($duplicate) {
        $msg = '<div class="alert alert-danger">รหัสนักศึกษาและชื่อกิจกรรมนี้มีอยู่ในระบบแล้ว</div>';
    } else {
        // Insert เข้าฐานข้อมูล
        $sql = "INSERT INTO student_activities (
                    student_name, student_id, major,
                    activity_name, activity_hours, barcode,
                    contact, date_joined
                ) VALUES (
                    '$student_name', '$student_id', '$major',
                    '$activity_name', '$activity_hours', '$barcode',
                    '$contact', '$date_joined'
                )";

        $query = mysqli_query($conn, $sql);

        if ($query) {
            $msg = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้ว</div>';
        } else {
            $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการบันทึกข้อมูล</div>';
        }
    }
}
?>

<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <?php include "sidebar.php"; ?>
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">
            <div class="page-body">
              <div class="row justify-content-center">
                <div class="col-md-8">
                  <div class="card">
                    <div class="card-header">
                      <h3>เพิ่มข้อมูลกิจกรรมนักศึกษา</h3>
                      <?php if (!empty($msg)) echo $msg; ?>
                    </div>
                    <div class="card-block">
                      <form method="POST" action="">
                        <div class="form-group">
                          <label>ชื่อ - สกุล</label>
                          <input type="text" name="student_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>รหัสนักศึกษา</label>
                          <input type="text" name="student_id" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>หลักสูตร</label>
                          <input type="text" name="major" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>ชื่อกิจกรรม</label>
                          <input type="text" name="activity_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>ชั่วโมงกิจกรรมที่ได้รับ</label>
                          <input type="number" step="0.1" name="activity_hours" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>รหัสบาร์โค้ด</label>
                          <input type="text" name="barcode" class="form-control">
                        </div>
                        <div class="form-group">
                          <label>ข้อมูลติดต่อ</label>
                          <input type="text" name="contact" class="form-control">
                        </div>
                        <div class="form-group">
                          <label>วันที่เข้าร่วม</label>
                          <input type="date" name="date_joined" class="form-control" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary mt-3">บันทึกข้อมูล</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div> <!-- row -->
            </div> <!-- page-body -->
          </div> <!-- page-wrapper -->
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>
