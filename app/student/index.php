<?php
require_once '../include/common.php';
require_once '../include/protect_student.php';

$dao = new StudentDAO();
$student = $dao->retrieve($_SESSION['userid']);

$bid_dao = new BidDAO();
$bids = $bid_dao->retrieveByUser($student->userid);

$enrolment_dao = new EnrolmentDAO();
$enrolments = $enrolment_dao->retrieveByUser($student->userid);

$round_dao = new RoundDAO();
$round_num = $round_dao->retrieveRound();
$round_status = $round_dao->retrieveStatus();

$minbid_dao = new MinBidDAO();

function in_enrolments($bid) {
    global $enrolments;
    foreach ($enrolments as $enrolment) {
        if ($enrolment->code == $bid->code && $enrolment->section == $bid->section) {
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
        echo "<h1>BIOS [{$student->name}, {$student->school}]</h1>";
    }
?>
    <p>
        <a href='bid_section.php'>Bid Section</a> |
        <a href='drop_bid.php'>Drop Bid</a> |
        <a href='drop_section.php'>Drop Section</a> |    
        <a href='../logout.php'>Logout</a>
    </p>
    <p>
        Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
        Bidding Round <?=$round_num?>: <big><b><u><?=$round_status?></u></b></big>
    </p>
    <table>
        <tr>
            <th>Course ID</th>
            <th>Section</th>
            <th>Bid Amount</th>
<?php
    $colspan_num = 4;
    if ($round_num == 2 & $round_status == "ACTIVE") {
        echo "<th>Minimum Bid</th>";
        $colspan_num++;
    } 
?>
            <th>Status</th>

<?php
    if (empty($bids)) {
        echo "<tr><td colspan=$colspan_num style='text-align:center;'>No existing bids!</td></tr>";

    } else {
        foreach ($bids as $bid) {

            echo "<tr>
                    <td>{$bid->code}</td>
                    <td>{$bid->section}</td>
                    <td>{$bid->amount}</td>";

            if ($round_status == "ACTIVE") {
                $status = "Pending";

                // for round 2 real-time bid prices
                if ($round_num == 2) {
                    $minbid = $minbid_dao->retrieve($bid->code, $bid->section);
                    echo "<td>$minbid</td>";
                    $status = ($bid->amount >= $minbid) ? "Successful" : "Unsuccessful";
                }

            } else {
                $status = in_enrolments($bid) ? "Success" : "Fail";
            }

            echo "<td>$status</td></tr>";
        }
    }
?>
    
    </table>
</body>
</html>