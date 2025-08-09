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
    if (!empty($_FILES['posts_image']['name'])) {
        $image_name = $_FILES['posts_image']['name'];
        $image_tmp  = $_FILES['posts_image']['tmp_name'];
        $image_size = intval($_FILES['posts_image']['size']);

        // ตรวจชนิดไฟล์ จากนามสกุล + mime
        $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_ext = ["jpg","jpeg","png","webp"];
        if (!in_array($ext, $allowed_ext, true)) {
            $errors[] = "นามสกุลไฟล์ไม่รองรับ (รองรับ: jpg, jpeg, png, webp)";
        }

        if ($image_size > 2 * 1024 * 1024) {
            $errors[] = "ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB";
        }

        if (!$errors) {
            // สร้างชื่อไฟล์ใหม่ ป้องกันชนกัน
            $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($image_name, PATHINFO_FILENAME));
            $newName  = $safeBase . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            // โฟลเดอร์ปลายทาง
            $uploadDir = __DIR__ . "/uploads";
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $locRel = "uploads/" . $newName;               // path relative เก็บใน DB
            $locAbs = $uploadDir . "/" . $newName;         // path จริง

            if (!move_uploaded_file($image_tmp, $locAbs)) {
                $errors[] = "อัปโหลดรูปภาพไม่สำเร็จ";
            } else {
                $loc = $locRel;
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
