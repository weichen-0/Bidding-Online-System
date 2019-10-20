<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($_SESSION['userid']);

    $round_dao = new RoundDAO();
    $section_dao = new SectionDAO();
    $course_dao = new CourseDAO();

    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieveByUser($student->userid);

    $enrolment_dao = new EnrolmentDAO();
    $enrolments = $enrolment_dao->retrieveByUser($student->userid);

    // check if section have exam or class sections with list of bidded sections
    function clashWithBids($section) {
        global $section_dao;
        global $course_dao;
        global $bids;
        $course = $course_dao->retrieve($section->course);

        foreach ($bids as $bid) {
            $bid_section = $section_dao->retrieve($bid->code, $bid->section);
            $bid_course = $course_dao->retrieve($bid->code);
            if ($bid_section->classClashWith($section) || $bid_course->examClashWith($course)) {
                return true;
            }
        }
        return false;
    }

    // check if section have exam or class sections with list of enrolled sections
    function clashWithEnrolments($section) {
        global $section_dao;
        global $course_dao;
        global $enrolments;
        $course = $course_dao->retrieve($section->course);

        foreach ($enrolments as $enrolment) {
            $enrolled_section = $section_dao->retrieve($enrolment->code, $enrolment->section);
            $enrolled_course = $course_dao->retrieve($enrolment->code);
            if ($enrolled_section->classClashWith($section) || $enrolled_course->examClashWith($course)) {
                return true;
            }
        }
        return false;
    }

    function notOwnSchool($section) {
        global $round_dao;
        global $student;
        $course_dao = new CourseDAO();
        $course = $course_dao->retrieve($section->course);
        return $round_dao->retrieveRound() == 1 && $student->school != $course->school;
    }

    function courseBidded($section) {
        global $enrolments;
        foreach ($enrolments as $enrolment) {
            if ($enrolment->code == $section->course) {
                return true;
            }
        }
        return false;
    }
    
    function prereqIncomplete($section) {
        global $student;
        $prereq_dao = new PrereqDAO();
        $prereqs = $prereq_dao->retrieve($section->course);
        $course_completed_dao = new CourseCompletedDAO();
        $completed_courses = $course_completed_dao->retrieve($student->userid);

        foreach ($prereqs as $prereq) {
            if (!in_array($prereq, $completed_courses)) {
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
        <h1>BIOS Bid Section</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='drop_bid.php'>Drop Bid</a> |
            <a href='drop_section.php'>Drop Section</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>

        <div style="overflow-y:auto; max-height:300px;">
        <table>
                <tr>
                    <th>Course ID</td>
                    <th>Section</td>
                    <th>Day</td>
                    <th>Start</td>
                    <th>End</td>
                    <th>Instructor</td>
                    <th>Venue</td>
                    <th>Size</td>
                    <th>Vacancy</td>
                </tr>
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
                    <td rowspan='$num_of_sections'>$key</td>";
            foreach ($list as $section) {           
                $num_enrolment = count($enrolment_dao->retrievebySection($section->course, $section->section));
                $vacancy = $section->size - $num_enrolment;

                $strike_start = "";
                $strike_end = "";
                if (clashWithBids($section) || clashWithEnrolments($section) || notOwnSchool($section) || courseBidded($section) || prereqIncomplete($section) || $vacancy === 0) {
                    $strike_start = "<strike>";
                    $strike_end = "</strike>";
                }

                echo "<td>$strike_start{$section->section}$strike_end</td>
                <td>$strike_start{$days[$section->day - 1]}$strike_end</td>
                <td>$strike_start{$section->start}$strike_end</td>
                <td>$strike_start{$section->end}$strike_end</td>
                <td>$strike_start{$section->instructor}$strike_end</td>
                <td>$strike_start{$section->venue}$strike_end</td>
                <td>$strike_start{$section->size}$strike_end</td>
                <td>$strike_start{$vacancy}$strike_end</td></tr>";
            }                    
        }
?>
        </table>        
        </div>
        <br/>
        
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
            if (isset($_SESSION['msg'])) {
                printMessages();
            } else {
                printErrors();
            }
?>
        </p>

    </body>
</html>