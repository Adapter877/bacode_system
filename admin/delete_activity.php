<?php
session_start();
include __DIR__ . "/dbconfig.php";

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $activity_id = intval($_GET['id']);

    // ตรวจสอบว่า record มีอยู่จริงก่อนลบ (optional)
    $check_sql = "SELECT * FROM student_activities WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $activity_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีข้อมูล ลบข้อมูล
        $delete_sql = "DELETE FROM student_activities WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $activity_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            // ลบสำเร็จ
            $_SESSION['message'] = "✅ ลบกิจกรรมเรียบร้อยแล้ว";
        } else {
            // ลบไม่สำเร็จ
            $_SESSION['error'] = "❌ ไม่สามารถลบกิจกรรมได้: " . mysqli_error($conn);
        }

        mysqli_stmt_close($delete_stmt);
    } else {
        $_SESSION['error'] = "❌ ไม่พบกิจกรรมที่ต้องการลบ";
    }

    mysqli_stmt_close($check_stmt);
} else {
    $_SESSION['error'] = "❌ รหัสกิจกรรมไม่ถูกต้อง";
}

// กลับไปหน้ารายการกิจกรรม
header("Location: all_bacode.php");
exit;
?>
