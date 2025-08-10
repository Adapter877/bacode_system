<?php
// admin/all_users.php
include "header.php";

// ✅ Guard: อนุญาตเฉพาะผู้ดูแลระบบ (role = 0)
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 0) {
    echo "<script>window.location.href='index.php'</script>";
    exit();
}

$sql   = "SELECT id, name, username, email, role, created_at FROM user_info ORDER BY id ASC";
$query = mysqli_query($conn, $sql);
?>
<style>
/* ทำให้ตารางอ่านง่ายขึ้น ไม่ล้น และหัวตารางติดบน */
.table-users thead th, .table-users tbody td { vertical-align: middle; }

/* จำกัดความกว้าง + ตัดคำ */
.col-idx    { width: 72px;  white-space: nowrap; }
.col-name   { width: 220px; }
.col-user   { width: 180px; white-space: nowrap; }
.col-email  { width: 260px; }
.col-role   { width: 160px; white-space: nowrap; }
.col-date   { width: 120px; white-space: nowrap; }
.col-action { width: 100px; white-space: nowrap; text-align: right; }

.truncate-1, .truncate-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
}
.truncate-1 { -webkit-line-clamp: 1; }
.truncate-2 { -webkit-line-clamp: 2; }

/* ความสูงพื้นที่เลื่อน: ปรับตาม layout ของธีม */
.table-responsive { max-height: calc(100vh - 320px); overflow: auto; }
.table-users thead th { position: sticky; top: 0; background: #fff; z-index: 2; }

/* ปุ่มแก้ไขกระชับ */
.btn-icon { padding: .25rem .5rem; }
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
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h3 class="mb-0">ผู้ใช้งานทั้งหมด</h3>
                </div>

                <div class="card-block table-border-style">
                  <div class="table-responsive">
                    <table class="table table-striped table-users mb-0">
                      <thead class="table-dark">
                        <tr>
                          <th class="col-idx">ลำดับ</th>
                          <th class="col-name">ชื่อ-นามสกุล</th>
                          <th class="col-user">ชื่อผู้ใช้</th>
                          <th class="col-email">อีเมล</th>
                          <th class="col-role">สิทธิ์</th>
                          <th class="col-date">วันที่สร้าง</th>
                          <th class="col-action">แก้ไข</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($query)):
                          $id       = (int)$row['id'];
                          $name     = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
                          $username = htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8');
                          $email    = htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8');
                          $role     = (int)$row['role'];
                          $created  = $row['created_at'] ? date("d/m/Y", strtotime($row['created_at'])) : '-';

                          // map role → label
                          if ($role === 0) {
                            $roleLabel = '<span class="badge badge-primary">ผู้ดูแลระบบ</span>';
                          } elseif ($role === 1) {
                            $roleLabel = '<span class="badge badge-success">สโมสรนักศึกษา</span>';
                          } elseif ($role === 3) {
                            $roleLabel = '<span class="badge badge-warning">นักศึกษา</span>';
                          } elseif ($role === 4) {
                            $roleLabel = '<span class="badge badge-info">ผู้บริหาร</span>';
                          } else {
                            $roleLabel = '<span class="badge badge-default">ไม่ทราบสิทธิ์</span>';
                          }
                        ?>
                        <tr>
                          <td><?php echo $i++; ?></td>
                          <td><div class="truncate-1" title="<?php echo $name; ?>"><?php echo $name; ?></div></td>
                          <td><div class="truncate-1" title="<?php echo $username; ?>"><?php echo $username; ?></div></td>
                          <td><div class="truncate-1" title="<?php echo $email; ?>"><?php echo $email; ?></div></td>
                          <td><?php echo $roleLabel; ?></td>
                          <td><?php echo $created; ?></td>
                          <td class="text-right">
                            <a href="edit_user.php?id=<?php echo $id; ?>" class="btn btn-info btn-icon" title="แก้ไข">
                              <i class="ti-pencil-alt"></i>
                            </a>
                          </td>
                        </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div><!-- /.table-responsive -->
                </div><!-- /.card-block -->
              </div><!-- /.card -->
            </div><!-- /.page-body -->
            <div id="styleSelector"></div>
          </div><!-- /.page-wrapper -->
        </div><!-- /.main-body -->
      </div><!-- /.pcoded-inner-content -->
    </div><!-- /.pcoded-content -->
  </div><!-- /.pcoded-wrapper -->
</div><!-- /.pcoded-main-container -->

<?php include "footer.php"; ?>
