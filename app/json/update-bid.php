<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('amount'),
            isMissingOrEmpty ('course'),
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
$section_dao = new SectionDAO();
$student_dao = new StudentDAO();
$round_dao = new RoundDAO();
$bid_dao = new BidDAO();
$prereq_dao = new PrereqDAO();
$course_completed_dao = new CourseCompletedDAO();
$enrolment_dao = new EnrolmentDAO();
$sort_class = new Sort();

// ================== INPUT VALIDATION ===================
// check if amount is a positive number (>= 10) and not more than 2dp
$amt = $_REQUEST['amount'];
$more_than_2dp = preg_match('/\.\d{3,}/', $amt);
if (!is_numeric($amt) || $amt < 10 || $more_than_2dp) {
    $errors[] = "invalid amount";
}

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

if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "messages" => $errors
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// ================== LOGICAL VALIDATION ===================
// check if there is an active round
$round_status = $round_dao->retrieveStatus();
$round_num = $round_dao->retrieveRound();
if ($round_status == 'INACTIVE') {
    $errors[] = 'round ended';

// if round 1, check if student is bidding for course under their school
} else if ($round_num == 1 && $student->school != $course->school) {
    $errors[] = 'not own school course';

} 
// if round 2, check if amount is more than minimum bid
// else if ($round_num == 2 && ...) {
//     $errors[] = 'bid too low';
// }

// check if there is vacancy left in the bidded section
$vacancy = $section->size;
$section_enrolments = $enrolment_dao->retrieveBySection($code, $_REQUEST['section']);
if (($vacancy - count($section_enrolments)) <= 0) {
    $errors[] = 'no vacancy';
}

// check if student has already enrolled into section in previous round
foreach ($section_enrolments as $enrolment) {
    if ($enrolment->userid == $userid) {
        $errors[] = 'course enrolled';
        break;
    }
}

// check if class timing clashes with all previously bidded sections
$bids = $bid_dao->retrieveByUser($userid);
foreach ($bids as $bid) {
    $prev_section = $section_dao->retrieve($bid->code, $bid->section);
    if ($section->classClashWith($prev_section)) {
        $errors[] = "class timetable clash";
        break;
    }
}

// check if exam timing clashes with all previously bidded courses
foreach ($bids as $bid) {
    $prev_course = $course_dao->retrieve($bid->code);
    if ($course->examClashWith($prev_course)) {
        $errors[] = "exam timetable clash";
        break;
    }
}

// check if student has completed prerequisites for course
$prereqs = $prereq_dao->retrieve($code);
$courses_completed = $course_completed_dao->retrieve($userid);
foreach ($prereqs as $prereq) {
    if (!in_array($prereq, $courses_completed)) {
        $errors[] = "incomplete prerequisites";
        break;
    }
}

// check if student has already completed this course
if (in_array($code, $courses_completed)) {
    $errors[] = "course completed";
}

// check if student has a previous bid for the same course (whether update is required)
$prev_bid = null;
foreach ($bids as $bid) {
    if ($bid->code == $code) {
        $prev_bid = $bid;
        break;
    }
}

// check if student has enough edollars whether updating bid or not
$insuff_edollar_with_refund = (!is_null($prev_bid) && ($prev_bid->amount + $student->edollar) < $amt);
if ($insuff_edollar_with_refund || $amt > $student->edollar) {
    $errors[] = "not enough e-dollar";
}

// check if student has already bidded for 5 sections
if ((!is_null($prev_bid) && count($bids) > 5) || count($bids) >= 5) {
    $errors[] = "section limit reached";
}

if (isEmpty($errors)) {
    $result = ["status" => "success"];

    if (!is_null($prev_bid)) {
        $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar + $prev_bid->amount));
        $bid_dao->remove($prev_bid);
    }

    $bid_dao->add(new Bid($userid, $amt, $code, $section->section));
    $student = $student_dao->retrieve($userid);
    $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar - $amt));

} else {
    $errors = $sort_class->sort_it($errors, "string");
    $result = ["status" => "error",
               "message" => $errors];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
?>