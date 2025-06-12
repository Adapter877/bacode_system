<?php
// filepath: /home/ohm/Desktop/project/Blog-php-mysql-main/admin/verify_pdf.php
if (!isset($_GET['file'])) {
    echo "ไม่พบไฟล์ PDF";
    exit;
}
$pdf_file = basename($_GET['file']);
$pdf_path = __DIR__ . '/barcode_pdf/' . $pdf_file;

if (!file_exists($pdf_path)) {
    echo "ไม่พบไฟล์ PDF";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตรวจสอบ PDF</title>
</head>
<body style="background:#232135;color:#fff;">
    <h2>ตรวจสอบข้อมูลจาก PDF</h2>
    <iframe src="barcode_pdf/<?php echo htmlspecialchars(urlencode($pdf_file)); ?>" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>
    <p><a href="barcode_pdf/<?php echo htmlspecialchars(urlencode($pdf_file)); ?>" target="_blank" style="color:#a599e9;">เปิด PDF ในแท็บใหม่</a></p>
</body>
</html>