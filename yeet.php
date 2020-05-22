<?php
require_once('config/Database.php');

$conn = new Database();
$invalidlogin = null;

date_default_timezone_set('Asia/Hong_Kong');

function successAttempt($username){
  $conn = new Database();
  $conn->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $conn->query("INSERT INTO login_history SET username = :username, result = 'success'");
  $conn->bind(":username", $username);
  $conn->execute();

}

function failedAttempt($username){
  $conn = new Database();
  $conn->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $conn->query("INSERT INTO login_history SET username = :username, result = 'failed'");
  $conn->bind(":username", $username);
  $conn->execute();

}
session_start();
if(isset($_SESSION['employee_id'])){

      if($_SESSION['acc_type'] == 'Partner'){
        header("location:partner/index.php");
      } else if ($rs['acc_type'] == 'HR') {
        header("location:hr/index.php");
      } else if ($rs['acc_type'] == 'Admin Accounting'){
        header("location:admin-acctg/index.php");
      } else if ($rs['acc_type'] == 'Admin'){
        header("location:admin/index.php");
      } else {
        header("location:user/index.php");
      }

}

// GET CURRENT PAY SCHED FOR TR RECORDING
$current_date = date("Y-m-d");
$conn->query("SELECT payroll_id FROM payroll_schedule 
        WHERE :cdate between payroll_from AND payroll_to");
$conn->bind(":cdate",$current_date);
$rspid = $conn->single();


// GET CURRENT PAY SCHED FOR TR RECORDING
$conn->query("SELECT payroll_id FROM payroll_schedule 
        WHERE :cdate between payroll_from AND payroll_to");
$conn->bind(":cdate",$current_date);
$rsmpid = $conn->single();

$payroll_id = $rspid['payroll_id'];
$mpayroll_id = $rsmpid['payroll_id'];

// $payroll_id = 84;
// $mpayroll_id = 84;

if (isset($_POST['login'])){
  $username=$_POST['username'];

  try{
    $conn = new Database();
    $conn->query("SELECT * FROM accounts a, employees e 
      WHERE a.username = :username
      AND a.passcode = PASSWORD(:password)
      AND e.employee_id = a.employee_id
      AND e.active_employee = 'Y'");

    $conn->bind(':username',$username);
    $conn->bind(':password', $_POST['password']);
    $conn->execute();
    $rs = $conn->single();
    $cnt = $conn->rowCount();
    if($cnt == 1){
      session_start();
      successAttempt($username);
       $_SESSION['account_id'] = $rs['account_id'];
      $_SESSION['employee_id'] = $rs['employee_id'];
      $_SESSION['rank'] = $rs['rank'];
      $_SESSION['username'] = $rs['username'];
      $_SESSION['password'] = $rs['password'];
      $_SESSION['acc_type'] = $rs['acc_type'];
      $_SESSION['payroll_id'] = $payroll_id;

      if($rs['acc_type'] == 'Manager'){
        $conn->query("SELECT * FROM managers WHERE employee_id = :employee_id");
        $conn->bind(':employee_id', $rs['employee_id']);
        $rm = $conn->single();

        $_SESSION['manager_id'] = $rm['manager_id'];
        $_SESSION['mpayroll_id'] = $mpayroll_id;
      }

      if($rs['acc_type'] == 'Partner'){
        $conn->query("SELECT * FROM partners WHERE employee_id = :employee_id");
        $conn->bind(':employee_id', $rs['employee_id']);
        $rp = $conn->single();

        $_SESSION['partner_id'] = $rp['partner_id'];
        $_SESSION['mpayroll_id'] = $mpayroll_id;

        header("location:partner/index.php");
       
      } else if ($rs['acc_type'] == 'HR') {
        header("location:hr/index.php");
      } else if ($rs['acc_type'] == 'Admin Accounting'){
        header("location:admin-acctg/index.php");
      } else if ($rs['acc_type'] == 'Admin'){
        header("location:admin/index.php");
      } else {
        header("location:user/index.php");
      }

    } else {
      $invalidlogin = true;
      $msg="<span style='color:#e74c3c;'>Invalid credentials! :c</span>";
      failedAttempt($username);
    }
  }catch(PDOException $ex){
    echo 'Exception -> ';
    var_dump($ex->getMessage());
  }
}

    //GET POSTED[ADD] PROJECT
    $u = isset($_POST['username']) ? $_POST['username'] : '';

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>TR | Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/skin-red.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/iCheck/square/blue.css">
  <!-- Bootstrap time Picker -->
  <link rel="stylesheet" href="../../plugins/timepicker/bootstrap-timepicker.min.css">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <link rel="icon" href="../dist/img/temp_favicon.ico" type="image/x-icon">

  <!-- Global Site Tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-107582777-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-107582777-1');
  </script>

<style type="text/css">
  body {
    background-color: black;
    background: url(./dist/img/tr_bg.jpg) no-repeat fixed; 
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
}
  
</style>


</head>
<body >
  <div class="login-box">
    <br><br><br>
    <div class="login-logo">
    <!-- <img src="./dist/img/cg-logo-2014.PNG"> <br> -->
    <H1 style="color: #fff;"> <b> TR</b></H1>
    <!-- <h4 style="color: #fff;"> <b> TR BETA (VM)</b></h4> -->
      
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
      <form method="post">
        <div class="form-group has-feedback">
          <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $u; ?>">
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <center>
        <?php
        echo $payroll_id;
        echo '<br>';
        echo $mpayroll_id;
        echo '<br>';

        if(isset($invalidlogin)){
          echo $msg;
        }

        // echo $payroll_id;
        ?>
        </center>
        <br>
        <div class="row">
          <!-- /.col -->
          <div class="col-xs-12">
          <?php

            $function = '';
            if($invalidlogin){
              $function = 'myFunction()';
            } else {
              $function = '';
            }

          ?>
            <button type="submit" id="login" name="login" class="btn btn-block btn-flat <?php echo $function; ?>"
              style="background-image: linear-gradient(to bottom right, #8cbfb7, #2a508f); color: #ffffff;">Log In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-box-body -->
  </div>
  

  <center><img src="./dist/img/powered_by_meiyo2.PNG"></center> <br>

  <!-- jQuery 3 -->
  <script src="bower_components/jquery/dist/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- iCheck -->
  <script src="plugins/iCheck/icheck.min.js"></script>
  <script>
    $(function () {
      $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
    });
  </script>

  <!-- bootstrap time picker -->
<script src="../../plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script>
  //Timepicker
    $('.timepicker').timepicker({
      showInputs: false
    })

  </script>
</body>
</html>
