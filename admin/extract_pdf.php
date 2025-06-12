<?php
header('Content-Type: application/json; charset=utf-8');

function sendJson($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    if (!isset($_FILES['barcode_pdf']) || $_FILES['barcode_pdf']['error'] !== UPLOAD_ERR_OK) {
        sendJson(['success' => false, 'message' => 'กรุณาเลือกไฟล์ PDF ที่ถูกต้อง']);
    }

    $pdfPath = $_FILES['barcode_pdf']['tmp_name'];
    $text = shell_exec("pdftotext " . escapeshellarg($pdfPath) . " -");  // `-` = ส่งออก stdout

    if (empty($text)) {
        sendJson(['success' => false, 'message' => 'ไม่สามารถดึงข้อความจาก PDF ได้']);
    }

    // Normalize
    $text = preg_replace('/\s+/', ' ', $text);

    // ใช้ regex เพื่อจับ barcode ที่คาดว่าจะเจอ เช่น AC1234-XXXX-YYYYY หรือ C2024-ABCD-12345
    preg_match_all('/(?:AC|C)?\d{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4,6}/', $text, $matches);
    $barcodes = array_unique($matches[0]);

    if (empty($barcodes)) {
        sendJson(['success' => false, 'message' => 'ไม่พบรหัสบาร์โค้ดในไฟล์']);
    }

    sendJson([
        'success' => true,
        'barcodes' => $barcodes,
        'count' => count($barcodes)
    ]);
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
