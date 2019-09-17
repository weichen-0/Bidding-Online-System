<?php
require_once 'include/common.php';
require_once 'include/token.php';

$error = '';

// To catch 'Please login!' errors from protect.php
if (isset($_GET['error']) ) {
    $error = $_GET['error'];

} elseif (isset($_POST['userid']) && isset($_POST['password']) ) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // if admin login is valid, direct to admin homepage
    if ($userid == 'admin' && $password == 'admin') {
        $_SESSION['userid'] = $userid;
        header("Location: admin/index.php");
        exit;
    }

    $dao = new StudentDAO();
    $student = $dao->retrieve($userid);

    // if student login is valid, direct to student homepage
    if ( $student != null && $student->authenticate($password) ) {
        $_SESSION['userid'] = $userid; 
        header("Location: student/index.php");
        exit;

    } 

    $error = 'Incorrect username or password!';
}
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>BIOS Login</h1>
        <form method='POST' action='login.php'>
            <table>
                <tr>
                    <td>User ID</td>
                    <td>
                        <input name='userid' />
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password' />
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Login' type='submit' />
                    </td>
                </tr>
            </table>             
        </form>

        <div class='error'>
            <p>
                <?=$error?>
            </p>
        </span>
    </body>
</html>