<?php
    require_once '../include/common.php';
    require_once '../include/protect_admin.php';
    
    $round_dao = new RoundDAO();
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
		<h1>BIOS Clear Round</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='bootstrap.php'>Bootstrap</a> |   
            <a href='start_round.php'>Start Round</a> |
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
		</p>

        <table>
            <form action='clear_round.php' method='post'>
                <td><input name='submit' value='Click here to end round!' type='submit' style="width:250px"/></td>
            </form>
        </table>
<?php
    if (isset($_POST['submit'])) {
        if ($round_num == 1 && $round_status == 'ACTIVE') {
            $_SESSION['msg'] = ["Round 1 ended successfully"];
            $round_dao->set(2, 'INACTIVE');
            printMessages();
            return;
        }
        elseif ($round_num == 2 && $round_status == 'ACTIVE') {
            $_SESSION['msg'] = ["Round 2 ended successfully"];
            $round_dao->set(2, 'INACTIVE');
            printMessages();
            return;
        }

        if ($round_status == 'INACTIVE') {
            $_SESSION['errors'] = ["Round $round_num has already ended!"];
            printErrors();
        } 
        
    }
?>
	</body>
</html>

