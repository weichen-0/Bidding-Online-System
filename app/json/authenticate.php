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
        "messages" => array_values($errors)
        ];
}
else{
    $userid = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    
    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($userid);

    if ($student == null) {
        $errors[] = "invalid username";
    } else if (!$student->authenticate($password)) {
        $errors[] = "invalid password";
    }

    if (isEmpty($errors)) {
        $result = [
            "status" => "success",
            "token" => generate_token($userid)
        ];
    } else {
        // $errors[] = "invalid username/password";
        $result = [
            "status" => "error",
            "messages" => $errors
            ];
    }
}
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
 
?>