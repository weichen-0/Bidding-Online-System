<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('userid'),
            isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section')];


// to ensure error messages are in alphabetical field order 
if (!empty($_REQUEST['token']) && !verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

$errors = array_filter($errors);


if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "message" => $errors
        ];
    $errors = array_values($errors);

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$course_dao = new CourseDAO();
$student_dao = new StudentDAO();
$section_dao = new SectionDAO();
$round_dao = new RoundDAO();
$bid_dao = new BidDAO();
$sort_class = new Sort();

// check if course code is in record
$code = $_REQUEST['course'];
$course = $course_dao->retrieve($code);
if ($course == null) {
    $errors[] = "invalid course";

// IF COURSE VALID, check if section is in record
} else {
    $section = $section_dao->retrieve($code, $_REQUEST['section']);
    if ($section == null) {
        $errors[] = "invalid section";
    }
}

// check if userid is in record
$userid = $_REQUEST['userid'];
$student = $student_dao->retrieve($userid);
if ($student == null) {
    $errors[] = "invalid userid";
}

// check if round has already ended
$round_status = $round_dao->retrieveStatus();
if ($round_status == "INACTIVE") {
    $errors[] = "round ended";
}

if (isEmpty($errors)) {
    $bid = $bid_dao->retrieve($userid, $code, $section->section);
    if ($bid == null) {
        $errors[] = 'no such bid';
    }
}

if (isEmpty($errors)) {
    $result = ["status" => "success"];
    $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar + $bid->amount));
    $bid_dao->remove($bid);
} else {
    $errors = $sort_class->sort_it($errors, "string");
    $result = ["status" => "error",
               "message" => $errors];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
?>