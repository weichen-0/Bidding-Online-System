<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('token', true) ];

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
$round_num = $round_dao->retrieveRound();
$round_status = $round_dao->retrieveStatus();

$result = ["status" => "success",
            "round" => $round_num];

if ($round_status == "INACTIVE") {
    if ($round_num == 1) {
        $round_dao->set(2, "ACTIVE");
        $result["round"] += 1;
    } else {
        unset($result["round"]);
        $result["message"] = ["round 2 ended"];
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;

?>