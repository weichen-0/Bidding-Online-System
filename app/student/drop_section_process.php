<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    $round_dao = new RoundDAO();

    // if bidding round is inactive, students are not allowed to drop any bids
    if ($round_dao->retrieveStatus() == 'INACTIVE') {
        $_SESSION['errors'] = ['Bidding round not active'];
        header("Location: drop_section.php");
        exit;
    }

    $errors = array();

    $course = $_POST['course'];
    $section = $_POST['section'];

    if (empty($course)) {
        $errors[] = "Course cannot be empty";
    }

    if (empty($section)) {
        $errors[] = "Section cannot be empty";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: drop_section.php");
        exit;
    } 

    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($_SESSION['userid']);

    $enrolment_dao = new EnrolmentDAO();
    $enrolment = $enrolment_dao->retrieve($student->userid, $course, $section);

    // checks if the student is enrolled in that section in the first place
    if ($enrolment != null) {
        $enrolled_dao->remove($enrolment);

        $updatedBal = $student->edollar + $enrolment->amount;
        $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
        $student_dao->update($studentNew);

        $_SESSION['msg'] = ["Section successfully dropped for {$course} {$section}!"];

    } else {
        $_SESSION['errors'] = ["Invalid course ID and section!"];
    }

    header("Location: drop_section.php");
    exit;

?>