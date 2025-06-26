<?php include "header.php";

if ($_SESSION['role'] != 0){
    echo "<script>window.location.href='index.php'</script>";
}

$sql = "SELECT * FROM tags";
$query = mysqli_query($conn,$sql);

?>

<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <?php include "sidebar.php"; ?>
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">

                        <div class="page-body">
                            <div class="card">
                                <div class="card-header">
                                    <h3>แท็กทั้งหมด</h3>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="table-inverse">
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>ชื่อแท็ก</th>
                                                    <th>วันที่สร้าง</th>
                                                    <th>ลบ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                            $i=1;
                                            while ($row = mysqli_fetch_assoc($query)) {
                                                $id =  $row['id'];                                          
                                                $name =  $row['name'];
                                                $created = $row['created_at'];                                         
                                            ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo $name; ?></td>
                                                    <td><?php echo date( "d-m-Y", strtotime($created)); ?></td>
                                                    <td>
                                                        <a type="submit" href="delete_tag.php?id=<?php echo $id ?>">
                                                            <button class="btn btn-danger"><i class="ti-trash"></i></button>
                                                        </a>
                                                    </td>
                                                </tr>
                                              <?php } ?>  
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="styleSelector"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>