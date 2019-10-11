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

        <div style="overflow-y:auto; max-height:300px;">
        <table width=248px>
                <tr>
                    <th>Course ID</td>
                    <th>Section</td>
                    <th>Amount</td>
                </tr>
<?php
        $bid_dao = new BidDAO();
        $bids = $bid_dao->retrieveByUser($student->userid);

        if (empty($bids)) {
            echo "<tr><td colspan=3 style='text-align:center;'>No existing bids!</td></tr>";
        } else {
            foreach ($bids as $bid) {
                echo "<tr>
                        <td>{$bid->code}</td>
                        <td>{$bid->section}</td>
                        <td>{$bid->amount}</td>";
            }             
        }       
?>
        </table>        
        </div>
        <br/>

        <form method='POST' action='drop_bid_process.php'>
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
                    <input name='Drop' type='submit' />
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