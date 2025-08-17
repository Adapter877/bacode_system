<?php
include "header.php";

$errors = [];
$success = "";

// กดบันทึกโพสต์
if (isset($_POST['submit'])) {
    // รับค่า
    $author        = mysqli_real_escape_string($conn, $_SESSION['username'] ?? '');
    $posts_title   = mysqli_real_escape_string($conn, trim($_POST['posts_title'] ?? ''));
    $posts_content = mysqli_real_escape_string($conn, trim($_POST['posts_content'] ?? ''));

    // ไม่ใช้หมวดหมู่/แท็กอีกแล้ว
    $category_name = '';
    $tag_name      = '';

    // ตรวจฟิลด์บังคับ
    if ($posts_title === '' || $posts_content === '') {
        $errors[] = "กรุณากรอกหัวข้อและเนื้อหาโพสต์";
    }

    // อัปโหลดรูป (เลือกได้ ไม่บังคับ)
$loc = '';

if (isset($_FILES['posts_image']) && $_FILES['posts_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['posts_image'];

    // 1) เช็กสถานะอัปโหลด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $map = [
            UPLOAD_ERR_INI_SIZE   => "ไฟล์ใหญ่กว่า upload_max_filesize ใน php.ini",
            UPLOAD_ERR_FORM_SIZE  => "ไฟล์ใหญ่กว่า limit ของฟอร์ม",
            UPLOAD_ERR_PARTIAL    => "อัปโหลดไม่สมบูรณ์",
            UPLOAD_ERR_NO_FILE    => "ไม่ได้เลือกไฟล์",
            UPLOAD_ERR_NO_TMP_DIR => "ไม่มีโฟลเดอร์ temp",
            UPLOAD_ERR_CANT_WRITE => "เขียนไฟล์ไม่สำเร็จ",
            UPLOAD_ERR_EXTENSION  => "ส่วนขยาย PHP บางตัวบล็อกไว้",
        ];
        $errors[] = $map[$file['error']] ?? "อัปโหลดไฟล์ล้มเหลว (รหัส {$file['error']})";
    } else {
        // 2) จำกัดขนาดไฟล์ (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB";
        }

        // 3) ตรวจ MIME จริงของไฟล์
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowedMime[$mime])) {
            $errors[] = "ไฟล์รูปภาพไม่รองรับ (รองรับ: JPG, PNG, WEBP)";
        }

        // 4) ย้ายไฟล์เข้า /admin/uploads แล้วเก็บพาธแบบ relative
        if (!$errors) {
            $ext     = $allowedMime[$mime];
            $newName = sprintf('post_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), $ext);

            $uploadDir = __DIR__ . "/uploads";   // => /admin/uploads
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = "สร้างโฟลเดอร์ uploads ไม่ได้";
                }
            }

            if (!$errors) {
                $locRel = "uploads/" . $newName;        // เก็บใน DB
                $locAbs = $uploadDir . "/" . $newName;  // ย้ายไฟล์จริง

                if (!move_uploaded_file($file['tmp_name'], $locAbs)) {
                    $errors[] = "อัปโหลดรูปภาพไม่สำเร็จ";
                } else {
                    @chmod($locAbs, 0644);
                    $loc = $locRel;
                }
            }
        }
    }
}


    // บันทึก DB
    if (!$errors) {
        // ใช้ prepared statements
        $sql = "INSERT INTO posts (posts_title, posts_content, posts_image, category_name, tag_name, author)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $posts_title, $posts_content, $loc, $category_name, $tag_name, $author);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>window.location.href='all_posts.php'</script>";
            exit;
        } else {
            $errors[] = "ไม่สามารถบันทึกข้อมูลได้: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
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
              <div class="row">
                <div class="col-sm-12">

                  <div class="card">
                    <div class="card-header">
                      <h3>เพิ่มโพสต์</h3>
                      <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mt-3 mb-0">
                          <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                              <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                          </ul>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="card-block">
                      <h4 class="sub-title">รายละเอียดข้อมูล</h4>
                      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">หัวข้อโพสต์</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" name="posts_title"
                                   placeholder="กรอกหัวข้อโพสต์" required>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">เนื้อหาโพสต์</label>
                          <div class="col-sm-10">
                            <textarea rows="10" class="form-control" name="posts_content"
                                      placeholder="กรอกเนื้อหาโพสต์" required></textarea>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-sm-2 col-form-label">รูปภาพโพสต์ (ไม่บังคับ)</label>
                          <div class="col-sm-10">
                            <input type="file" class="form-control" name="posts_image" accept=".jpg,.jpeg,.png,.webp">
                            <small class="text-muted">รองรับ jpg, jpeg, png, webp | สูงสุด 2MB</small>
                          </div>
                        </div>

                        <input type="hidden" name="author" value="<?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-primary waves-effect waves-light" name="submit">
                          เพิ่มโพสต์
                        </button>

                      </form>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div id="styleSelector"></div>
          </div>
        </div>
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
