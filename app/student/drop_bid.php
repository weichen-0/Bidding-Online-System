<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';

    $dao = new StudentDAO();
    $student = $dao->retrieve($_SESSION['userid']);

    $round_dao = new RoundDAO();
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS Drop Bid</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='bid_section.php'>Bid Section</a> |
            <a href='drop_section.php'>Drop Section</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big><br/>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>
        <form method='POST' action='drop_bid_process.php'>
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
                <td colspan='2'>
                    <input name='Drop' type='submit' />
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