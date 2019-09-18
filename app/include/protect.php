<?php
require_once 'token.php';
require_once 'common.php';

$userid = '';
if  (isset($_SESSION['userid'])) {
	$userid = $_SESSION['userid'];
	return;
}

# check if the username session variable has been set 
# send user back to the login page with the appropriate message if it was not

# add your code here 

$_SESSION['errors'] = ['Please login!'];
header("Location: ../login.php");
exit;

?>