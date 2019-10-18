<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section'),
            isMissingOrEmpty ('token') ];

if (!empty($_REQUEST['token']) && !verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

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
$course_dao = new CourseDAO();
$course = $course_dao->retrieve($request['course']);

$err_msg = array();

if ($course == null) {
    $err_msg[] = "invalid course";

} else {
    $section_dao = new SectionDAO();
    $section = $section_dao->retrieve($request['course'], $request['section']);
    
    if ($section == null) {
        $err_msg[] = "invalid section";

    } else {
        $enrolment_dao = new EnrolmentDAO();
        $enrolments = $enrolment_dao->retrieveBySection($request['course'], $request['section']);

        $sort_class = new Sort();
        $enrolments = $sort_class->sort_it($enrolments, "section_dump");

        $enrolment_result = array();
        for ($i = 0; $i < count($enrolments); $i++) {
            $enrolment = $enrolments[$i];

            $enrolment_result[] = ["userid" => $enrolment->userid,
                                   "amount" => (float) number_format($enrolment->amount, 1)]; // STILL NOT WORKING
        }
    }
}

if (empty($err_msg)) {
    $result = ["status" => "success",
               "students" => $enrolment_result];
} else {
    $result = ["status" => "error",
               "message" => $err_msg];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;

?>