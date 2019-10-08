<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section'),
            isMissingOrEmpty ('token')];

// to ensure error messages are in alphabetical field order 
if (!empty($_REQUEST('token')) && !verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

$errors[] = isMissingOrEmpty ('userid');
$errors = array_filter($errors);

if (!isEmpty($errors)) {
    $errors = array_values($errors);
    $result = [
        "status" => "error",
        "messages" => $errors
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$course_dao = new CourseDAO();
$student_dao = new StudentDAO();
$section_dao = new SectionDAO();
$round_dao = new RoundDAO();
$enrolment_dao = new EnrolmentDAO();
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

// check if round is active
$round_status = $round_dao->retrieveStatus();
if ($round_status == "INACTIVE") {
    $errors[] = "round not active";
}

if (isEmpty($errors)) {
    $enrolment = $enrolment_dao->retrieve($userid, $code, $section->section);
    if ($enrolment == null) {
        $errors[] = 'no such enrollment record';
    }
}

if (isEmpty($errors)) {
    $result = ["status" => "success"];
    $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar + $enrolment->amount));
    $enrolment_dao->remove($enrolment);
} else {
    $errors = $sort_class->sort_it($errors, "string");
    $result = ["status" => "error",
               "message" => $errors];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
?>