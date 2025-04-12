<?php 
include "header.php"; 

// ตรวจสอบการเข้าสู่ระบบ
session_start();

// ฟังก์ชันตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (isset($_SESSION['user_id'])) {
    // ผู้ใช้ล็อกอินแล้ว
    $role = $_SESSION['role']; // สมมติว่า role เก็บใน session
} else {
    // ผู้ใช้ยังไม่ได้ล็อกอิน
    $role = null;
}

?>

<!-- Page Content -->
<!-- Banner Starts Here -->
<div class="heading-page header-text">
    <section class="page-heading">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-content">
                        <h4>Post Details</h4>
                        
                        <?php 
                        $post = $_REQUEST['post'];

                        $sql = "SELECT * FROM posts WHERE posts_title LIKE '%$post%'";
                        $query = mysqli_query($conn, $sql); 

                        while ($row = mysqli_fetch_assoc($query)) {
                            $id = $row['posts_id'];                                          
                            $posts_title = $row['posts_title'];                                          
                            $posts_content = $row['posts_content'];                                          
                            $posts_image = $row['posts_image'];                                          
                            $category_name = $row['category_name'];                                          
                            $tag_name = $row['tag_name'];                                          
                            $author = $row['author'];
                            $created = $row['created_at']; 
                        }
                        ?>
                        <h2><?php echo $posts_title;?></h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Banner Ends Here -->

<section class="blog-posts grid-system">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="all-blog-posts">
                    <div class="row">

                    <?php 
                    if (isset($_REQUEST['post'])) {

                        $post = $_REQUEST['post'];

                        $sql = "SELECT * FROM posts WHERE posts_title LIKE '%$post%'";

                        $query = mysqli_query($conn, $sql); 

                        $result = mysqli_num_rows($query);

                        if ($result > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $id = $row['posts_id'];                                          
                                $posts_title = $row['posts_title'];                                          
                                $posts_content = $row['posts_content'];                                          
                                $posts_image = $row['posts_image'];                                          
                                $category_name = $row['category_name'];                                          
                                $tag_name = $row['tag_name'];                                          
                                $author = $row['author'];
                                $created = $row['created_at']; 

                                ?>
                                <div class="col-lg-12">
                                    <div class="blog-post">
                                        <div class="blog-thumb">
                                            <img src="admin/<?php echo $posts_image;?>" alt="<?php echo $posts_image;?>"  >
                                        </div>
                                        <div class="down-content">
                                            <span><a style="color:#f48840;" href="category.php?category=<?php echo $category_name;?>"><?php echo $category_name;?></a></span>
                                            <a href="post-details.php?post=<?php echo $posts_title;?> "><h4><?php echo $posts_title;?></h4></a>
                                            <ul class="post-info">
                                                <li><a href="#"><?php echo $author;?></a></li>
                                                <li><a href="#"><?php echo date("d-M-Y", strtotime($created));?></a></li>
                                            </ul>
                                            <p><?php echo $posts_content;?></p>
                                            <div class="post-options">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <ul class="post-tags">
                                                            <li><i class="fa fa-tags"></i></li>
                                                            <li><a href="tag.php?tag=<?php echo $tag_name;?>"><?php echo $tag_name;?></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php 
                            } 
                        } else {
                            echo "<h1>No data found</h1>";
                        }
                    }
                    ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
// แสดงปุ่ม Login หากไม่ได้ล็อกอิน
if (!$role) {
    echo '<a href="login.php" class="btn btn-primary">Login</a>';
} elseif ($role == 0) {
    // ถ้า role = 0 แสดงเนื้อหาหลังจากนี้
    echo '<a href="dashboard.php" class="btn btn-primary">Admin Dashboard</a>';
}
?>

<?php include "footer.php"; ?>
