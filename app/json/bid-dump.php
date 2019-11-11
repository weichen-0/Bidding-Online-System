<?php
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section'),
            isMissingOrEmpty ('token') ];

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

$request = json_decode($_REQUEST['r'], true);
$course_dao = new CourseDAO();
$course = $course_dao->retrieve($request['course']);

$err_msg = array();

if ($course == null) {
    $err_msg[] = "invalid course";

} else {
    $section_dao = new SectionDAO();
    $section = $section_dao->retrieve($request['course'], $request['section']);
    
    if ($section == null) {
        $err_msg[] = "invalid section";

    } else {
        $bid_dao = new BidDAO();
        $bids = $bid_dao->retrieveBySection($request['course'], $request['section']);

        $sort_class = new Sort();
        $bids = $sort_class->sort_it($bids, "bid_dump");

        $round_dao = new RoundDAO();
        $round_status = $round_dao->retrieveStatus();
        $round_num = $round_dao->retrieveRound();

        $enrolment_dao = new EnrolmentDAO();

        $bid_result = array();
        for ($i = 0; $i < count($bids); $i++) {
            $bid = $bids[$i];
            $enrolment = $enrolment_dao->retrieve($bid->userid, $bid->code, $bid->section);
            
            // pending status if active round
            if ($round_status == "ACTIVE") {
                $bid_status = '-';

            // in/out status if inactive round
            } else {
                $bid_status = ($enrolment == null) ? 'out' : 'in';
            }

            $bid_result[] = ["row" => $i + 1,
                             "userid" => $bid->userid,
                             "amount" => (float) $bid->amount, 
                             "result" => $bid_status]; 
        }
    }
}

if (empty($err_msg)) {
    $result = ["status" => "success",
               "bids" => $bid_result];
} else {
    $result = ["status" => "error",
               "message" => $err_msg];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
exit;

?>