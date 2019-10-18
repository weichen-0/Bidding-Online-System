<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('token') ];

if (!empty($_REQUEST['token']) && !verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

$errors[] = isMissingOrEmpty ('userid');
$errors = array_filter($errors);

if (!isEmpty($errors)) {
    $errors = array_values($errors);
    $result = [
        "status" => "error",
        "message" => $errors
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$request = json_decode($_REQUEST['r'], true);
$student_dao = new StudentDAO();
$student = $student_dao->retrieve($request['userid']);

if ($student == null) {
    $result = ["status" => "error",
                "message" => ["invalid userid"]];
} else {
    $result = ["status" => "success",
                "userid" => $student->userid,
                "password" => $student->password,
                "name" => $student->name,
                "school" => $student->school,
                "edollar" => (float) number_format($student->edollar, 1)]; // STILL NOT WORKING
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;

?>