<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';

    $dao = new StudentDAO();
    $student = $dao->retrieve($_SESSION['userid']);

    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieve($student->userid);
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>Welcome to BIOS, <?=$student->name?>!</h1>
        <p>
            <a href='bid_section.php'>Bid Section</a> |
            <a href='drop_bid.php'>Drop Bid</a> |
            <a href='drop_section.php'>Drop Section</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Account Balance: <big><b><u>e$<?=$student->edollar?></u></b></big>
        </p>
        <table>
            <tr>
                <th>Course ID</th>
                <th>Section</th>
                <th>Bid Amount</th>
                <th>Status</th>
            </tr>

<?php
        foreach ($bids as $bid) {
            echo "<tr>
                    <td>{$bid->code}</td>
                    <td>{$bid->section}</td>
                    <td>{$bid->amount}</td>
                    <td>...</td>
                </tr>";
        }
?>
        
        </table>

    </body>

</html>