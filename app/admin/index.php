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
            echo "<h1>Bidding Online System [Admin]</h1>";
        }
?>
        <p>
            <a href='../logout.php'>Logout</a>
        </p>
        <p>
            Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big>
        </p>
        <br/>
        <div style="overflow-y:auto; max-height:300px; background-color:darkgrey; display:inline-block;">
		<table>
            <form id='bootstrap-form' action="bootstrap_process.php" method="post" enctype="multipart/form-data">
				<tr>
					<th rowspan=2>Bootstrap</th>
					<td><input id='bootstrap-file' type="file" name="bootstrap-file" width='100'></td>
				</tr>
				<tr>
					<td style="text-align:center"><input name='import' type='submit'/></td>
                </tr>
            </form>
        </table>
        </div>
        <br/><br/>

        <form action='start_process.php' method='post' style='float:left'>
            <input name='submit' value='Start Round!' type='submit' style="width:168px; line-height:100px; background-color:yellowgreen; font-weight:bold; border-radius:10px; font-size:15;"/>
            <!-- <button type='submit' name='submit' value='Start Round!' class='start-round-button'></button> -->
        </form>
        <form action='clear_process.php' method='post'>
            <input name='submit' value="Stop Round!" type='submit' style='width:168px; line-height:100px; background-color:indianred; font-weight:bold; border-radius:10px; font-size:15'/>
        </form>
<?php
        // can just call both since each function checks whether errors and msg is set or not
        printMessages();
        printErrors();
?>
    </body>
</html>