<?php
include "../admin/dbconfig.php";
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: all_posts.php");
    exit();
}

$id = (int)$_GET['id'];

// ใช้ prepared statement เพื่อความปลอดภัย
$stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE posts_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: all_posts.php");
exit();
