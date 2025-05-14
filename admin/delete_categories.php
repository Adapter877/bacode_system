<?php
// เชื่อมต่อกับฐานข้อมูล
include "dbconfig.php";

// ตรวจสอบว่า id ถูกส่งมาหรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ตรวจสอบว่า ID หมวดหมู่มีในฐานข้อมูลหรือไม่
    $check_sql = "SELECT * FROM categories WHERE category_id = ?";
    if ($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id); // Binding parameter
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // ถ้าไม่พบข้อมูลในฐานข้อมูล
        if (mysqli_num_rows($result) == 0) {
            echo "<script>alert('Category not found!'); window.location.href='all_categories.php';</script>";
            exit();
        }
    }

    // คำสั่ง SQL เพื่อลบหมวดหมู่
    $sql = "DELETE FROM categories WHERE category_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id); // Binding parameter
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Category deleted successfully!'); window.location.href='all_categories.php';</script>";
        } else {
            echo "<script>alert('Error deleting category!'); window.location.href='all_categories.php';</script>";
        }
    } else {
        echo "<script>alert('Database query failed!'); window.location.href='all_categories.php';</script>";
    }
} else {
    echo "<script>alert('No category ID specified!'); window.location.href='all_categories.php';</script>";
}
?>
