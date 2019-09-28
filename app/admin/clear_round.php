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

	</body>
</html>

