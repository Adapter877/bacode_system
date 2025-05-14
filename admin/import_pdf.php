<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

include __DIR__ . '/../admin/dbconfig.php';  // เพิ่ม path ที่ถูกต้องไปยัง dbconfig.php

// ตรวจสอบการอัปโหลด
if (isset($_POST['submit'])) {
    // รับไฟล์ PDF จากฟอร์ม
    $studentFile = $_FILES['students_pdf']['tmp_name'];
    $activityFile = $_FILES['activities_pdf']['tmp_name'];

    // ตรวจสอบว่าไฟล์ถูกอัปโหลดมา
    if (empty($studentFile) || empty($activityFile)) {
        die("โปรดอัปโหลดไฟล์ PDF ทั้งสองไฟล์");
    }

    // ใช้ PdfParser ในการ解析 PDF
    $parser = new Parser();

    // อ่านข้อมูลจากไฟล์ PDF
    $studentPdf = $parser->parseFile($studentFile);
    $activityPdf = $parser->parseFile($activityFile);

    // ดึงข้อความจากไฟล์ PDF
    $studentText = $studentPdf->getText();
    $activityText = $activityPdf->getText();

    // แยกข้อความออกเป็นบรรทัดๆ
    $studentLines = array_filter(array_map('trim', explode("\n", $studentText)));
    $activityLines = array_filter(array_map('trim', explode("\n", $activityText)));

    // ตรวจสอบจำนวนบรรทัด
    $countStudents = count($studentLines);
    $countActivities = count($activityLines);

    // ตรวจสอบว่าไฟล์ทั้งสองมีจำนวนบรรทัดเท่ากันหรือไม่
    if ($countStudents !== $countActivities) {
        die("จำนวนบรรทัดไม่ตรงกันระหว่างรายชื่อนักศึกษาและกิจกรรม");
    }

    // แสดงข้อมูล PDF ให้ผู้ใช้ตรวจสอบก่อน
    echo "<h4>ข้อมูลจากไฟล์ PDF ที่ตรวจสอบแล้ว:</h4>";

    echo "<h5>รายชื่อนักศึกษา:</h5>";
    echo "<pre>" . implode("\n", $studentLines) . "</pre>";

    echo "<h5>ข้อมูลกิจกรรม:</h5>";
    echo "<pre>" . implode("\n", $activityLines) . "</pre>";

    // เก็บข้อมูลใน SESSION เพื่อใช้เมื่อยืนยัน
    $_SESSION['studentLines'] = $studentLines;
    $_SESSION['activityLines'] = $activityLines;

    // ให้ผู้ใช้ยืนยันการนำเข้าข้อมูล
    echo "
    <form method='POST'>
        <input type='submit' name='confirm' value='ยืนยันการนำเข้าข้อมูล'>
    </form>";
} 

// เมื่อกดยืนยัน
if (isset($_POST['confirm'])) {
    // ดึงข้อมูลที่เก็บไว้ใน SESSION
    $studentLines = $_SESSION['studentLines'];
    $activityLines = $_SESSION['activityLines'];

    // ตรวจสอบจำนวนบรรทัด
    $countStudents = count($studentLines);
    $countActivities = count($activityLines);

    // ตัวแปรนับจำนวนที่สำเร็จและไม่สำเร็จ
    $successCount = 0;
    $errorCount = 0;

    // วนลูปผ่านแต่ละบรรทัดในไฟล์ PDF
    for ($i = 0; $i < $countStudents; $i++) {
        // แยกข้อมูลของนักศึกษา
        $studentParts = array_map('trim', explode(',', $studentLines[$i]));
        if (count($studentParts) < 6) {
            $errorCount++;
            continue;
        }

        [$student_name, $student_id, $major, $contact, $barcode, $date_joined] = $studentParts;

        // แยกข้อมูลของกิจกรรม
        $activityParts = array_map('trim', explode(',', $activityLines[$i]));
        if (count($activityParts) < 2) {
            $errorCount++;
            continue;
        }

        [$activity_name, $activity_hours] = $activityParts;

        // เตรียมคำสั่ง SQL สำหรับการนำเข้าข้อมูล
        $stmt = $conn->prepare("INSERT INTO student_activities (student_name, student_id, major, contact, barcode, date_joined, activity_name, activity_hours) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $student_name, $student_id, $major, $contact, $barcode, $date_joined, $activity_name, $activity_hours);

        // ทำการ execute คำสั่ง SQL
        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
        }

        // ปิด statement
        $stmt->close();
    }

    // แสดงผลลัพธ์
    echo "<div style='padding:20px'>";
    echo "<h4>✅ นำเข้าข้อมูลสำเร็จ: $successCount รายการ</h4>";
    if ($errorCount > 0) {
        echo "<h5>⚠️ ไม่สามารถนำเข้าได้: $errorCount รายการ</h5>";
    }
    echo "<a href='add_bacode.php' class='btn btn-secondary mt-3'>กลับ</a>";
    echo "</div>";
}
?>
