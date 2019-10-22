<?php
require_once '../include/common.php';
require_once '../include/protect_student.php';
require_once '../include/process_round_logic.php';

$student_dao = new StudentDAO();
$student = $student_dao->retrieve($_SESSION['userid']);

$bid_dao = new BidDAO();
$bids = $bid_dao->retrieveByUser($student->userid);

$enrolment_dao = new EnrolmentDAO();
$enrolments = $enrolment_dao->retrieveByUser($student->userid);

$round_dao = new RoundDAO();
$round_num = $round_dao->retrieveRound();
$round_status = $round_dao->retrieveStatus();

$minbid_dao = new MinBidDAO();

function in_bid_arr($bid_arr) {
    global $student;
    foreach ($bid_arr as $bid) {
        if ($student->userid == $bid->userid) {
            return true;
        }
    }
    return false;
}
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="../include/style.css">
</head>
<body>
    
<?php
    if (isset($_SESSION['login'])) {
        echo "<h1>Welcome to BIOS, {$student->name}!</h1>";
        unset($_SESSION['login']);
    } else {
        echo "<h1>Bidding Online System [{$student->name}, {$student->school}]</h1>";
    }
?>
    <p>
        <a href='bid_section.php'>Bid Section</a> |
        <a href='drop_bid_section.php'>Drop Bid/Section</a> |
        <a href='../logout.php'>Logout</a>
    </p>
    <p>
        Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
        Bidding Round <?=$round_num?>: <big><b><u><?=$round_status?></u></b></big>
    </p>
    <div style="background-color:darkgrey; display:inline-block;">
    <p style='margin-top:5px; margin-bottom:5px; text-align:center;'><b>Bidding Results</b></p>
    <table>
        <tr>
            <th>Course ID</th>
            <th>Section</th>
            <th>Bid Amt</th>
<?php
    $colspan_num = 4;
    // add min bid column if active round 2
    if ($round_num == 2 & $round_status == "ACTIVE") {
        echo "<th>Min Bid</th>";
        $colspan_num++;
    } 
?>
            <th>Status</th>
        </tr>

<?php
    if (empty($bids) && empty($enrolments)) {
        echo "<tr><td colspan=$colspan_num style='text-align:center;'>No existing bids/enrolments!</td></tr>";

    } else {
        // display round 1 successful enrolments if active round 2
        if ($round_num == 2 & $round_status == "ACTIVE") {
            foreach ($enrolments as $enrolment) {
                echo "<tr>
                        <td>{$enrolment->code}</td>
                        <td>{$enrolment->section}</td>
                        <td>{$enrolment->amount}</td>
                        <td>-</td>
                        <td>Success</td>
                    </tr>";
            }
        }

        // display bids and their statuses for this round
        foreach ($bids as $bid) {
            echo "<tr>
                    <td>{$bid->code}</td>
                    <td>{$bid->section}</td>
                    <td>{$bid->amount}</td>";

            if ($round_status == "ACTIVE") {
                $status = "Pending"; // round 1 bid status

                if ($round_num == 2) {
                    $minbid = $minbid_dao->retrieve($bid->code, $bid->section);
                    echo "<td>$minbid</td>"; // round 2 min bid

                    $course_section_str = $bid->code . ' ' . $bid->section;
                    $successful_bids = process_r2_bids()[$course_section_str][0];
                    $status = (in_bid_arr($successful_bids)) ? "Success" : "Fail"; // round 2 bid status
                }

            // when bidding round is inactive
            } else {
                $enrolment = $enrolment_dao->retrieve($bid->userid, $bid->code, $bid->section);
                $status = !is_null($enrolment) ? "Success" : "Fail";
            }

            echo "<td>$status</td></tr>";
        }
    }
?>
        </div>
    
    </table>
</body>
</html>