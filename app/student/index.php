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
$section_dao = new SectionDAO();

// compares all student enrolments with processed round 2 bids to determine which enrolments were from Round 1
function get_r1_enrolments() {
    global $enrolments, $bids;
    $result = array();
    foreach ($enrolments as $enrolment) {
        if (!in_arr($bids, $enrolment)) {
            $result[] = $enrolment;
        }
    }
    return $result;
}

function convertToMinutes($time) {
    $arr = explode(':', $time);
    $min = (int) $arr[0] * 60 + (int) $arr[1];
    return (int) $min;
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
            <th>Round</th>
            <th>Course ID</th>
            <th>Section</th>
            <th>Vacancy</th>
            <th>Bid Amt</th>
<?php
    $colspan_num = 6;
    // add min bid column if active round 2
    if ($round_num == 2 & $round_status == "ACTIVE") {
        echo "<th>Min Bid</th>";
        $colspan_num++;
        process_round(false);
    } 
?>
            <th>Status</th>
        </tr>

<?php
    $all_sections = ["1"=>[], "2"=>[], "3"=>[], "4"=>[], "5"=>[], "6"=>[], "7"=>[]]; // for timetable
    if (empty($bids) && empty($enrolments)) {
        echo "<tr><td colspan='$colspan_num' style='text-align:center;'>No existing bids/enrolments!</td></tr>";

    } else {
        
        if ($round_status == "INACTIVE") {
            // if inactive round 2, display round 1 successful enrolments 
            if ($round_num == 2) {
                $r1_enrolments = get_r1_enrolments();
                $rowspan_num = count($r1_enrolments);

                if ($rowspan_num > 0) {
                    echo "<tr><td rowspan='$rowspan_num'>1</td>";
                    foreach ($r1_enrolments as $r1_enrolment) {
                        $vacancy = $section_dao->retrieve($r1_enrolment->code, $r1_enrolment->section)->size - count($enrolment_dao->retrieveBySection($r1_enrolment->code, $r1_enrolment->section));
                        echo "<td>{$r1_enrolment->code}</td>
                                <td>{$r1_enrolment->section}</td>
                                <td>$vacancy</td>
                                <td>{$r1_enrolment->amount}</td>
                                <td>Success</td>";
                    }
                }
            }
              
            $rowspan_num = count($bids);
            if ($rowspan_num > 0) {
                // display bid status for most recently concluded round
                echo "<tr><td rowspan='$rowspan_num'>$round_num</td>";
                foreach ($bids as $bid) {
                    $vacancy = $section_dao->retrieve($bid->code, $bid->section)->size - count($enrolment_dao->retrieveBySection($bid->code, $bid->section));
                    echo "<td>{$bid->code}</td>
                            <td>{$bid->section}</td>
                            <td>$vacancy</td>
                            <td>{$bid->amount}</td>";

                    $enrolment = $enrolment_dao->retrieve($bid->userid, $bid->code, $bid->section);
                    $status = !is_null($enrolment) ? "Success" : "Fail";
                    echo "<td>$status</td></tr>";
                }
            }
        } else { // active round
            // display round 1 successful enrolments
            if ($round_num == 2) { 
                $rowspan_num = count($enrolments);

                if ($rowspan_num > 0) {
                    echo "<tr><td rowspan='$rowspan_num'>1</td>";
                    foreach ($enrolments as $enrolment) {
                        $vacancy = $section_dao->retrieve($enrolment->code, $enrolment->section)->size - count($enrolment_dao->retrieveBySection($enrolment->code, $enrolment->section));
                        echo "  <td>{$enrolment->code}</td>
                                <td>{$enrolment->section}</td>
                                <td>$vacancy</td>
                                <td>{$enrolment->amount}</td>
                                <td>-</td>
                                <td>Success</td>
                            </tr>";
                    }
                }
            }

            $rowspan_num = count($bids);
            $r2_bids = process_r2_bids();

            if ($rowspan_num > 0) {
                // display bids and their statuses for current round
                echo "<tr><td rowspan='$rowspan_num'>$round_num</td>";
                foreach ($bids as $bid) {        
                    $vacancy = $section_dao->retrieve($bid->code, $bid->section)->size - count($enrolment_dao->retrieveBySection($bid->code, $bid->section));        
                    echo "<td>{$bid->code}</td>
                            <td>{$bid->section}</td>
                            <td>$vacancy</td>
                            <td>{$bid->amount}</td>";

                    $status = "Pending"; // round 1 bid status

                    if ($round_num == 2) {
                        $minbid = $minbid_dao->retrieve($bid->code, $bid->section);
                        echo "<td>$minbid</td>"; // round 2 min bid

                        $course_section_str = $bid->code . ' ' . $bid->section;
                        $successful_bids = $r2_bids[$course_section_str][0];
                        $status = (in_arr($successful_bids, null)) ? "Success" : "Fail"; // round 2 bid status
                    }
                    echo "<td>$status</td></tr>";

                    $section = $section_dao->retrieve($bid->code, $bid->section); // for timetable
                    $all_sections[$section->day][] = ["[Bidded]", $status, $section];
                }
            }
        }
        // for timetable
        foreach ($enrolments as $enrolment) {
            $section = $section_dao->retrieve($enrolment->code, $enrolment->section);
            $all_sections[$section->day][] = ["[Enrolled]", "Success", $section];
        }
    }
?>
    </table>
    </div>
    <br/><br/>
    
    <div style="background-color:darkgrey; display:inline-block;">
    <p style='margin-top:5px; margin-bottom:5px; text-align:center;'><b>Timetable</b></p>
    <table>
        <tr>
            <th></th>
            <th colspan=4 width='87'>0800 - 0900</th>
            <th colspan=4 width='87'>0900 - 1000</th>
            <th colspan=4 width='87'>1000 - 1100</th>
            <th colspan=4 width='87'>1100 - 1200</th>
            <th colspan=4 width='87'>1200 - 1300</th>
            <th colspan=4 width='87'>1300 - 1400</th>
            <th colspan=4 width='87'>1400 - 1500</th>
            <th colspan=4 width='87'>1500 - 1600</th>
            <th colspan=4 width='87'>1600 - 1700</th>
            <th colspan=4 width='87'>1700 - 1800</th>
            <th colspan=4 width='87'>1800 - 1900</th>
        </tr>
<?php
    $sort_class = new Sort();
    $day_arr = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

    // display days of the week and their respective sections
    for ($i = 1; $i <= 7; $i++) {
        $day = $day_arr[$i - 1];
        echo "<tr><th height='46'>$day</th>";
        $time = convertToMinutes("8:00"); // to track the printing of table cells

        $day_sections = $sort_class->sort_it($all_sections[$i], "timetable_time"); // sort sections of each day according to their start time
        foreach ($day_sections as $day_section) {
            $section = $day_section[2];
            $start_time = convertToMinutes($section->start);

            // print out empty cells until the start of first section of the day
            while ($time < $start_time) {
                echo "<td></td>";
                $time += 15;
            }
            
            // background color for the table cells depending on their bid status
            $status = $day_section[0];
            if ($day_section[1] == "Success") {
                $color_style = "style='background-color:yellowgreen;'";
            } else if ($day_section[1] == "Fail") {
                $color_style = "style='background-color:indianred;'";
            } else {
                $color_style = "style='background-color:#FFAF00;'";
            }

            // calculation of how long the section lasts to print appropriate number of cells
            $end_time = convertToMinutes($section->end);
            $colspan_num = (int) ($end_time - $start_time) / 15;
            $time = $end_time;
            echo "<td colspan='$colspan_num' $color_style align='center'><b>$section->course $section->section<br/>$status</b></td>";
        }
        // if sections dont last till to the end of the day, print out remaining empty cells
        while ($time < convertToMinutes("19:00")) {
            echo "<td></td>";
            $time += 15;
        }
        echo "</tr>";
    }

?>
    

</body>
</html>