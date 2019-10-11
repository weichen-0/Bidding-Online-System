<?php

require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('username'), 
            isMissingOrEmpty ('password')];
$errors = array_filter($errors);

// common json validation
if (!isEmpty($errors)) {

    $result = [
        "status" => "error",
        "message" => array_values($errors)
        ];
}
else{
    $userid = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    if ($userid == 'admin' && $password == 'skulked4154]campsite') {
        $result = [
            "status" => "success",
            "token" => generate_token($userid)
        ];

    } else {
        $errors[] = "invalid username/password";
        $result = [
            "status" => "error",
            "message" => $errors
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
 
?>