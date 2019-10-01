<?php
    require_once '../include/common.php';
    require_once '../include/protect_admin.php';
    
    $round_dao = new RoundDAO();
    $round_num = $round_dao->retrieveRound();
    $bid_dao = new BidDAO();
    $allBids = $bid_dao->retrieveAll();



    //Round 1
    // if ($round_num == 1) {

    // }










?>