<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';

    $dao = new StudentDAO();
    $student = $dao->retrieve($_SESSION['userid']);
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>Welcome <?=$student->name?>!</h1>
        <p>
            <a href='../logout.php'>Logout</a>
        </p>
    </body>

</html>