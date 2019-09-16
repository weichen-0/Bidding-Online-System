<?php
require_once 'include/common.php';

unset($_SESSION['username']);
header("Location: login.php");

?>
