<?php
include "header.php";

require_once __DIR__ . '/../vendor/autoload.php'; // path ของ Composer autoload

$error = [];
$msg = "";
$barcode = "";

// กรณีกดปุ่ม "ดึงข้อมูลจาก PDF"
if (isset($_POST['extract_pdf'])) {
    if (isset($_FILES['barcode_pdf']) && $_FILES['barcode_pdf']['error'] == UPLOAD_ERR_OK) {
        $pdf_tmp_path = $_FILES['barcode_pdf']['tmp_name'];

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_tmp_path);
        $text = $pdf->getText();

        if (preg_match('/[A-Z0-9\-]{6,}/', $text, $matches)) {
            $barcode = $matches[0];
            $msg = '<div class="alert alert-success">ดึงรหัสบาร์โค้ดจาก PDF สำเร็จ: ' . $barcode . '</div>';
        } else {
            $msg = '<div class="alert alert-warning">ไม่พบรหัสบาร์โค้ดในไฟล์ PDF</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">กรุณาเลือกไฟล์ PDF ที่ถูกต้อง</div>';
    }
}

// กรณีกดปุ่ม "submit"
if (isset($_POST['submit'])) {
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $major = mysqli_real_escape_string($conn, $_POST['major']);
    $activity_name = mysqli_real_escape_string($conn, $_POST['activity_name']);
    $activity_hours = mysqli_real_escape_string($conn, $_POST['activity_hours']);
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $date_joined = mysqli_real_escape_string($conn, $_POST['date_joined']);

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
                      <form method="POST" action="" enctype="multipart/form-data">
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
                          <div class="input-group">
                            <input type="text" name="barcode" class="form-control" value="<?php echo htmlspecialchars($barcode); ?>" id="barcodeInput">
                            <div class="input-group-append">
                              <button type="button" class="btn btn-info" onclick="showBarcodeHistory()">
                                <i class="feather icon-list"></i> ประวัติบาร์โค้ด
                              </button>
                            </div>
                          </div>
                        </div>
                        <div class="form-group">
                          <label>ข้อมูลติดต่อ</label>
                          <input type="text" name="contact" class="form-control">
                        </div>
                        <div class="form-group">
                          <label>วันที่เข้าร่วม</label>
                          <input type="date" name="date_joined" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label>อัปโหลด PDF เพื่อดึงรหัสบาร์โค้ด</label>
                          <input type="file" name="barcode_pdf" class="form-control-file" accept=".pdf">
                        </div>
                        <div class="form-group d-flex gap-2">
                          <button type="submit" name="extract_pdf" class="btn btn-warning mt-3">ดึงข้อมูลจาก PDF</button>
                          <button type="submit" name="submit" class="btn btn-primary mt-3">บันทึกข้อมูล</button>
                        </div>
                      </form>

                      <!-- Barcode History Modal -->
                      <div class="modal fade" id="barcodeHistoryModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">ประวัติบาร์โค้ดที่นำเข้า</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div id="barcodeList"></div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <script>
                      let barcodeHistory = [];

                      // Modify your existing PDF extraction code to store barcodes in history
                      document.querySelector('button[name="extract_pdf"]').onclick = function(e) {
                          e.preventDefault();
                          
                          const pdfInput = document.querySelector('input[name="barcode_pdf"]');
                          if (!pdfInput.files.length) {
                              alert('กรุณาเลือกไฟล์ PDF ก่อน');
                              return;
                          }

                          const formData = new FormData();
                          formData.append('barcode_pdf', pdfInput.files[0]);
                          formData.append('extract_pdf', '1');

                          const popup = window.open('', 'PDF Extract', 'width=600,height=600');
                          popup.document.write(`
                              <html>
                              <head>
                                  <title>กำลังดึงข้อมูลจาก PDF</title>
                                  <style>
                                      body { font-family: Arial; background: #f5f5f5; padding: 20px; }
                                      .loading { text-align: center; margin-top: 50px; }
                                      .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
                                      .success { background: #d4edda; color: #155724; }
                                      .error { background: #f8d7da; color: #721c24; }
                                      button { padding: 8px 15px; border-radius: 4px; cursor: pointer; margin: 5px; }
                                      .barcode-list { margin: 15px 0; }
                                      .barcode-item { margin: 10px 0; padding: 10px; background: #fff; border-radius: 4px; }
                                  </style>
                              </head>
                              <body>
                                  <div class="loading">กำลังดึงข้อมูลจาก PDF...</div>
                              </body>
                              </html>
                          `);

                          fetch('extract_pdf.php', {
                              method: 'POST',
                              body: formData
                          })
                          .then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  // Add barcodes to history
                                  barcodeHistory = barcodeHistory.concat(data.barcodes);
                                  localStorage.setItem('barcodeHistory', JSON.stringify(barcodeHistory));

                                  let barcodesList = data.barcodes.map((barcode, index) => `
                                      <div class="barcode-item">
                                          รหัสบาร์โค้ด ${index + 1}: ${barcode}
                                          <button onclick="window.opener.document.querySelector('input[name=barcode]').value='${barcode}'; window.close();">
                                              ใช้รหัสนี้
                                          </button>
                                      </div>
                                  `).join('');

                                  popup.document.querySelector('.loading').innerHTML = `
                                      <div class="result success">
                                          พบรหัสบาร์โค้ดทั้งหมด ${data.count} รหัส<br>
                                          <div class="barcode-list">
                                              ${barcodesList}
                                          </div>
                                          <button onclick="window.close()">ปิด</button>
                                      </div>
                                  `;
                              } else {
                                  popup.document.querySelector('.loading').innerHTML = `
                                      <div class="result error">
                                          ${data.message}<br><br>
                                          <button onclick="window.close()">ปิด</button>
                                      </div>
                                  `;
                              }
                          })
                          .catch(error => {
                              popup.document.querySelector('.loading').innerHTML = `
                                  <div class="result error">
                                      เกิดข้อผิดพลาด: ${error.message}<br><br>
                                      <button onclick="window.close()">ปิด</button>
                                  </div>
                              `;
                          });
                      };

                      // Function to show barcode history modal
                      function showBarcodeHistory() {
                          const storedHistory = localStorage.getItem('barcodeHistory');
                          if (storedHistory) {
                              barcodeHistory = JSON.parse(storedHistory);
                          }

                          let historyHTML = '<div class="list-group">';
                          barcodeHistory.forEach((barcode, index) => {
                              historyHTML += `
                                  <a href="#" class="list-group-item list-group-item-action" 
                                     onclick="selectBarcode('${barcode}')">
                                      ${barcode}
                                  </a>
                              `;
                          });
                          historyHTML += '</div>';

                          document.getElementById('barcodeList').innerHTML = 
                              barcodeHistory.length ? historyHTML : '<p class="text-center">ไม่พบประวัติบาร์โค้ด</p>';
                          
                          $('#barcodeHistoryModal').modal('show');
                      }

                      // Function to select barcode from history
                      function selectBarcode(barcode) {
                          document.getElementById('barcodeInput').value = barcode;
                          $('#barcodeHistoryModal').modal('hide');
                      }
                      </script>
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
