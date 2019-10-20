<?php
require_once '../include/protect_admin.php';
require_once '../include/process_round_logic.php';

$round_dao = new RoundDAO();
$round_num = $round_dao->retrieveRound();
$round_status = $round_dao->retrieveStatus();

if (isset($_POST['submit'])) {

    if ($round_status == 'ACTIVE') {
        $_SESSION['msg'] = ["Round $round_num ended successfully"];
        process_round(true);
        $round_dao->set($round_num, 'INACTIVE');

    } else { // if round is inactive
        $_SESSION['errors'] = ["Round $round_num has already ended!"];
    }
}

header("Location: index.php");
exit;
?>

