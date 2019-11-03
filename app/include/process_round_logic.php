<?php
require_once 'common.php';

// returns a dictionary of bids placed with key <code section> and value <sorted list of bids in descending amount>
function getAllPlacedBids() {
    $sort_class = new Sort();
    $bid_dao = new BidDAO();
    $section_dao = new SectionDAO();
    $sections = $section_dao->retrieveAll();
    
    $result = array();
    foreach ($sections as $section) {
        $course_section_str = $section->course . " " . $section->section;
        $bids = $bid_dao->retrieveBySection($section->course, $section->section);

        if (!empty($bids)) {
            // sort all bids in descending order based on amount
            $bids = $sort_class->sort_it($bids, "clear_round");
            $result[$course_section_str] = $bids;
        }
    }
    return $result;
}

// returns an array of successful bids, unsuccessful bids and clearing price for each section in round 1
// clearing price is based on nth highest bid
function process_r1_bids() {
    $section_dao = new SectionDAO();
    $result = array();
    $placed_bids = getAllPlacedBids();

    foreach ($placed_bids as $course_section_str => $bid_list) {
        $course_section_arr = explode(' ', $course_section_str);
        $section = $section_dao->retrieve($course_section_arr[0], $course_section_arr[1]);
        
        $section_size = $section->size;
        $total_bids = count($bid_list);
        $unsuccessful_bids = array();

        if ($total_bids < $section_size) {
            $successful_bids = $bid_list;
            $clearing_price = 10;
        
        } else {         
            // find the nth bid amount, with n being the section size
            $clearing_price = $bid_list[$section_size - 1]->amount;

            // no multiple clearing price bids if section size and bid size is 1
            if ($total_bids == $section_size && $section_size == 1) {
                $multiple_clearing_price_bids = false;

            // check (n-1)th bid amt if bid size = section size
            } else if ($total_bids == $section_size) {
                $multiple_clearing_price_bids = $bid_list[$section_size - 2]->amount == $clearing_price;

            // check (n+1)th bid amt if section size = 1
            } else if ($section_size == 1) {
                $multiple_clearing_price_bids = $bid_list[$section_size]->amount == $clearing_price;

            // check both (n-1)th and (n+1)th bid amt
            } else {
                $multiple_clearing_price_bids = ($bid_list[$section_size - 2]->amount == $clearing_price) || ($bid_list[$section_size]->amount == $clearing_price); 
            }          
            
            $successful_bids = array();
            // add placed bids to successful bid array
            for ($i = 0; $i < $section_size; $i++) {
                $bid = $bid_list[$i];

                // stop if multiple bids at clearing price and current bid is at clearing price
                if ($bid->amount == $clearing_price && $multiple_clearing_price_bids) {
                    break;
                }
                $successful_bids[] = $bid;
            }

            // add remaining bids to unsuccessful bid array
            for ($i = count($successful_bids); $i < $total_bids; $i++) {
                $bid = $bid_list[$i];
                $unsuccessful_bids[] = $bid;
            }
        }
        $result[$course_section_str] = [$successful_bids, $unsuccessful_bids, $clearing_price];
    }
    return $result;
}

// returns an array of successful bids, unsuccessful bids and clearing price for each section in round 2
// clearing price is the lowest successful bid in order to secure a spot
function process_r2_bids() {
    $enrolment_dao = new EnrolmentDAO();
    $minbid_dao = new MinBidDAO();
    $section_dao = new SectionDAO();
    $round_dao = new RoundDAO();
    $round_status = $round_dao->retrieveStatus(); 
    $result = array();
    $placed_bids = getAllPlacedBids();

    foreach ($placed_bids as $course_section_str => $bid_list) {
        $course_section_arr = explode(' ', $course_section_str);
        $section = $section_dao->retrieve($course_section_arr[0], $course_section_arr[1]);
        
        // get number of vacancies left in section
        $enrolments = $enrolment_dao->retrieveBySection($course_section_arr[0], $course_section_arr[1]);
        $enrolled = count($enrolments);
        $vacancies = $section->size - $enrolled;

        // get min bid
        $min_bid = $minbid_dao->retrieve($course_section_arr[0], $course_section_arr[1]);

        $total_bids = count($bid_list);
        $unsuccessful_bids = array();

        // account for newly added enrolments from recently concluded round  
        if ($round_status == 'INACTIVE') {
            foreach ($bid_list as $bid) {
                foreach ($enrolments as $enrolment) {
                    if ($bid->userid == $enrolment->userid) {
                        $vacancies++;
                    }
                }
            }
        }

        if ($total_bids <= $vacancies) {
            $successful_bids = $bid_list;
            // if section is full, clearing price is the last bid amount + 1, else price is min bid
            $clearing_price = ($total_bids == $vacancies) ? $bid_list[$vacancies - 1]->amount + 1: $min_bid;

        } else {
            // find the nth bid amount, with n being the section size
            $clearing_price = $bid_list[$vacancies - 1]->amount;

            // check if (n+1)th bid at clearing price
            $multiple_clearing_price_bids = ($bid_list[$vacancies]->amount == $clearing_price); 
            
            $successful_bids = array();
            // add placed bids to successful bid array
            for ($i = 0; $i < $vacancies; $i++) {
                $bid = $bid_list[$i];

                // stop if class unable to accommodate all bids with the same bid price
                if ($bid->amount == $clearing_price && $multiple_clearing_price_bids) {
                    break;
                }
                $successful_bids[] = $bid;
            }

            // add remaining bids to unsuccessful bid array
            for ($i = count($successful_bids); $i < $total_bids; $i++) {
                $bid = $bid_list[$i];
                $unsuccessful_bids[] = $bid;
            }
            $clearing_price++;
        }
        
        // increments clearing price by one and enforces the 'price never goes down' condition 
        if ($min_bid > $clearing_price) {
            $clearing_price = $min_bid;
        }
        $result[$course_section_str] = [$successful_bids, $unsuccessful_bids, $clearing_price];
    }
    return $result;
}

function process_round($isEndOfRound) {
    $enrolment_dao = new EnrolmentDAO();
    $minbid_dao = new MinBidDAO();
    $student_dao = new StudentDAO();
    $round_dao = new RoundDAO();
    $round_num = $round_dao->retrieveRound();
    $processed_bids = ($round_num == 1) ? process_r1_bids() : process_r2_bids();

    foreach ($processed_bids as $course_section_str => $arr) {
        $successful_bids = $arr[0];
        $unsuccessful_bids = $arr[1];
        $clearing_price = $arr[2];

        // update min bid for each section
        $course = $successful_bids[0]->code;
        $section = $successful_bids[0]->section;
        $minbid_dao->set($course, $section, $clearing_price);

        if ($isEndOfRound) {
            // enrol students into section
            foreach ($successful_bids as $enrolment) {
                $enrolment_dao->add($enrolment);
            }
            // refund e$ for failed bids
            foreach ($unsuccessful_bids as $bid) {
                $student = $student_dao->retrieve($bid->userid);
                $updatedBal = $student->edollar + $bid->amount;
                $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
                $student_dao->update($studentNew);
            }
        }
    }
}

?>