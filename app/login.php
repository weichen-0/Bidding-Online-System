<?php
require_once 'include/common.php';

$error = '';

// first condition checks if there is any error message in SESSION
if (!isset($_SESSION['errors']) && isset($_POST['userid']) && isset($_POST['password']) ) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // if admin login is valid, direct to admin homepage
    if ($userid == 'admin' && $password == 'skulked4154]campsite') {
        $_SESSION['userid'] = $userid;
        $_SESSION['login'] = true;
        header("Location: admin/index.php");
        exit;
    }

    $dao = new StudentDAO();
    $student = $dao->retrieve($userid);

    // if student login is valid, direct to student homepage
    if ( $student != null && $student->authenticate($password) ) {
        $_SESSION['userid'] = $userid; 
        $_SESSION['login'] = true;
        header("Location: student/index.php");
        exit;
    } 

    $_SESSION['errors'] = ['Invalid username or password!'];

    
}
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="include/style.css">
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body style='background:
radial-gradient(black 15%, transparent 16%) 0 0,
radial-gradient(black 15%, transparent 16%) 8px 8px,
radial-gradient(rgba(255,255,255,.1) 15%, transparent 20%) 0 1px,
radial-gradient(rgba(255,255,255,.1) 15%, transparent 20%) 8px 9px;
background-color:slategray;
background-size:16px 16px;'>
    
    <div style='text-align:center; border:3px solid black; width: 600px; height:500px; margin:0 auto; margin-top: 130px;'>
    
        <div style='width: 50%; height:500px; float:left; overflow:hidden'>
        <img src='https://cdn3.vectorstock.com/i/1000x1000/54/27/merlion-statue-on-blue-vector-21355427.jpg' style='float:center; height:550px; margin: -9px 0 0 -47px;'>
        </div>

    
        <div style='width: 50%; height:500px; background-color:white; float:left;'>
            <h1 style='text-align:center;'><b>Merlion University</b></h1>
            <br/><br/>

            <form class="login100-form" method='POST' action='login.php'>
                <span class="login100-form-title">
                    <small>Bidding Online System</small>
                </span>

                <div class="wrap-input100" data-validate = "Valid email is required: ex@abc.xyz">
                    <input class="input100" type="text" name="userid" placeholder="User ID">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-user"  aria-hidden="true"></i>
                    </span>
                </div>

                <div class="wrap-input100" data-validate = "Password is required">
                    <input class="input100" type="password" name="password" placeholder="Password" type='password'>
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </span>
                </div>
                
                <div class="container-login100-form-btn">
                    <button class="login100-form-btn" name='login' type='submit'>
                        Login
                    </button>
                </div>
            </form>
<?php
    printErrors();
?>    
        </div>
    </div>

</body>
</html>