<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    $round_dao = new RoundDAO();

    // if bidding round is inactive, students are not allowed to drop any bids
    if ($round_dao->retrieveStatus() == 'INACTIVE') {
        $_SESSION['errors'] = ['Bidding round not active'];
        header("Location: drop_bid.php");
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
        header("Location: drop_bid.php");
        exit;
    } 

    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($_SESSION['userid']);

    $bid_dao = new BidDAO();
    $bid = $bid_dao->retrieve($student->userid, $course, $section);

    // checks if the student placed such a bid before
    if ($bid != null) {
        $bid_dao->remove($bid);

        $updatedBal = $student->edollar + $bid->amount;
        $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
        $student_dao->update($studentNew);

        $_SESSION['msg'] = ["Bid successfully dropped for {$course} {$section}!"];

    } else {
        $_SESSION['errors'] = ["Invalid course ID and section!"];
    }

    header("Location: drop_bid.php");
    exit;

?>