<?php include "header.php"; ?>

<!-- Page Content -->
<div class="heading-page header-text">
  <section class="page-heading">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="text-content">
            <h4>student activities</h4>
            <h2>search activity code</h2>
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
            <label for="activity_code"><strong>กรอกรหัสกิจกรรม:</strong></label>
            <input type="text" class="form-control" id="activity_code" name="activity_code" placeholder="เช่น ACT12345" required>
          </div>
          <button type="submit" class="btn btn-primary mt-2">ค้นหา</button>
        </form>
      </div>
    </div>

    <?php
    if (isset($_GET['activity_code'])) {
      $activity_code = $_GET['activity_code'];

      // สมมุติว่ามีข้อมูลจากฐานข้อมูล
      $participants = [
        [
          'date' => '2025-04-10',
          'name' => 'สมชาย ใจดี',
          'student_id' => '65011234',
          'program' => 'วิศวกรรมคอมพิวเตอร์',
          'barcode' => 'ACT123456789',
          'contact' => 'somchai@email.com'
        ],
        [
          'date' => '2025-04-10',
          'name' => 'สุดารัตน์ แสนดี',
          'student_id' => '65014567',
          'program' => 'วิศวกรรมโยธา',
          'barcode' => 'ACT123456790',
          'contact' => 'sudarat@email.com'
        ],
      ];

      echo "<div class='row'>
              <div class='col-lg-12'>
                <h4>ผลการค้นหา (กิจกรรม: <span class='text-primary'>$activity_code</span>)</h4>
                <div class='table-responsive'>
                  <table class='table table-bordered table-striped'>
                    <thead class='thead-dark'>
                      <tr>
                        <th>วันที่เข้าร่วม</th>
                        <th>ชื่อ - สกุล</th>
                        <th>รหัสประจำตัวนักศึกษา</th>
                        <th>หลักสูตร</th>
                        <th>บาโค้ด</th>
                        <th>ติดต่อ</th>
                      </tr>
                    </thead>
                    <tbody>";

      foreach ($participants as $row) {
        echo "<tr>
                <td>{$row['date']}</td>
                <td>{$row['name']}</td>
                <td>{$row['student_id']}</td>
                <td>{$row['program']}</td>
                <td>{$row['barcode']}</td>
                <td>{$row['contact']}</td>
              </tr>";
      }

      echo "    </tbody>
                  </table>
                </div>
              </div>
            </div>";
    }
    ?>

  </div>
</section>

<?php include "footer.php"; ?>
