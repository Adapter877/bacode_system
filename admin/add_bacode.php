<?php
// admin/import_barcodes.php
// นำเข้าบาร์โค้ดจาก PDF/CSV/TXT → อัปเดต student_activities แบบปลอดภัย
// คุณสมบัติ:
//  - ต้องเลือกกิจกรรมก่อนอัปโหลด (อัปเดตเฉพาะกิจกรรมนั้นเท่านั้น)
//  - โหมด SID: (student_id => barcode)
//  - โหมด SEQ: มีแต่ barcode → ใส่ให้ “แถวของกิจกรรมนี้ที่ barcode ยังว่าง” ตามลำดับ
//  - พรีวิวรายชื่อผู้สมัครที่จะถูกอัปเดต + สถานะ (OK/ซ้ำ/ไม่พบ/มีค่าแล้ว)
//  - เช็คบาร์โค้ดซ้ำทั้งในไฟล์และในฐานข้อมูลก่อนอัปเดตจริง
//  - ไม่ insert แถวใหม่ | อัปเดตเฉพาะที่มีอยู่และยังว่างเท่านั้น

session_start();
require_once __DIR__ . '/dbconfig.php';

// อนุญาตเฉพาะ admin
if (!isset($_SESSION['role']) || (string)$_SESSION['role'] !== '0') {
    http_response_code(403);
    echo "Permission denied.";
    exit();
}

$errors = [];
$notices = [];
$updated = 0;
$skipped = 0;

/* -------------------- Utilities -------------------- */
function hasPdftotext(): bool {
    $which = @shell_exec("which pdftotext 2>/dev/null");
    return is_string($which) && trim($which) !== '';
}
function pdfToTextFlexible(string $pdfPath): ?string {
    if (hasPdftotext()) {
        $cmd = "pdftotext -layout -nopgbrk " . escapeshellarg($pdfPath) . " -";
        $out = @shell_exec($cmd);
        if (is_string($out) && $out !== '') return $out;
    }
    if (class_exists('\\Smalot\\PdfParser\\Parser')) {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();
            if (is_string($text) && $text !== '') return $text;
        } catch (\Throwable $e) {}
    }
    return null;
}
/** barcode สมเหตุสมผล? */
function looks_like_barcode(string $s): bool {
    if (!preg_match('/^(?=[A-Za-z0-9._-]{8,64}$)(?=.*[A-Za-z])[A-Za-z0-9._-]+$/u', $s)) return false;
    if (preg_match('/^AC\d{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}$/', $s)) return true;
    return true;
}
/** แปลงข้อความเป็น data (sid map หรือ seq list) */
function parseTextToData(string $text): array {
    // คืนค่า ['mode'=>'sid'|'seq', 'data'=>mapping|list]
    $lines = preg_split("/\r\n|\r|\n/", $text);
    $mapSid = [];
    $seq = [];
    foreach ($lines as $raw) {
        $line = trim($raw); if ($line==='') continue;
        if (preg_match('/\b(\d{8,13})\b/u', $line, $m)) {
            $sid = $m[1];
            $rest = trim(preg_replace('/\b'.preg_quote($sid,'/').'\b/u', '', $line, 1));
            if (preg_match_all('/[A-Za-z0-9._-]{4,64}/u', $rest, $mm)) {
                foreach ($mm[0] as $tok) if (looks_like_barcode($tok)) { $mapSid[$sid] = $tok; break; }
            }
        } else {
            if (preg_match_all('/[A-Za-z0-9._-]{4,64}/u', $line, $mm)) {
                foreach ($mm[0] as $tok) if (looks_like_barcode($tok)) $seq[] = $tok;
            }
        }
    }
    if (!empty($mapSid)) return ['mode'=>'sid', 'data'=>$mapSid];
    if (!empty($seq))    return ['mode'=>'seq', 'data'=>$seq];
    if (preg_match_all('/[A-Za-z0-9._-]{8,64}/u', $text, $mm)) {
        $extra = array_values(array_filter($mm[0], 'looks_like_barcode'));
        if (!empty($extra)) return ['mode'=>'seq', 'data'=>$extra];
    }
    return ['mode'=>'empty','data'=>[]];
}
/** CSV → ['mode'=>'sid'|'empty', 'data'=>map] (student_id, barcode) */
function parseCsvToData(string $csvPath): array {
    $map = [];
    if (($h=fopen($csvPath,'r'))!==false) {
        while(($row=fgetcsv($h))!==false) {
            if (count($row)<2) continue;
            $sid = preg_replace('/\D+/','', trim((string)$row[0]));
            $bc  = trim((string)$row[1]);
            if ($sid!=='' && looks_like_barcode($bc)) $map[$sid]=$bc;
        }
        fclose($h);
    }
    return ['mode'=> empty($map)?'empty':'sid', 'data'=>$map];
}
/** barcode ซ้ำในฐานข้อมูลหรือไม่ */
function db_barcode_exists(mysqli $conn, string $bc): bool {
    $sql="SELECT 1 FROM student_activities WHERE barcode = ? LIMIT 1";
    $st=mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($st,"s",$bc);
    mysqli_stmt_execute($st);
    $res=mysqli_stmt_get_result($st);
    $exists = $res && mysqli_fetch_row($res);
    mysqli_stmt_close($st);
    return (bool)$exists;
}

