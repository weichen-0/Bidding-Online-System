<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';

    $dao = new StudentDAO();
    $student = $dao->retrieve($_SESSION['userid']);

    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieve($student->userid);
?>
<html>
    <body>
    <?php
        $courseid = $_POST["courseId"];
        $section = $_POST["section"];
        foreach ($bids as $bid) {
            if ($bid->code == $courseid && $bid->section) {
                $amt = $student->edollar + $bid->amount;
                $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $amt);
                $dao->update($studentNew);
                $bid_dao->remove($student->userid, $courseid, $section);
                header("Location: drop_bid.php?msg=Successfully dropped bid");
                exit;
            }
            else {
                $_SESSION['errors'][] = "Error! Bid has not yet been placed";
                header("Location: drop_bid.php");
                exit;
            }
        }
        
        
?>
        <!-- <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big>
        </p> -->




    </body>

</html>