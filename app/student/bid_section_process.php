<?php
require_once '../include/common.php';
require_once '../include/protect_student.php';    
require_once '../include/process_round_logic.php';

$round_dao = new RoundDAO();
$round_num = $round_dao->retrieveRound();

// if bidding round is inactive, students are not allowed to bid for sections
if ($round_dao->retrieveStatus() == 'INACTIVE') {
    $_SESSION['errors'] = ['Bidding round not active'];
    header("Location: bid_section.php");
    exit;
}

$errors = array();

$course = $_POST['course'];
$section = $_POST['section'];
$amount = $_POST['amount'];

if (empty($course)) {
    $errors[] = "Course cannot be empty";
}

if (empty($section)) {
    $errors[] = "Section cannot be empty";
}

if (empty($amount)) {
    $errors[] = "Bid cannot be empty";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: bid_section.php");
    exit;
}

$section_dao = new SectionDAO();
$sectionObj = $section_dao->retrieve($course, $section);

$student_dao = new StudentDAO();
$student = $student_dao->retrieve($_SESSION['userid']);

$course_dao = new CourseDAO();
$courseObj = $course_dao->retrieve($course);

// checks if course and section actually exists
if ($sectionObj == null) {
    $_SESSION['errors'] = ["Invalid course ID and section!"];
    header("Location: bid_section.php");
    exit;
}

// checks if it is bidding round 1, if yes then students only allowed to bid for courses under their own school
if ($round_num == 1 && $student->school != $courseObj->school) {
    $_SESSION['errors'] = ["Only courses under your school ({$student->school}) are biddable in Round 1!"];
    header("Location: bid_section.php");
    exit;
}

$minbid_dao = new MinBidDAO();

if (is_numeric($amount)) {
    // check if bid amount is less than section min bid
    $minbid = $minbid_dao->retrieve($course, $section);
    if ($amount < $minbid) {
        $errors[] = "$course $section minimum bid is e$$minbid";
    }

    // check if amount is not more than 2dp
    if (preg_match('/\.\d{3,}/', $amount)) {
        $errors[] = "Bid should have no more than 2 decimal places";
    }

    // check if student has enough e$
    if ($amount > $student->edollar) {
        $errors[] = "Insufficient e$ balance";
    }
} else {
    $errors[] = "Bid must be a numeric value";
}

$bid_dao = new BidDAO();
$bids = $bid_dao->retrieveByUser($student->userid);

$enrolment_dao = new EnrolmentDAO();
$enrolments = $enrolment_dao->retrieveByUser($student->userid);

// checks if student has already bidded for 5 sections
if (count($bids) + count($enrolments) >= 5) {
    $errors[] = "Only a maximum of 5 bidded/enrolled sections allowed";
}

$hasBidded = false;
// checks if student has bidded for the course
foreach ($bids as $bid) {
    if ($sectionObj->course == $bid->code) {
        $errors[] = "Only 1 bid per course allowed";
        $hasBidded = true;
        break;
    }
}

$hasEnrolled = false;
// checks if student is already enrolled in the course
foreach ($enrolments as $enrolment) {
    if ($sectionObj->course == $enrolment->code) {
        $errors[] = "Already enrolled in $enrolment->code";
        $hasEnrolled = true;
        break;
    }
}

if (!$hasBidded) {
    // check for clash in class timetables with previously bidded sections
    foreach ($bids as $bid) {
        $biddedSection = $section_dao->retrieve($bid->code, $bid->section);
        if ($sectionObj->classClashWith($biddedSection)) {
            $errors[] = "Class timetable clashes with $bid->code $bid->section";
            break;
        }
    }

    // check for clash in exam timetables with previously bidded courses
    foreach($bids as $bid) {
        $biddedCourse = $course_dao->retrieve($bid->code);
        if ($courseObj->examClashWith($biddedCourse)) {
            $errors[] = "Exam timetable clashes with $bid->code";
            break;
        }
    }
}

if (!$hasEnrolled) {
    // check for clash in class timetables with previously enrolled sections
    foreach ($enrolments as $enrolment) {
        $enrolled_section = $section_dao->retrieve($enrolment->code, $enrolment->section);
        if ($sectionObj->classClashWith($enrolled_section)) {
            $errors[] = "Class timetable clashes with $enrolment->code $enrolment->section";
            break;
        }
    }

    // check for clash in exam timetables with previously enrolled courses
    foreach($enrolments as $enrolment) {
        $enrolled_course = $course_dao->retrieve($enrolment->code);
        if ($courseObj->examClashWith($enrolled_course)) {
            $errors[] = "Exam timetable clashes with $enrolment->code";
            break;
        }
    }
}

$enrolments = $enrolment_dao->retrieveBySection($course, $section);
$vacancy = $sectionObj->size - count($enrolments);
// check for remaining section vacancies
if ($vacancy <= 0) {
    $errors[] = "No vacancies left for $course $section";
}


$prereq_dao = new PrereqDAO();
$prereqs = $prereq_dao->retrieve($course);

$course_completed_dao = new CourseCompletedDAO();
$completed_courses = $course_completed_dao->retrieve($student->userid);

// check if student has fulfilled pre-requisite courses
foreach ($prereqs as $prereq) {
    if (!in_array($prereq, $completed_courses)) {
        $errors[] = "Pre-requisite courses not fulfilled";
        break;
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;

} else { 
    $bid_dao->add(new Bid($student->userid, $amount, $course, $section));
    
    $updatedBal = $student->edollar - $amount;
    $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
    $student_dao->update($studentNew);

    $_SESSION['msg'] = ["Bid successfully placed for $course $section!"];
    
    // for real-time status of round 2
    if ($round_num == 2) {
        process_round(false);
    }
}

header("Location: bid_section.php");
exit;
?>