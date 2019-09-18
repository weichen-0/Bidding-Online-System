<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        
<?php
        if (isset($_SESSION['login'])) {
            echo "<h1>Welcome to BIOS, Admin!</h1>";
            unset($_SESSION['login']);
        } else {
            echo "<h1>BIOS [Admin]</h1>";
        }
?>
        <p>
            <a href='bootstrap.php'>Bootstrap</a> |
            <a href='start_round.php'>Start Round</a> |
            <a href='clear_round.php'>Clear Round</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round: <big><b><u>0</u></b></big>
        </p>
        <!-- <table>
            <tr>
                <th>Course ID</th>
                <th>Section</th>
                <th>Bid Amount</th>
                <th>Status</th>
            </tr>
            
<?php
        // foreach ($bids as $bid) {
        //     echo "<tr>
        //             <td>{$bid->code}</td>
        //             <td>{$bid->section}</td>
        //             <td>{$bid->amount}</td>
        //             <td>Pending</td>
        //         </tr>";
        // }
?> 
        
        </table> -->
    </body>
</html>