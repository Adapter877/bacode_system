<?php include "header.php";


$total_users = mysqli_num_rows( mysqli_query($conn,"SELECT * FROM user_info"));

$total_categories = mysqli_num_rows( mysqli_query($conn,"SELECT * FROM categories"));

$total_posts = mysqli_num_rows( mysqli_query($conn,"SELECT * FROM posts"));

$total_tags = mysqli_num_rows( mysqli_query($conn,"SELECT * FROM tags"));



// นับจำนวนผู้ใช้งานแต่ละประเภท
$sql_admin = "SELECT COUNT(*) AS total FROM user_info WHERE role = 0";
$sql_editor = "SELECT COUNT(*) AS total FROM user_info WHERE role = 1";
$sql_student = "SELECT COUNT(*) AS total FROM user_info WHERE role = 3";

$admin_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_admin))['total'];
$editor_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_editor))['total'];
$student_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_student))['total'];
?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                <?php include "sidebar.php"; ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- card1 start -->
                                            <div class="col-md-6 col-xl-3">
                                                <div class="card widget-card-1">
                                                    <div class="card-block-small">
                                                        <i class="icofont icofont-pie-chart bg-c-blue card1-icon"></i>
                                                        <span class="text-c-blue f-w-600">จำนวนโพสต์ทั้งหมด</span>
                                                        <h4><?php  echo $total_posts; ?></h4>
                                                        <div>
                                                            <span class="f-left m-t-10 text-muted">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- card1 end -->
                                            <!-- card1 start -->
                                            <div class="col-md-6 col-xl-3">
                                                <div class="card widget-card-1">
                                                    <div class="card-block-small">
                                                        <i class="icofont icofont-ui-home bg-c-pink card1-icon"></i>
                                                        <span class="text-c-pink f-w-600">หมวดหมู่ทั้งหมด</span>
                                                        <h4><?php  echo $total_categories; ?></h4>
                                                        <div>
                                                            <span class="f-left m-t-10 text-muted">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- card1 end -->
                                            <!-- card1 start -->
                                            <div class="col-md-6 col-xl-3">
                                                <div class="card widget-card-1">
                                                    <div class="card-block-small">
                                                        <i class="icofont icofont-warning-alt bg-c-green card1-icon"></i>
                                                        <span class="text-c-green f-w-600">แท็กทั้งหมด</span>
                                                        <h4><?php echo $total_tags; ?></h4>
                                                        <div>
                                                            <span class="f-left m-t-10 text-muted">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- card1 end -->
                                            <!-- card1 start -->
                                            <div class="col-md-6 col-xl-3">
                                                <div class="card widget-card-1">
                                                    <div class="card-block-small">
                                                        <i class="icofont icofont-social-twitter bg-c-yellow card1-icon"></i>
                                                        <span class="text-c-yellow f-w-600">ผู้ใช้งานทั้งหมด</span>
                                                        <h4><?php  echo $total_users; ?></h4>
                                                        <div>
                                                            <span class="f-left m-t-10 text-muted">
                                                              
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- card1 end -->
                                            
                                        </div>
                                    </div>

                                    <!-- เพิ่มตารางข้อมูลผู้ใช้งานทั้งหมดด้านล่างสุดของหน้า index.php -->
                                   
                                    <div class="card mt-5">
                                        <div class="card-header">
                                            <h4>กราฟสัดส่วนประเภทผู้ใช้งาน</h4>
                                        </div>
                                        <div class="card-block" style="max-width:350px; margin:auto;">
                                            <canvas id="userChart" width="320" height="220"></canvas>
                                        </div>
                                    </div>

                                    <div class="card mt-5">
                                        <div class="card-header">
                                            <h4>รายชื่อผู้ใช้งานทั้งหมด</h4>
                                        </div>
                                        <div class="card-block">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>ลำดับ</th>
                                                            <th>ชื่อ-นามสกุล</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql_users = "SELECT name FROM user_info ORDER BY id ASC";
                                                        $query_users = mysqli_query($conn, $sql_users);
                                                        $i = 1;
                                                        while ($row = mysqli_fetch_assoc($query_users)) {
                                                            echo "<tr>";
                                                            echo "<td>{$i}</td>";
                                                            echo "<td>{$row['name']}</td>";
                                                            echo "</tr>";
                                                            $i++;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="styleSelector">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        
<?php include "footer.php"; ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('userChart').getContext('2d');
const userChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['ผู้ดูแลระบบ', 'สโมสรนักศึกษา', 'นักศึกษา'],
        datasets: [{
            data: [
                <?php echo $admin_count; ?>,
                <?php echo $editor_count; ?>,
                <?php echo $student_count; ?>
            ],
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107'
            ]
        }]
    },
    options: {
        responsive: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});
</script>
