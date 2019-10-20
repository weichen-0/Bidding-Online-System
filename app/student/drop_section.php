<?php
require_once '../include/common.php';
require_once '../include/protect_student.php';

$dao = new StudentDAO();
$student = $dao->retrieve($_SESSION['userid']);

$round_dao = new RoundDAO();
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS Drop Section</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='bid_section.php'>Bid Section</a> |
            <a href='drop_bid.php'>Drop Bid</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>

        <div style="overflow-y:auto; max-height:300px;">
        <table width=248px>
                <tr>
                    <th>Course ID</td>
                    <th>Section</td>
                    <th>Amount</td>
                </tr>
<?php
        $enrolment_dao = new EnrolmentDAO();
        $enrolments = $enrolment_dao->retrieveByUser($student->userid);

        if (empty($enrolments)) {
            echo "<tr><td colspan=3 style='text-align:center;'>No existing enrolments!</td></tr>";
        } else {
            foreach ($enrolments as $enrolment) {
                echo "<tr>
                        <td>{$enrolment->code}</td>
                        <td>{$enrolment->section}</td>
                        <td>{$enrolment->amount}</td>";
            }     
        }               
?>
        </table>        
        </div>
        <br/>

        <form method='POST' action='drop_section_process.php'>
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
                <th colspan='2'>
                    <input name='drop' type='submit'/>
                </th>
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