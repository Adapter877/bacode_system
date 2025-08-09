<?php include "header.php";

$author = $_SESSION['username'] ?? '';
$role   = (int)($_SESSION['role'] ?? 3);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset   = ($page - 1) * $per_page;

if ($role === 0) {
    // แอดมินเห็นทุกโพสต์
    $sql   = "SELECT * FROM posts ORDER BY posts_id DESC LIMIT $offset, $per_page";
    $count = "SELECT COUNT(*) AS c FROM posts";
} else {
    // ผู้ใช้ทั่วไป เห็นเฉพาะของตัวเอง
    $safeAuthor = mysqli_real_escape_string($conn, $author);
    $sql   = "SELECT * FROM posts WHERE author='$safeAuthor' ORDER BY posts_id DESC LIMIT $offset, $per_page";
    $count = "SELECT COUNT(*) AS c FROM posts WHERE author='$safeAuthor'";
}

$query  = mysqli_query($conn, $sql);
$qCount = mysqli_query($conn, $count);
$total  = $qCount ? (int)mysqli_fetch_assoc($qCount)['c'] : 0;
$page_number = max(1, (int)ceil($total / $per_page));
?>
<style>
/* ตารางไม่ล้น, ตัดคำยาวทุกรุ่น */
.table-posts{
  table-layout: fixed;
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}
.table-posts thead th,
.table-posts tbody td{
  vertical-align: middle;
  overflow-wrap: anywhere;     /* ← กันคำยาว/URL ดันล้น */
  word-break: break-word;
  white-space: normal;
  padding-top: .75rem;
  padding-bottom: .75rem;
}

/* สัดส่วนคอลัมน์ (รวม 100%) */
.col-id      { width: 6%;  white-space: nowrap; }
.col-title   { width: 22%; }
.col-body    { width: 32%; }
.col-image   { width: 8%;  white-space: nowrap; }
.col-author  { width: 12%; white-space: nowrap; }
.col-date    { width: 10%; white-space: nowrap; }
.col-actions { width: 10%; white-space: nowrap; text-align: right; }

/* ตัดข้อความแบบ multi-line */
.truncate-1, .truncate-2{
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
}
.truncate-1{ -webkit-line-clamp: 1; }
.truncate-2{ -webkit-line-clamp: 2; }

/* รูปพรีวิวเล็กลง */
.table-posts img.thumb{
  width: 48px; height: 48px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid rgba(0,0,0,.08);
}

/* Head ติดบนสุดเวลามีสกรอลล์แนวตั้ง */
.table-wrap{ max-height: calc(100vh - 320px); overflow: auto; }
.table-posts thead th{
  position: sticky; top: 0;
  background: #fff; z-index: 2;
  box-shadow: 0 1px 0 rgba(0,0,0,.06);
}

/* ปุ่มไอคอนกระชับ */
.btn-icon{ padding: .25rem .5rem; line-height: 1; }
.btn-icon i{ pointer-events: none; }

/* สีกึ่งสลับ/โฮเวอร์ อ่านง่าย */
.table-posts tbody tr:nth-child(odd){ background: rgba(0,0,0,.015); }
.table-posts tbody tr:hover{ background: rgba(0,0,0,.035); }

/* ---------- Responsive: ตัดคอลัมน์ที่ไม่จำเป็นเพื่อลดความกว้าง ---------- */
/* จอกว้างปานกลาง: ซ่อนเนื้อหาโพสต์ */
@media (max-width: 1200px){
  .col-body{ display: none; }
  .col-title{ width: 40%; }
  .col-image{ width: 10%; }
  .col-author{ width: 20%; }
  .col-date{ width: 14%; }
  .col-actions{ width: 10%; }
}

/* จอแคบลงอีก: ซ่อนรูป */
@media (max-width: 992px){
  .col-image{ display: none; }
  .col-title{ width: 48%; }
  .col-author{ width: 22%; }
  .col-date{ width: 14%; }
  .col-actions{ width: 10%; }
}

