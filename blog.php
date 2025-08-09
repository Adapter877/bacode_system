<?php include "header.php"; ?>

<style>
/* ====== Card layout fixed ====== */
.post-col { display: flex; }                      /* ให้ column ดันการ์ดสูงเท่ากัน */
.blog-post {
  display: flex; flex-direction: column;
  width: 100%;
  max-width: 520px;                                /* กันการ์ดไม่กว้างเกิน */
  margin: 0 auto 30px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,.06);
  overflow: hidden;
  min-height: 560px;                               /* << ความสูงการ์ดคงที่ */
}
.blog-thumb img{
  width: 100%;
  height: 190px;                                   /* << ความสูงภาพคงที่ */
  object-fit: cover;
  display: block;
}
.down-content{
  display: flex; flex-direction: column;
  padding: 16px 16px 10px;
  flex: 1;                                         /* ให้เนื้อหายืดเต็ม */
}
/* หัวข้อคงที่ 2 บรรทัด */
.post-title{
  font-size: 20px; font-weight: 700; margin: 6px 0 8px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  min-height: 52px;                                /* กันเด้งเมื่อหัวข้อสั้น */
}
/* meta */
.post-info { margin: 0 0 10px; }
.post-info li{ color:#6b7280; }
/* เส้นแบ่งบางๆ */
.hr-soft{ height:1px; background:#eef2f7; margin: 10px 0; border:0; }
/* คำเกริ่นคงที่ 3 บรรทัด */
.post-excerpt{
  color:#475569; margin: 6px 0 0;
  display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
  min-height: 66px;
}
/* ส่วนท้ายติดล่างเสมอ */
.post-options{
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px solid #eef2f7;
}
.post-tags{ margin-bottom:0; }
</style>

<!-- Page Content -->
<div class="heading-page header-text">
  <section class="page-heading">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="text-content">
            <h4>โพสต์ล่าสุด</h4>
            <h2>รายการโพสต์ล่าสุดของเรา</h2>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<section class="blog-posts grid-system">
  <div class="container">
    <div class="row justify-content-center">
      <?php 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 10;
        $offset   = ($page - 1) * $per_page;

        $sql   = "SELECT * FROM posts ORDER BY created_at DESC LIMIT $offset, $per_page";
        $query = mysqli_query($conn,$sql); 

        while ($row = mysqli_fetch_assoc($query)) {
          $id            = $row['posts_id'];                                          
          $posts_title   = $row['posts_title'];                                          
          $posts_content = $row['posts_content'];                                          
          $posts_image   = $row['posts_image'];                                          
          $category_name = $row['category_name'];                                          
          $tag_name      = $row['tag_name'];                                          
          $author        = $row['author'];
          $created       = $row['created_at']; 
      ?>
      <div class="col-xl-4 col-lg-4 col-md-6 post-col">
        <article class="blog-post h-100">
          <div class="blog-thumb">
            <img src="admin/<?php echo $posts_image;?>" alt="<?php echo htmlspecialchars($posts_title, ENT_QUOTES, 'UTF-8'); ?>">
          </div>

          <div class="down-content">
            <span>
              <a style="color:#f48840;" href="category.php?category=<?php echo urlencode($category_name); ?>">
                <?php echo htmlspecialchars($category_name); ?>
              </a>
            </span>

            <a href="post-details.php?post=<?php echo urlencode($posts_title); ?>">
              <h4 class="post-title"><?php echo htmlspecialchars($posts_title); ?></h4>
            </a>

            <ul class="post-info">
              <li><a href="#"><?php echo htmlspecialchars($author); ?></a></li>
              <li><a href="#"><?php echo date("d-M-Y", strtotime($created)); ?></a></li>
            </ul>

            <hr class="hr-soft">

            <p class="post-excerpt">
              <?php echo htmlspecialchars(mb_substr(strip_tags($posts_content),0,180))."..."; ?>
            </p>

            <div class="post-options">
              <div class="d-flex justify-content-between align-items-center">
                <ul class="post-tags">
                  <li><i class="fa fa-tags"></i></li>
                  <li>
                    <a href="tag.php?tag=<?php echo urlencode($tag_name); ?>"><?php echo htmlspecialchars($tag_name); ?></a>
                  </li>
                </ul>
                <a href="enroll.php?post_id=<?php echo (int)$id; ?>" class="btn btn-sm btn-primary">
                  สมัครเข้าร่วม
                </a>
              </div>
            </div>
          </div>
        </article>
      </div>
      <?php } ?>

      <div class="col-lg-12">
        <ul class="page-numbers">
          <?php 
            $sql2        = "SELECT COUNT(*) AS c FROM posts";
            $query2      = mysqli_query($conn,$sql2); 
            $rowc        = mysqli_fetch_assoc($query2);
            $total_page  = (int)$rowc['c'];
            $page_number = (int)ceil($total_page/$per_page);

            for ($i=1; $i <= $page_number; $i++) { 
              $page_active = ($page==$i) ? 'active' : '';
          ?>
          <li class="<?php echo $page_active;?>">
            <a href="blog.php?page=<?php echo $i;?>"><?php echo $i;?></a>
          </li>
          <?php } ?>
        </ul>
      </div>

    </div>
  </div>
</section>

<?php include "footer.php"; ?>
