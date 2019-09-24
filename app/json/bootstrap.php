<?php
require_once '../include/bootstrap.php';
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('token') ];
$errors = array_filter($errors);

if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "messages" => array_values($errors)
        ];

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

# complete bootstrap
doBootstrap();

?>