/* มือถือ: เหลือ ID/หัวข้อ/ปุ่ม */
@media (max-width: 768px){
  .col-author, .col-date{ display: none; }
  .col-id{ width: 12%; }
  .col-title{ width: 68%; }
  .col-actions{ width: 20%; text-align: left; }
  .truncate-2{ -webkit-line-clamp: 3; } /* ให้หัวข้ออ่านง่ายขึ้นบนจอเล็ก */
}
    /* ให้ตารางยาวปกติบนจอเล็ก */
}
</style>


<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <?php include "sidebar.php"; ?>
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">

            <div class="page-body">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h3 class="mb-0">โพสต์ทั้งหมด</h3>
                  <a href="add_post.php" class="btn btn-primary btn-sm">+ เพิ่มโพสต์</a>
                </div>

                <div class="card-block table-border-style">
                  <div class="table-responsive">
                    <table class="table table-posts">
                      <thead class="table-inverse">
                        <tr>
                          <th class="col-id">ลำดับ</th>
                          <th class="col-title">หัวข้อโพสต์</th>
                          <th class="col-body">เนื้อหาโพสต์</th>
                          <th class="col-image">รูปภาพ</th>
                          <th class="col-author">ผู้เขียน</th>
                          <th class="col-date">วันที่สร้าง</th>
                          <th class="col-actions">จัดการ</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                          <?php while ($row = mysqli_fetch_assoc($query)):
                            $id           = (int)$row['posts_id'];
                            $posts_title  = $row['posts_title'] ?? '';
                            $posts_content= $row['posts_content'] ?? '';
                            $posts_image  = $row['posts_image'] ?? '';
                            $authorName   = $row['author'] ?? '';
                            $created      = $row['created_at'] ?? '';
                          ?>
                          <tr>
                            <td class="col-id"><?php echo $id; ?></td>

                            <td class="col-title">
                              <div class="truncate-2" title="<?php echo htmlspecialchars($posts_title, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($posts_title, ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                            </td>

                            <td class="col-body">
                              <div class="truncate-2" title="<?php echo htmlspecialchars(strip_tags($posts_content), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars(mb_substr(strip_tags($posts_content), 0, 150), ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                            </td>

                            <td class="col-image">
                              <?php if ($posts_image): ?>
                                <img class="thumb" src="<?php echo htmlspecialchars($posts_image, ENT_QUOTES, 'UTF-8'); ?>" alt="thumb">
                              <?php else: ?>
                                <span class="text-muted">-</span>
                              <?php endif; ?>
                            </td>

                            <td class="col-author">
                              <span class="badge badge-primary"><?php echo htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>

                            <td class="col-date">
                              <?php echo $created ? date("d-m-Y", strtotime($created)) : '-'; ?>
                            </td>

                            <td class="col-actions">
                              <a href="edit_post.php?id=<?php echo $id; ?>" class="btn btn-info btn-icon" title="แก้ไข">
                                <i class="ti-pencil-alt"></i>
                              </a>
                              <a href="delete_post.php?id=<?php echo $id; ?>" class="btn btn-danger btn-icon"
                                 onclick="return confirm('ยืนยันการลบโพสต์นี้หรือไม่?');" title="ลบ">
                                <i class="ti-trash"></i>
                              </a>
                            </td>
                          </tr>
                          <?php endwhile; ?>
                        <?php else: ?>
                          <tr><td colspan="7" class="text-center text-muted">ไม่พบข้อมูลโพสต์</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

                  <?php if ($page_number > 1): ?>
                  <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center m-4">
                      <?php for ($i=1; $i <= $page_number; $i++):
                        $active = ($i === $page) ? 'active' : ''; ?>
                        <li class="page-item <?php echo $active; ?>">
                          <a class="page-link p-3" href="all_posts.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                      <?php endfor; ?>
                    </ul>
                  </nav>
                  <?php endif; ?>

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
