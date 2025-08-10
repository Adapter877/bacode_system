<?php
// admin/access_guard.php
// ใช้ร่วมกับหน้าใดๆ ที่ต้องจำกัดสิทธิ์ด้วยตัวแปร $ALLOW_ROLES เช่น [4] หรือ ['4']
// ตัวไฟล์จะยอมรับทั้ง string และ int โดยแปลงให้เทียบได้

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// กำหนด allow list จากหน้าที่ include เข้ามา
if (!isset($ALLOW_ROLES) || !is_array($ALLOW_ROLES)) {
    $ALLOW_ROLES = [];
}

// ดึง role จาก session (ถ้าไม่มีถือว่าไม่ได้ล็อกอิน)
$roleRaw = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// ทำให้การเปรียบเทียบยืดหยุ่น: เทียบทั้งแบบ string และ int
$roleStr = is_null($roleRaw) ? null : (string)$roleRaw;
$roleInt = is_null($roleRaw) ? null : (int)$roleRaw;

$allowStr = array_map('strval', $ALLOW_ROLES);
$allowInt = array_map('intval', $ALLOW_ROLES);

$ok = false;
if (!is_null($roleStr) && in_array($roleStr, $allowStr, true)) {
    $ok = true;
}
if (!is_null($roleInt) && in_array($roleInt, $allowInt, true)) {
    $ok = true;
}

// ถ้าไม่ผ่าน -> เด้งกลับหน้าแรกของไซต์ (ปรับ path ได้ตามต้องการ)
if (!$ok) {
    header('Location: /index.php');
    exit();
}
