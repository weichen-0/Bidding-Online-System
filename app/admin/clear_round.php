<?php
    // require_once '../include/common.php';
    // require_once '../include/protect_admin.php';

    // already requires common.php and protect_admin.php
    require_once 'clear_round_logic.php';
    
    $round_dao = new RoundDAO();
    $round_num = $round_dao->retrieveRound();
    $round_status = $round_dao->retrieveStatus()
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
                <th><input name='submit' value='Click here to end round!' type='submit' style="width:250px"/></th>
            </form>
        </table>
<?php
    if (isset($_POST['submit'])) {

        if ($round_status == 'ACTIVE') {
            $_SESSION['msg'] = ["Round $round_num ended successfully"];
            clear_round();

        } else { // if round is inactive
            $_SESSION['errors'] = ["Round $round_num has already ended!"];
        }
        
        // can just call both since each function checks whether errors and msg is set or not
        printMessages();
        printErrors();
    }
?>
	</body>
</html>

