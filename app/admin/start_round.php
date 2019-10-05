<?php
    require_once '../include/common.php';
    require_once '../include/protect_admin.php';

    $round_dao = new RoundDAO();
    $round_num = $round_dao->retrieveRound();
    $round_status = $round_dao->retrieveStatus()
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
		<h1>BIOS Start Round</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='bootstrap.php'>Bootstrap</a> |   
            <a href='clear_round.php'>Clear Round</a> |
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
		</p>
        <table>
            <form action='start_round.php' method='post'>
                <td><input name='submit' value='Click here to start round!' type='submit' style="width:250px"/></td>
            </form>
        </table>
<?php
    if (isset($_POST['submit'])) {

        if ($round_status == 'ACTIVE') {
            $_SESSION['errors'] = ["Round $round_num has already started!"];
        
        } else if ($round_num == 1) { // if round 1 is inactive
            $_SESSION['msg'] = ["Round 2 started successfully"];
            $round_dao->set(2, 'ACTIVE');

        } else { // if round 2 is inactive
            $_SESSION['errors'] = ["Round 2 has already ended!"];
        }

        // can just call both since each function checks whether errors and msg is set or not
        printMessages();
        printErrors();
    }
?>
	</body>
</html>

