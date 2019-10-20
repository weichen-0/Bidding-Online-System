<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/process_round_logic.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('token') ];

if (!empty($_REQUEST['token']) && !verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

$errors = array_filter($errors);

if (!isEmpty($errors)) {
    $errors = array_values($errors);
    $result = [
        "status" => "error",
        "message" => $errors
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$round_dao = new RoundDAO();
$round_status = $round_dao->retrieveStatus();
$round_num = $round_dao->retrieveRound();

if ($round_status == 'ACTIVE') {
    $result = ["status" => "success"];
    process_round(true);
    $round_dao->set($round_num, "INACTIVE");

} else { // if round is inactive
    $result = ["status" => "error",
                "message" => ["round already ended"]];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;

?>