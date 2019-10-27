<?php
require_once '../include/common.php';
require_once '../include/protect_student.php';

$student_dao = new StudentDAO();
$student = $student_dao->retrieve($_SESSION['userid']);

$round_dao = new RoundDAO();
$section_dao = new SectionDAO();
$enrolment_dao = new EnrolmentDAO();

function validateSection($section) {
    global $section_dao, $student, $vacancy, $enrolment_dao, $round_dao;

    $course_dao = new CourseDAO();
    $course = $course_dao->retrieve($section->course);

    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieveByUser($student->userid);

    $enrolments = $enrolment_dao->retrieveByUser($student->userid);

    // check if student has already bidded for 5 sections
    if (count($bids) + count($enrolments) >= 5) {
        return false;
    }

    // check for previous bids under same course
    foreach ($bids as $bid) {
        if ($section->course == $bid->code) {
            return false;
        }
    }
    // check for previous enrolment in same course
    foreach ($enrolments as $enrolment) {
        if ($section->course == $enrolment->code) {
            return false;
        }
    }

    // check for clash with bids
    foreach ($bids as $bid) {
        $bid_section = $section_dao->retrieve($bid->code, $bid->section);
        $bid_course = $course_dao->retrieve($bid->code);
        if ($bid_section->classClashWith($section) || $bid_course->examClashWith($course)) {
            return false;
        }
    }

    // check for clash with enrolments
    foreach ($enrolments as $enrolment) {
        $enrolled_section = $section_dao->retrieve($enrolment->code, $enrolment->section);
        $enrolled_course = $course_dao->retrieve($enrolment->code);
        if ($enrolled_section->classClashWith($section) || $enrolled_course->examClashWith($course)) {
            return false;
        }
    }

    // check for remaining section vacancies
    if ($vacancy <= 0) {
        return true;
    }

    $prereq_dao = new PrereqDAO();
    $prereqs = $prereq_dao->retrieve($section->course);

    $course_completed_dao = new CourseCompletedDAO();
    $completed_courses = $course_completed_dao->retrieve($student->userid);

    // check if student has fulfilled pre-requisite courses
    foreach ($prereqs as $prereq) {
        if (!in_array($prereq, $completed_courses)) {
            return false;
        }
    }

    // check for course bids under own school
    return $round_dao->retrieveRound() == 2 || $student->school == $course->school;
}       
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>Bidding Online System (Bid Section)</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='drop_bid_section.php'>Drop Bid/Section</a> |
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>
    
        <div style="background-color:darkgrey; display:inline-block;">
            <p style='margin-top:5px; margin-bottom:5px; text-align:center; text'><b>Scroll table to see all available courses</b></p>
            <table>
                    <tr>
                        <th width='74'>Course ID</td>
                        <th width='50'>Section</td>
                        <th width='30'>Day</td>
                        <th width='36'>Start</td>
                        <th width='36'>End</td>
                        <th width='115'>Instructor</td>
                        <th width='114'>Venue</td>
                        <th width='28'>Size</td>
                        <th width='57'>Vacancy</td>
                    </tr>
            </table>

            <div style='overflow-y:auto; max-height:350px;'>
                <table>
<?php
                $section_dict = array();
                $days = ["Mon", "Tue", "Wed", "Thu", "Fri"];

                foreach ($section_dao->retrieveAll() as $section) {
                    if (isset($section_dict[$section->course])) {
                        $section_dict[$section->course][] = $section;
                    } else {
                        $section_dict[$section->course] = [$section];
                    }
                }

                foreach ($section_dict as $key => $list) {
                    $num_of_sections = count($list);
                    echo "<tr>
                            <td width='74'rowspan='$num_of_sections'>$key</td>";
                    foreach ($list as $section) {   
                        $section_enrolments = $enrolment_dao->retrieveBySection($section->course, $section->section);
                        $vacancy = $section->size - count($section_enrolments);
                        $isValid = validateSection($section);

                        $error_style = "";
                        if (!$isValid) {
                            $error_style = "style='background-color:firebrick'";
                        }

                        echo "<td width='50' $error_style>{$section->section}</td>
                        <td width='30'>{$days[$section->day - 1]}</td>
                        <td width='36'>{$section->start}</td>
                        <td width='36'>{$section->end}</td>
                        <td width='115'>{$section->instructor}</td>
                        <td width='114'>{$section->venue}</td>
                        <td width='28'>{$section->size}</td>
                        <td width='57'>{$vacancy}</td></tr>";
                    }                    
                }
?>
                </table>        
            </div>
        </div>

        <br/><br/>
        
        <form method='POST' action='bid_section_process.php'>
        <table>
            <tr>
                <th>Course ID</th>
                <th>
                    <input name='course'/>
                </th>
            </tr>
            <tr>
                <th>Section</th>
                <th>
                    <input name='section'/>
                </th>
            </tr>
            <tr>
                <th>Bid (e$)</th>
                <th>
                    <input name='amount'/>
                </th>
            </tr>
            <tr>
                <th colspan='2'>
                    <input name='submit' type='submit' />
                </td>
            </tr>       
        </table>
        </form>

        <p>
<?php
    printMessages();
    printErrors();
?>
        </p>

    </body>
</html>