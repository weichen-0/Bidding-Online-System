<?php
require_once 'include/common.php';

unset($_SESSION['userid']);
header("Location: ../login.php");
exit;

?>
