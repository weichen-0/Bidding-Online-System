<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    $student_dao = new StudentDAO();
    $student = $student_dao->retrieve($_SESSION['userid']);

    $round_dao = new RoundDAO();
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
                </tr>
<?php
        $section_dao = new SectionDAO();
        $section_dict = array();

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
                echo "<td>{$section->section}</td>
                    <td>{$section->day}</td>
                    <td>{$section->start}</td>
                    <td>{$section->end}</td>
                    <td>{$section->instructor}</td>
                    <td>{$section->venue}</td>
                    <td>{$section->size}</td></tr>";
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