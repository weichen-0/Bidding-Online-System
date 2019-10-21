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

<?php
        printErrors();
?>
    </body>
</html>