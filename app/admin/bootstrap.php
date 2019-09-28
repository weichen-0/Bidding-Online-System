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
		<h1>BIOS Bootstrap</h1>
        <p>
            <a href='index.php'>Home</a> |
            <a href='start_round.php'>Start Round</a> |
            <a href='clear_round.php'>Clear Round</a> |   
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
		</p>

		<form id='bootstrap-form' action="bootstrap-process.php" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<td>Zip File Upload</td>
					<td><input id='bootstrap-file' type="file" name="bootstrap-file"></td>
				</tr>
				<tr>
					<td colspan='2' style="text-align:left"><input name='Import' type='submit' /></td>
            	</tr>
		</form>

	</body>
</html>

