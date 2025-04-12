<?php include "header.php"; 
if (!$_SESSION['role'] == 0){
    echo "<script>window.location.href='index.php'</script>";
}

$sql = "SELECT * FROM student_activities ORDER BY date_joined DESC";
$query = mysqli_query($conn, $sql);
?>

<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <?php include "sidebar.php"; ?>
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">

                        <div class="page-body">
                            <div class="card">
                                <div class="card-header">
                                    <h3>กิจกรรมของนักศึกษาทั้งหมด</h3>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>ชื่อ - สกุล</th>
                                                    <th>รหัสนักศึกษา</th>
                                                    <th>หลักสูตร</th>
                                                    <th>ชื่อกิจกรรม</th>
                                                    <th>ชั่วโมงกิจกรรมที่ได้รับ</th>
                                                    <th>รหัสกิจกรรม</th>
                                                    <th>ติดต่อ</th>
                                                    <th>วันที่เข้าร่วม</th>
                                                    <th>เวลาที่เพิ่มข้อมูล</th>
                                                    <th>ลบ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $i = 1;
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $i++; ?></td>
                                                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['major']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['activity_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['activity_hours']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['barcode']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                                        <td><?php echo date("d-M-Y", strtotime($row['date_joined'])); ?></td>
                                                        <td><?php echo date("d-M-Y H:i", strtotime($row['created_at'])); ?></td>
                                                        <td>
                                                            <a href="delete_activity.php?id=<?php echo $row['id']; ?>" onclick="return confirm('คุณแน่ใจว่าต้องการลบกิจกรรมนี้?')">
                                                                <button class="btn btn-danger btn-sm"><i class="ti-trash"></i></button>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                } 
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="styleSelector"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
