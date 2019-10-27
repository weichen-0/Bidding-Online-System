<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/process_round_logic.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('amount'),
            isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section'),
            isMissingOrEmpty ('token')];

// to ensure error messages are in alphabetical field order 
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
$course_dao = new CourseDAO();
$section_dao = new SectionDAO();
$student_dao = new StudentDAO();
$round_dao = new RoundDAO();
$bid_dao = new BidDAO();
$prereq_dao = new PrereqDAO();
$course_completed_dao = new CourseCompletedDAO();
$enrolment_dao = new EnrolmentDAO();
$minbid_dao = new MinBidDAO();
$sort_class = new Sort();

// ================== INPUT VALIDATION ===================
// check if amount is a positive number (>= 10) and not more than 2dp
$amt = $request['amount'];
$more_than_2dp = preg_match('/\.\d{3,}/', $amt);
if (!is_numeric($amt) || $amt < 10 || $more_than_2dp) {
    $errors[] = "invalid amount";
}

// check if course code is in record
$code = $request['course'];
$course = $course_dao->retrieve($code);
if ($course == null) {
    $errors[] = "invalid course";

// IF COURSE VALID, check if section is in record
} else {
    $section = $section_dao->retrieve($code, $request['section']);
    if ($section == null) {
        $errors[] = "invalid section";
    }
}

// check if userid is in record
$userid = $request['userid'];
$student = $student_dao->retrieve($userid);
if ($student == null) {
    $errors[] = "invalid userid";
}

if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "message" => $errors
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// ================== LOGICAL VALIDATION ===================
$round_status = $round_dao->retrieveStatus();
// check if there is an active round, output error immediately if yes
if ($round_status == 'INACTIVE') {
    $errors[] = 'round ended';
    $result = ["status" => "error",
               "message" => $errors];
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$round_num = $round_dao->retrieveRound();
$bids = $bid_dao->retrieveByUser($userid);
$min_bid = $minbid_dao->retrieve($section->course, $section->section);

// check if student has a previous bid for the same course (whether update is required)
$prev_bid = null;
foreach ($bids as $bid) {
    if ($bid->code == $code) {
        $prev_bid = $bid;
        break;
    }
}

if (!is_null($prev_bid)) {
    $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar + $prev_bid->amount));
    $bid_dao->remove($prev_bid);
    $bids = $bid_dao->retrieveByUser($userid);
}

// if active round 1, check if student is bidding for course under their school
if ($round_num == 1 && $student->school != $course->school) {
    $errors[] = 'not own school course';

// if active round 2, check if bid amount is less than minimum bid
} else if ($round_num == 2 && $amt < $min_bid) {
    $errors[] = 'bid too low';
}

// check if there is vacancy left in the bidded section
$vacancy = $section->size;
$section_enrolments = $enrolment_dao->retrieveBySection($code, $request['section']);
if (($vacancy - count($section_enrolments)) <= 0) {
    $errors[] = 'no vacancy';
}

// check if student has already enrolled into section in previous round
$user_enrolments = $enrolment_dao->retrieveByUser($userid);
foreach ($user_enrolments as $enrolment) {
    if ($enrolment->code == $code) {
        $errors[] = 'course enrolled';
        break;
    }
}

// check if class timing clashes with all previously bidded sections
foreach ($bids as $bid) {
    $prev_section = $section_dao->retrieve($bid->code, $bid->section);
    if ($section->classClashWith($prev_section)) {
        $errors[] = "class timetable clash";
        break;
    }
}

$enrolments = $enrolment_dao->retrieveByUser($student->userid);
// check if class timing clashes with all previously enrolled sections
if (!in_array("class timetable clash", $errors)) {
    foreach ($enrolments as $enrolment) {
        $enrolled_section = $section_dao->retrieve($enrolment->code, $enrolment->section);
        if ($section->classClashWith($enrolled_section)) {
            $errors[] = "class timetable clash";
            break;
        }
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

// check if exam timing clashes with all previously enrolled courses
if (!in_array("exam timetable clash", $errors)) {
    foreach ($enrolments as $enrolment) {
        $enrolled_course = $course_dao->retrieve($enrolment->code);
        if ($course->examClashWith($enrolled_course)) {
            $errors[] = "exam timetable clash";
            break;
        }
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

// check if student has enough edollars whether updating bid or not
$insuff_edollar_with_refund = (!is_null($prev_bid) && ($prev_bid->amount + $student->edollar) < $amt);
$insuff_edollar_without_refund = (is_null($prev_bid) && $amt > $student->edollar);
if ($insuff_edollar_with_refund || $insuff_edollar_without_refund) {
    $errors[] = "not enough e-dollar";
}

// check if student has already bidded/enrolled for 5 sections
$section_total = count($enrolments) + count($bids);
if ((!is_null($prev_bid) && $section_total > 5) || $section_total >= 5) {
    $errors[] = "section limit reached";
}

if (isEmpty($errors)) {
    $result = ["status" => "success"];

    $bid_dao->add(new Bid($userid, $amt, $code, $section->section));
    $student = $student_dao->retrieve($userid);
    $student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar - $amt));

    // for real-time status of round 2
    if ($round_num == 2) {
        process_round(false);
    }

} else {
    $errors = $sort_class->sort_it($errors, "string");
    $result = ["status" => "error",
               "message" => $errors];
    
    // add back previous bid if any error encountered since it was removed at the start
    if (!is_null($prev_bid)) {
        $bid_dao->add($prev_bid);
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;
?>