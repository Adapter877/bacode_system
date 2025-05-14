<?php include "header.php"; ?>

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
                                    <h3>นำเข้าข้อมูลกิจกรรมจาก PDF</h3>
                                </div>
                                <div class="card-block">
                                    <form action="import_pdf.php" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="students_pdf">📘 ไฟล์ PDF รายชื่อนักศึกษา:</label>
                                            <input type="file" name="students_pdf" class="form-control" accept=".pdf" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="activities_pdf">📗 ไฟล์ PDF รหัสกิจกรรม:</label>
                                            <input type="file" name="activities_pdf" class="form-control" accept=".pdf" required>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary">อัปโหลดและนำเข้า</button>
                                    </form>
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
