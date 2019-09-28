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
        <form method='POST' action='bid_section_process.php'>
        <table>
            <tr>
                <td>Course ID</td>
                <td>
                    <input name='course'/>
                </td>
            </tr>
            <tr>
                <td>Section</td>
                <td>
                    <input name='section'/>
                </td>
            </tr>
            <tr>
                <td>Bid (e$)</td>
                <td>
                    <input name='amount'/>
                </td>
            </tr>
            <tr>
                <td colspan='2'>
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