/* -------------------- โหลดกิจกรรม -------------------- */
$activity_opts=[];
$ro = mysqli_query($conn,"SELECT DISTINCT activity_name FROM student_activities WHERE activity_name<>'' ORDER BY activity_name ASC");
if($ro){ while($r=mysqli_fetch_assoc($ro)){ $activity_opts[]=$r['activity_name']; } }

$selected_activity = $_POST['activity_name'] ?? ($_SESSION['import_activity'] ?? '');

/* -------------------- Upload / Preview -------------------- */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['mode']??'')==='upload') {
    if ($selected_activity==='') {
        $errors[]="กรุณาเลือกกิจกรรมก่อน";
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error']!==UPLOAD_ERR_OK) {
        $errors[]="อัปโหลดไฟล์ไม่สำเร็จ";
    } else {
        $tmp=$_FILES['file']['tmp_name'];
        $ext=strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
        if ($ext==='pdf') {
            $txt = pdfToTextFlexible($tmp);
            if ($txt===null) {
                $errors[]="อ่าน PDF ไม่ได้ (ไม่มี pdftotext/pdfparser) — ใช้ .txt/.csv แทนชั่วคราวได้";
            } else {
                $parsed = parseTextToData($txt);
            }
        } elseif ($ext==='txt') {
            $txt=@file_get_contents($tmp);
            if(!is_string($txt) || $txt===''){ $errors[]="อ่าน .txt ไม่ได้"; }
            else { $parsed = parseTextToData($txt); }
        } elseif ($ext==='csv') {
            $parsed = parseCsvToData($tmp);
        } else {
            $errors[]="รองรับ .pdf / .txt / .csv เท่านั้น";
        }

        if (empty($errors)) {
            if (empty($parsed) || $parsed['mode']==='empty' || empty($parsed['data'])) {
                $errors[]="ไม่พบข้อมูลที่ใช้ได้ในไฟล์";
            } else {
                $mode = $parsed['mode'];
                $data = $parsed['data'];
                $preview_rows = [];
                $file_seen = [];

                if ($mode==='sid') {
                    // ผู้สมัครกิจกรรมนี้ทั้งหมด
                    $st = mysqli_prepare($conn,
                        "SELECT id, student_id, student_name, barcode
                         FROM student_activities
                         WHERE activity_name = ?");
                    mysqli_stmt_bind_param($st,"s",$selected_activity);
                    mysqli_stmt_execute($st);
                    $rs = mysqli_stmt_get_result($st);
                    $by_sid = [];
                    while($row=$rs->fetch_assoc()){
                        $by_sid[(string)$row['student_id']] = $row;
                    }
                    mysqli_stmt_close($st);

                    foreach ($data as $sid=>$bc) {
                        $row = $by_sid[(string)$sid] ?? null;
                        $status = 'OK';
                        $reason = '';
                        if (!$row) {
                            $status='SKIP'; $reason='ไม่พบผู้สมัครในกิจกรรมนี้';
                        } elseif (!empty($row['barcode'])) {
                            $status='SKIP'; $reason='แถวนั้นมีบาร์โค้ดอยู่แล้ว';
                        } elseif (!looks_like_barcode($bc)) {
                            $status='SKIP'; $reason='รูปแบบบาร์โค้ดไม่ถูกต้อง';
                        } elseif (isset($file_seen[$bc])) {
                            $status='SKIP'; $reason='บาร์โค้ดซ้ำในไฟล์';
                        } elseif (db_barcode_exists($conn,$bc)) {
                            $status='SKIP'; $reason='บาร์โค้ดนี้ถูกใช้ไปแล้วในฐานข้อมูล';
                        }
                        $file_seen[$bc]=true;

                        $preview_rows[] = [
                            'target_id'   => $row['id'] ?? null,
                            'student_id'  => (string)$sid,
                            'student_name'=> $row['student_name'] ?? '-',
                            'barcode'     => $bc,
                            'status'      => $status,
                            'reason'      => $reason,
                        ];
                    }
                } else { // seq
                    // แถวที่ยังไม่มี barcode
                    $st = mysqli_prepare($conn,
                        "SELECT id, student_id, student_name
                         FROM student_activities
                         WHERE activity_name = ? AND (barcode IS NULL OR barcode = '')
                         ORDER BY id ASC");
                    mysqli_stmt_bind_param($st,"s",$selected_activity);
                    mysqli_stmt_execute($st);
                    $rs = mysqli_stmt_get_result($st);
                    $eligible = $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
                    mysqli_stmt_close($st);

                    $n = min(count($data), count($eligible));
                    for ($i=0; $i<$n; $i++){
                        $bc  = (string)$data[$i];
                        $row = $eligible[$i];
                        $status='OK'; $reason='';
                        if (!looks_like_barcode($bc)) {
                            $status='SKIP'; $reason='รูปแบบบาร์โค้ดไม่ถูกต้อง';
                        } elseif (isset($file_seen[$bc])) {
                            $status='SKIP'; $reason='บาร์โค้ดซ้ำในไฟล์';
                        } elseif (db_barcode_exists($conn,$bc)) {
                            $status='SKIP'; $reason='บาร์โค้ดนี้ถูกใช้ไปแล้วในฐานข้อมูล';
                        }
                        $file_seen[$bc]=true;

                        $preview_rows[] = [
                            'target_id'   => (int)$row['id'],
                            'student_id'  => (string)$row['student_id'],
                            'student_name'=> (string)$row['student_name'],
                            'barcode'     => $bc,
                            'status'      => $status,
                            'reason'      => $reason,
                        ];
                    }
                    // barcode เกินโควต้า → แจ้งในพรีวิว
                    for ($i=$n; $i<count($data); $i++){
                        $bc=(string)$data[$i];
                        $status='SKIP'; $reason='เกินจำนวนผู้สมัครที่ยังว่าง';
                        if (!looks_like_barcode($bc)) { $reason='รูปแบบบาร์โค้ดไม่ถูกต้อง'; }
                        elseif (isset($file_seen[$bc])) { $reason='บาร์โค้ดซ้ำในไฟล์'; }
                        elseif (db_barcode_exists($conn,$bc)) { $reason='บาร์โค้ดนี้ถูกใช้ไปแล้วในฐานข้อมูล'; }
                        $file_seen[$bc]=true;
                        $preview_rows[] = [
                            'target_id'=>null,'student_id'=>'-','student_name'=>'-',
                            'barcode'=>$bc,'status'=>$status,'reason'=>$reason,
                        ];
                    }
                }

                $_SESSION['import_preview']  = [
                    'activity' => $selected_activity,
                    'rows'     => $preview_rows,
                ];
                $_SESSION['import_activity'] = $selected_activity;
                $notices[] = "อ่านไฟล์สำเร็จ: เตรียมอัปเดต ".count($preview_rows)." รายการ (ตรวจสอบในตารางพรีวิวด้านล่าง)";
            }
        }
    }
}

/* -------------------- Apply -------------------- */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['mode']??'')==='apply') {
    $pv = $_SESSION['import_preview'] ?? null;
    if (!$pv || empty($pv['rows']) || empty($pv['activity'])) {
        $errors[]="ไม่มีข้อมูลสำหรับอัปเดต";
    } else {
        $rows=$pv['rows'];
        $st = mysqli_prepare($conn,"UPDATE student_activities SET barcode=? WHERE id=? AND (barcode IS NULL OR barcode='')");
        foreach ($rows as $r) {
            if ($r['status']!=='OK') { $skipped++; continue; }
            // double-check กันชนระหว่างพรีวิว→อัปเดต
            if (db_barcode_exists($conn,$r['barcode'])) { $skipped++; continue; }
            mysqli_stmt_bind_param($st,"si",$r['barcode'],$r['target_id']);
            if (mysqli_stmt_execute($st) && mysqli_stmt_affected_rows($st)>0) $updated++; else $skipped++;
        }
        mysqli_stmt_close($st);
        unset($_SESSION['import_preview'], $_SESSION['import_activity']);
        if ($updated || $skipped) $notices[]="อัปเดตสำเร็จ: $updated แถว, ข้าม: $skipped แถว";
    }
}

