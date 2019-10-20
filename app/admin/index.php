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
        
<?php
        if (isset($_SESSION['login'])) {
            echo "<h1>Welcome to BIOS, Admin!</h1>";
            unset($_SESSION['login']);
        } else {
            echo "<h1>BIOS [Admin]</h1>";
        }
?>
        <p>
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>

		<form id='bootstrap-form' action="bootstrap-process.php" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<th>Bootstrap Data</th>
					<th><input id='bootstrap-file' type="file" name="bootstrap-file"></th>
				</tr>
				<tr>
					<th colspan='2' style="text-align:center"><input name='import' type='submit'/></th>
            	</tr>
            </table>
		</form>

        <table>
            <tr>
                <form action='start_round.php' method='post'>
                    <td><input name='submit' value='Start Round!' type='submit' style="width:173px"/></td>
                </form>
                <form action='clear_round.php' method='post'>
                    <td><input name='submit' value='End Round!' type='submit' style="width:173px"/></td>
                </form>
            </tr>
        </table>
<?php
        // can just call both since each function checks whether errors and msg is set or not
        printMessages();
        printErrors();
?>
    </body>
</html>