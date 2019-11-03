<?php
# edit the bootstrap file included below, bootstrap logic is there
require_once '../include/bootstrap.php';
require_once '../include/protect_admin.php';

if (!isset($_POST['import'])) {
    header("Location: bootstrap.php");
    exit;
} 

$result = doBootstrap();
$round_dao = new RoundDAO();
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="../include/style.css">
</head>
<body>
    <h1>BIOS Bootstrap Response</h1>
    <p>
        <a href='index.php'>Back</a> |
        <a href='../logout.php'>Logout</a>
    </p>
    <p>
        Bidding Round <?=$round_dao->retrieveRound()?>: <big><b><u><?=$round_dao->retrieveStatus()?></u></b></big><br/>
        Bootstrap Status: <big><b><u><?=$result['status'] == 'success' ? 'SUCCESS' : "ERROR"?></u></b></big>
    </p>

<?php
echo "<div style='background-color:darkgrey; display:inline-block;'>";
// table for num-record-loaded 
echo "<table>
        <tr>
            <th>File Name</th>
            <th>Total Records Loaded</th>
        </tr>";
foreach ($result['num-record-loaded'] as $arr) {
    foreach ($arr as $file_name => $row) {
        echo "<tr>
                <td>$file_name</td>
                <td>$row</td>
            </tr>";
    }
}
echo "</table></div><br/><br/>";

echo "<div style='background-color:darkgrey; display:inline-block;'";
// table for errors
if (isset($result['error'])) {
    if (!is_array($result['error'][0])) {
        echo "<br/><table width='318'>
                    <tr><th>Error Message</th></tr>
                    <tr><td align='center'>input files not found</td></tr>
                    </table>";
    } else {
        echo "<br/><table>
                    <tr>
                        <th width='142'>File Name</th>
                        <th>Row</th>
                        <th>Error Messages</th>
                    </tr>";
        
        // count number of line with errors per file for rowspan in table
        $file_errors = array();
        foreach ($result['error'] as $arr) {
            $name = $arr['file'];
            if (isset($file_errors[$name])) {
                $file_errors[$name][] = ["line" => $arr['line'], "message" => $arr['message']];
            } else {
                $file_errors[$name] = [["line" => $arr['line'], "message" => $arr['message']]];
            }
        }

        foreach ($file_errors as $name => $arr) {
            $num_rows = count($arr);
            echo "<tr>
                    <td rowspan='$num_rows'>$name</td>";
            foreach ($arr as $row) {
                echo "<td width='151'>{$row['line']}</td><td style='min-width:151px'>";
                foreach ($row['message'] as $msg) {
                    echo "$msg<br/>";
                }
                echo "</td></tr>";
            }
        }
        echo "</table></div>";
    }
}
?>

	</body>
</html>