/* -------------------- Read preview back -------------------- */
$pv = $_SESSION['import_preview'] ?? null;
$selected_activity = $_SESSION['import_activity'] ?? $selected_activity;

/* -------------------- เริ่มส่วน UI ฝั่งแอดมิน (รวม navbar/sidebar/footer) -------------------- */
include "header.php"; // ✅ ใช้ header/admin layout เดิม
?>
<style>
.container-narrow{ max-width: 1050px; margin: 0 auto; }
.code{ font-family: ui-monospace, Menlo, Consolas, "Liberation Mono", monospace; }
.badge-status{ font-size:.85rem; }
</style>

<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <?php include "sidebar.php"; ?>
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">

            <div class="page-header">
              <div class="row align-items-end">
                <div class="col-lg-8">
                  <div class="page-header-title">
                    <i class="ti-import bg-c-blue"></i>
                    <div class="d-inline">
                      <h4>นำเข้าบาร์โค้ด (PDF/CSV/TXT)</h4>
                      <span>อัปเดตเฉพาะกิจกรรมที่เลือก และเฉพาะแถวที่ยังไม่มีบาร์โค้ด</span>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="page-header-breadcrumb">
                    <ul class="breadcrumb breadcrumb-title">
                      <li class="breadcrumb-item"><a href="index.php"><i class="ti-home"></i></a></li>
                      <li class="breadcrumb-item">Admin</li>
                      <li class="breadcrumb-item"><a href="#">Import Barcodes</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="container-narrow">

              <?php if (!empty($notices)): ?>
                <div class="alert alert-info">
                  <?= nl2br(htmlspecialchars(implode("\n", $notices))) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <?= nl2br(htmlspecialchars(implode("\n", $errors))) ?>
                </div>
              <?php endif; ?>

              <div class="card mb-4">
                <div class="card-header">
                  อัปโหลดไฟล์ (PDF / CSV / TXT)
                </div>
                <div class="card-block">
                  <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="mode" value="upload">
                    <div class="form-group">
                      <label class="form-label">เลือกกิจกรรม</label>
                      <select name="activity_name" class="form-control" required>
                        <option value="">-- เลือกกิจกรรม --</option>
                        <?php foreach ($activity_opts as $opt): ?>
                          <option value="<?= htmlspecialchars($opt) ?>" <?= $opt===$selected_activity?'selected':'' ?>>
                            <?= htmlspecialchars($opt) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">ไฟล์นำเข้า</label>
                      <input type="file" name="file" class="form-control" accept=".pdf,.csv,.txt" required>
                      <small class="text-muted d-block mt-2">
                        • PDF ต้องเป็นไฟล์ข้อความ (ไม่ใช่ภาพล้วน) | หากไม่มี pdftotext ให้ใช้ TXT/CSV ได้<br>
                        • CSV: <code>student_id,barcode</code> | TXT/PDF ดึงได้ทั้งแบบมีรหัส นศ. หรือมีแต่บาร์โค้ด
                      </small>
                    </div>
                    <button class="btn btn-primary"><i class="ti-eye"></i> อ่านไฟล์และพรีวิว</button>
                  </form>
                </div>
              </div>

              <?php if ($pv && !empty($pv['rows'])): ?>
                <div class="card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                      พรีวิวข้อมูล (กิจกรรม: <b><?= htmlspecialchars($pv['activity']) ?></b>) —
                      ทั้งหมด: <?= count($pv['rows']) ?> รายการ
                    </span>
                    <form method="post">
                      <input type="hidden" name="mode" value="apply">
                      <button class="btn btn-success btn-sm"
                        onclick="return confirm('ยืนยันอัปเดตเฉพาะรายการที่สถานะ OK หรือไม่?');">
                        <i class="ti-check"></i> ยืนยันอัปเดต
                      </button>
                    </form>
                  </div>
                  <div class="card-block p-0">
                    <div class="table-responsive">
                      <table class="table table-sm table-striped mb-0">
                        <thead class="thead-default">
                          <tr>
                            <th style="width:60px">#</th>
                            <th style="width:140px">student_id</th>
                            <th>ชื่อผู้สมัคร</th>
                            <th style="min-width:280px">barcode</th>
                            <th style="width:200px">สถานะ</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $i=1; foreach ($pv['rows'] as $r): ?>
                            <tr>
                              <td><?= $i++ ?></td>
                              <td class="code"><?= htmlspecialchars($r['student_id']) ?></td>
                              <td><?= htmlspecialchars($r['student_name']) ?></td>
                              <td class="code"><?= htmlspecialchars($r['barcode']) ?></td>
                              <td>
                                <?php if ($r['status']==='OK'): ?>
                                  <span class="badge badge-success badge-status">OK</span>
                                <?php else: ?>
                                  <span class="badge badge-inverse badge-status">SKIP</span>
                                  <small class="text-muted d-block"><?= htmlspecialchars($r['reason']) ?></small>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="p-2 small text-muted">
                      ระบบจะอัปเดตเฉพาะรายการที่สถานะ <b>OK</b> เท่านั้น และจะอัปเดตเฉพาะแถวที่ <b>barcode ว่าง</b> ของกิจกรรมที่เลือก
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($updated || $skipped): ?>
                <div class="card">
                  <div class="card-header">สรุปผลการอัปเดต</div>
                  <div class="card-block">
                    อัปเดตสำเร็จ: <strong><?= (int)$updated ?></strong> แถว<br>
                    ข้าม: <strong><?= (int)$skipped ?></strong> แถว
                  </div>
                </div>
              <?php endif; ?>

            </div><!-- /.container-narrow -->

          </div><!-- /.page-wrapper -->
          <div id="styleSelector"></div>
        </div><!-- /.main-body -->
      </div><!-- /.pcoded-inner-content -->
    </div><!-- /.pcoded-content -->
  </div><!-- /.pcoded-wrapper -->
</div><!-- /.pcoded-main-container -->

<?php include "footer.php"; // ✅ footer ของแอดมิน ?>
