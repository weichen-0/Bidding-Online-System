<?php
require_once '../include/common.php';
require_once '../include/protect_admin.php';

$round_dao = new RoundDAO();
$round_num = $round_dao->retrieveRound();
$round_status = $round_dao->retrieveStatus();

if (isset($_POST['submit'])) {
    
    if ($round_status == 'ACTIVE') {
        $_SESSION['errors'] = ["Round $round_num has already started!"];
    
    } else if ($round_num == 2) { // if round 2 is inactive
        $_SESSION['errors'] = ["Round 2 has already ended!"];
    
    } else { // if round 1 is inactive
        $_SESSION['msg'] = ["Round 2 started successfully"];
        $round_dao->set(2, 'ACTIVE');

        $bid_dao = new BidDAO();
        $bid_dao->removeAll();

        $minbid_dao = new MinBidDAO();
        $minbid_dao->resetAll(10);
    }
}

header("Location: index.php");
exit;
?>

