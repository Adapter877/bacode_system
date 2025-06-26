<?php
session_start();
include "admin/dbconfig.php"; // แก้ให้ถูกตาม path จริงของไฟล์ config

// ตรวจสอบว่าเข้าสู่ระบบหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบสิทธิ์ role
$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM user_info WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $role);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ถ้าไม่ใช่ role 3 (เช่น Admin)
if ($role == 3) {
    header("Location: /404.php?redirect=about");
    exit();
}
?>

<?php include "header.php"; ?>
    <!-- Page Content -->
    <!-- Banner Starts Here -->
    <div class="main-banner header-text">
      <?php include "top_slider.php"; ?>
     
    </div>
   
    <!-- Banner Ends Here -->
    <section class="blog-posts">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="all-blog-posts">
              <div class="row">
                <?php 

                $sql = "SELECT * FROM posts ORDER BY created_at DESC  LIMIT 6";


                $query = mysqli_query($conn,$sql); 

                while ($row = mysqli_fetch_assoc($query)) {
                  $id =  $row['posts_id'];                                          
                  $posts_title =  $row['posts_title'];                                          
                  $posts_content =  $row['posts_content'];                                          
                  $posts_image =  $row['posts_image'];                                          
                  $category_name =  $row['category_name'];                                          
                  $tag_name =  $row['tag_name'];                                          
                  $author =  $row['author'];
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
                        <li><a href="#"><?php echo date( "d-M-Y", strtotime($created));?></a></li>
                      </ul>
                      <p><?php echo substr($posts_content,0,500)."...";?></p>
                      <div class="post-options">
                        <div class="row">
                          <div class="col-6">
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

<?php } ?>
<?php 

$result = mysqli_num_rows($query);


if ($result>0) {


  ?>

<div class="col-lg-12">
                  <div class="main-button">
                    <a href="blog.php">View All Posts</a>
                  </div>
 </div>

<?php 
}

else {
  ?>

<div class="col-lg-12">
                  <div class="main-button">
                    <a style="background-color:red;" href="javascript:void(0)">No Post Found</a>
                  </div>
 </div>

<?php 
  }

?>
                


              </div>
            </div>
          </div>
          <?php include "right_sidebar.php"; ?>
        </div>
      </div>
    </section>
   
    
    <?php include "footer.php"; ?>