<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';

    $errors = array();
    if (isset($_POST['course']) && isset($_POST['section']) && isset($_POST['amount'])) {
        $course = $_POST['course'];
        $section = $_POST['section'];
        $amount = $_POST['amount'];

        if (empty($_POST['course'])) {
            $errors[] = "Course cannot be empty";
        }

        if (empty($_POST['section'])) {
            $errors[] = "Section cannot be empty";
        }

        if (empty($_POST['amount'])) {
            $errors[] = "Bid cannot be empty";
        }

        if (!empty($errors)) {
            header("Location: bid_section.php?errors=$errors");
            exit;

        } else {

            $student_dao = new StudentDAO();
            $student = $student_dao->retrieve($_SESSION['userid']);

            $bid_dao = new BidDAO();
            $bids = $bid_dao->retrieveByUser($student->userid);

            $sufficientBal = $amount <= $student->edollar;

            // if ($sufficientBal && 

        }
    }

    if 
?>