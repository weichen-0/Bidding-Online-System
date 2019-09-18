<?php
    require_once '../include/common.php';
    require_once '../include/protect.php';
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
            Bidding Round: <big><b><u>0</u></b></big>
		</p>

		<form id='bootstrap-form' action="bootstrap-process.php" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<th>ZIP File Upload</th>
					<th><input id='bootstrap-file' type="file" name="bootstrap-file"></th>
				</tr>
				<tr>
					<th colspan='2' style="text-align:left"><input name='Import' type='submit' /></th>
            	</tr>
		</form>

	</body>
</html>

