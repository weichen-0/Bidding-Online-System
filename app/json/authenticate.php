<?php

require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('password'), 
            isMissingOrEmpty ('username')];
$errors = array_filter($errors);

// common json validation
if (!isEmpty($errors)) {

    $result = [
        "status" => "error",
        "message" => array_values($errors)
        ];
}
else{
    $request = json_decode($_REQUEST['r'], true);
    $userid = $request['username'];
    $password = $request['password'];

    if ($userid == 'admin' && $password == 'skulked4154]campsite') {
        $result = [
            "status" => "success",
            "token" => generate_token($userid)
        ];

    } else {
        // Only return EITHER 'invalid username' OR 'invalid password', username has precedence if both fields are wrong
        $errors[] = ($userid != 'admin') ? "invalid username" : "invalid password";
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