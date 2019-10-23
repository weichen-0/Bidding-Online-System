<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/process_round_logic.php';

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
        process_round(false);
        $enrolment_dao = new EnrolmentDAO();
        $enrolments = $enrolment_dao->retrieveBySection($request['course'], $request['section']);

        $student_dao = new StudentDAO();
        $sort_class = new Sort();

        $bid_dao = new BidDAO();
        // array of bids sorted from highest to lowest amount
        $bids = $sort_class->sort_it($bid_dao->retrieveBySection($request['course'], $request['section']), 'clear_round');
        $total_bids = count($bids);

        $round_dao = new RoundDAO();
        $round_num = $round_dao->retrieveRound();
        $round_status = $round_dao->retrieveStatus();

        $minbid_dao = new MinBidDAO();
        $minbid = $minbid_dao->retrieve($request['course'], $request['section']); // accounts for active round 2 + most of active round 1

        $student_arr = array();
        $vacancy = $section->size - count($enrolments);
        if ($round_status == 'INACTIVE') {

            if ($round_num == 1) {
                $round_bids = process_r1_bids();
                foreach ($bids as $bid) {
                    $enrolment = $enrolment_dao->retrieve($bid->userid, $bid->code, $bid->section);
                    $status = !is_null($enrolment) ? "success" : "fail";

                    $student = $student_dao->retrieve($bid->userid);
                    $student_arr[] = ["userid" => $student->userid,
                                      "amount" => (float) $bid->amount,
                                      "balance" => (float) $student->edollar,
                                      "status" => $status];
                }
            } else { // inactive round 2
                $round_bids = process_r2_bids();
                foreach ($enrolments as $enrolment) {
                    $student = $student_dao->retrieve($enrolment->userid);
                    $student_arr[] = ["userid" => $student->userid,
                                      "amount" => (float) $enrolment->amount,
                                      "balance" => (float) $student->edollar,
                                      "status" => 'success'];
                }
            }
            $course_section_str = $request['course'] . " " . $request['section'];
            $successful_bids = $round_bids[$course_section_str][0];
            $minbid = (empty($successful_bids)) ? 10.00 : $successful_bids[count($successful_bids) - 1]->amount; // minimum successful bid amount

        } else { // active round
            if ($round_num == 1) {
                if ($total_bids < $vacancy && $total_bids > 0) {
                    $minbid = $bids[$total_bids - 1]->amount; // report lowest bid amount for active round 1 if condition fulfilled
                }
                foreach ($bids as $bid) {
                    $student = $student_dao->retrieve($bid->userid);
                    $student_arr[] = ["userid" => $student->userid,
                                      "amount" => (float) $bid->amount,
                                      "balance" => (float) $student->edollar,
                                      "status" => 'pending'];
                }
            
            } else { // active round 2
                $round_bids = process_r2_bids();
                foreach ($bids as $bid) {
                    $student = $student_dao->retrieve($bid->userid);
                    $course_section_str = $request['course'] . " " . $request['section'];
                    $successful_bids = $round_bids[$course_section_str][0];
                    $status = (in_arr($successful_bids, null)) ? "success" : "fail"; // round 2 bid status

                    $student_arr[] = ["userid" => $student->userid,
                                      "amount" => (float) $bid->amount,
                                      "balance" => (float) $student->edollar,
                                      "status" => $status];
                }
            }
        }
    }
}

if (empty($err_msg)) {
    $result = ["status" => "success",
               "vacancy" => (int) $vacancy,
               "min-bid-amount" => (float) $minbid,
               "students" => $student_arr];
} else {
    $result = ["status" => "error",
               "message" => $err_msg];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
exit;
?>