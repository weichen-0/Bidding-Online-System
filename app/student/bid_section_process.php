<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    $round_dao = new RoundDAO();

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
    
    // checks if course and section actually exists
    if ($sectionObj == null) {
        $_SESSION['errors'] = ["Invalid course ID and section!"];
        header("Location: bid_section.php");
        exit;
    }

    // checks if it is bidding round 1, if yes then students only allowed to bid for courses under their own school
    if ($round_dao->retrieveRound() == 1 && $student->school != $courseObj->school) {
        $_SESSION['errors'] = ["Only courses under your school ({$student->school}) are biddable in Round 1!"];
        header("Location: bid_section.php");
        exit;
    }

    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($_SESSION['userid']);

    if (is_numeric($amount)) {
        // checks if bid amount is more than e$10
        if ($amount < 10) {
            $errors[] = "Minimum bid is e$10";
        }

        // check if amount is not more than 2dp
        if (preg_match('/\.\d{3,}/', $amount)) {
            $errors[] = "Bid should have no more than 2 decimal places";
        }

        // checks if student has enough e$
        if ($amount > $student->edollar) {
            $errors[] = "Insufficient e$ balance";
        }
    } else {
        $errors[] = "Bid must be a numeric value";
    }

    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieveByUser($student->userid);
    
    // checks if student has already bidded for 5 sections
    if (count($bids) >= 5) {
        $errors[] = "Only a maximum of 5 bids allowed";
    }

    // checks if student has bidded for another section under the same course
    foreach ($bids as $bid) {
        if ($course == $bid->code) {
            $errors[] = "Only 1 bid per course allowed";
            break;
        }
    }

    // check for clash in class timetables
    foreach ($bids as $bid) {
        $biddedSection = $section_dao->retrieve($bid->code, $bid->section);
        if ($sectionObj->classClashWith($biddedSection)) {
            $errors[] = "Class timetable clashes with other bids";
            break;
        }
    }

    $course_dao = new CourseDAO();
    $courseObj = $course_dao->retrieve($course);

    // check for clash in exam timetables
    foreach($bids as $bid) {
        $biddedCourse = $course_dao->retrieve($bid->code);
        if ($courseObj->examClashWith($biddedCourse)) {
            $errors[] = "Exam timetable clashes with other bids";
            break;
        }
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

        $_SESSION['msg'] = ["Bid successfully placed for {$course} {$section}!"];
    }

    header("Location: bid_section.php");
    exit;
?>