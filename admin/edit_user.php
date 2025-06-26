<?php include "header.php"; 
if (!$_SESSION['role']== 0){

    echo "<script>window.location.href='index.php'</script>";
}
$id =$_REQUEST['id'];

$sql = "SELECT * FROM user_info WHERE id = '$id'";

$query = mysqli_query($conn,$sql);
while ($row = mysqli_fetch_assoc($query)) {
    $id =  $row['id'];                                          
    $name =  $row['name'];                                          
    $username = $row['username'];
    $email = $row['email'];                                            
    $role = $row['role'];     
}

if (isset($_POST['submit'])) {

    $id =mysqli_real_escape_string($conn,$_POST['id']);
    $name =mysqli_real_escape_string($conn,$_POST['name']);
    $u_name = mysqli_real_escape_string($conn,$_POST['username']);
    $email =mysqli_real_escape_string($conn,$_POST['email']);
    $password =mysqli_real_escape_string($conn,$_POST['password']);

    $pass = mysqli_real_escape_string($conn,md5($password));

    $role =mysqli_real_escape_string($conn,$_POST['select']);

    $sql= "SELECT * FROM user_info WHERE username ='$u_name'"; 

    $query = mysqli_query($conn,$sql);

    $result = mysqli_num_rows($query);
  
    $error="";
    $success="";

    if ($u_name == $username ) {

        $sql2 ="UPDATE user_info SET name='$name', email='$email',password='$pass',role ='$role' WHERE  id = '$id'";
        $query3 = mysqli_query($conn,$sql2);
        echo "<script>window.location.href='all_users.php'</script>";

        }
        else{

            $sql3= "SELECT * FROM user_info WHERE username ='$u_name'"; 

            $query3 = mysqli_query($conn,$sql3); 

            $result3 = mysqli_num_rows($query3);

            if ($result3 == 1) {

                $error = "ชื่อผู้ใช้งานมีอยู่แล้ว";
             
            }

            else{

                $sql4 ="UPDATE user_info SET  name='$name', username='$u_name',email='$email',password='$pass',role ='$role' WHERE  id = '$id'";
                $query3 = mysqli_query($conn,$sql4);
                echo "<script>window.location.href='all_users.php'</script>";

            }

        }

    }

?>
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <?php include "sidebar.php"; ?>
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">

                        <div class="page-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <!-- Basic Form Inputs card start -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h3>แก้ไขผู้ใช้งาน</h3>
                                            <?php 
                                            if (isset($_POST['submit'])) {
                                                echo"<h2>$error</h2>";                                               
                                          
                                            }
                                            ?>
                                        </div>
                                        <div class="card-block">
                                            <h4 class="sub-title">รายละเอียดข้อมูล</h4>
                                            <form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">ชื่อ</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="name"
                                                            placeholder="กรอกชื่อของคุณ" spellcheck="false" value="<?php echo $name; ?>"
                                                            data-ms-editor="true">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">ชื่อผู้ใช้งาน</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="username"
                                                            placeholder="กรอกชื่อผู้ใช้งาน" spellcheck="false" value="<?php echo $username; ?>"
                                                            data-ms-editor="true">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">อีเมล</label>
                                                    <div class="col-sm-10">
                                                        <input type="email" class="form-control" name="email"
                                                            placeholder="กรอกอีเมลของคุณ" spellcheck="false" value="<?php echo $email; ?>"
                                                            data-ms-editor="true">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">รหัสผ่าน</label>
                                                    <div class="col-sm-10">
                                                        <input type="password" class="form-control" name="password" value="<?php echo $password; ?>"
                                                            placeholder="กรอกรหัสผ่าน">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">เลือกบทบาท</label>
                                                    <div class="col-sm-10">
                                                        <select name="select" class="form-control">
                                                            <option value="option">เลือกสิทธิ์</option>
                                                            <option value="0" <?php if($role == 0) echo "selected"; ?>>ผู้ดูแลระบบ</option>
                                                            <option value="1" <?php if($role == 1) echo "selected"; ?>>สโมสรนักศึกษา</option>
                                                            <option value="3" <?php if($role == 3) echo "selected"; ?>>นักศึกษา</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary waves-effect waves-light"
                                                    name="submit">ยืนยัน</button>

                                            </form>
                                        </div>

                                    </div>
                                </div>

                                <div id="styleSelector">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php include "footer.php"; ?>
