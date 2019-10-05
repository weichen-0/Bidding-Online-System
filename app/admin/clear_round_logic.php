<?php
require_once '../include/common.php';
require_once '../include/protect_admin.php';

function clear_round($round_num) {
    
    $bid_dao = new BidDAO();
    $bids = $bid_dao->retrieveAll();

    $section_dao = new SectionDAO();
    $enrolment_dao = new EnrolmentDAO();
    $student_dao = new StudentDAO();
    $sort_class = new Sort();

    // group bids of the same course and section together in a dictionary: key <code section>, value <list of bids>
    $bid_dict = array();
    foreach ($bids as $bid) {
        $key = $bid->code . ' ' . $bid->section;

        if (isset($bid_dict[$key])) {
            $bid_dict[$key][] = $bid;
        } else {
            $bid_dict[$key] = [$bid];
        }
    }

    if ($round_num == 1) {
        foreach ($bid_dict as $key => $bid_list) {
            $course_section = explode(' ', $key);
            $size = $section_dao->retrieve($course_section[0], $course_section[1])->size;

            $num_bids = count($bid_list);

            // Scenario 1: no clearing price, number of bids are less than size
            if ($num_bids < $size) {
                $successful = $bid_list;
            
            // Scenario 2: clearing price, number of bids are >= size
            } else {
                // sort all bids in descending order to find clearing price
                $bid_list = $sort_class->sort_it($bid_list, "desc_bid_obj_amt");
                
                // find the nth bid amount, with n being the number of vacancies
                $clearingprice = $bid_list[$size - 1]->amount;

                $successful = array();
                for ($i = 0; $i < $size; $i++) {
                    $bid_amount = $bid_list[$i]->amount;

                    // stop adding bids to successful bid list if amount is equal to clearing price but bid is not the nth bid
                    if ($bid_amount == $clearingprice && $i != $size - 1) {
                        break;
                    }
                    $successful[] = $bid;
                }
            }

            // enrol the students. No need to deduct anything because already deducted when bidding
            foreach ($successful as $enrolment) {
                $enrolment_dao->add($enrolment);
            }

            // refund e$ for failed bids, continues from the last bid not added into the successful bid list
            for ($i = count($successful); $i < num_bids; $i++) {
                $updatedBal = $student->edollar + $bid_list[$i]->amount;
                $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
                $student_dao->update($studentNew);
            }
        }
    } 
}
    
    // $round_dao = new RoundDAO();
    // $round_num = $round_dao->retrieveRound();

    // // retrieve all the bids in system
    // $bid_dao = new BidDAO();
    // $allBids = $bid_dao->retrieveAll(); 

    // // retrieve all courses and their sections to get their capacities
    // $section_dao = new SectionDAO();
    // $allSections = $section_dao->retrieveAll();

    // // to enroll the students
    // $enrolment_dao = new EnrolmentDAO();
    
    // // to refund e$
    // $student_dao = new StudentDAO();

    // // round 1
    // if ($round_num == 1) {

    //     // check bids for each course and section
    //     foreach ($allSections as $section) {
    //         $course = $section->course;
    //         $section = $section->section;
    //         $size = $section->size;

    //         $currentBids = array();
            
    //         // check each bid to see if they are for the specified course and section
    //         // if yes, and bid amt is 10 or more, add to array of bids
    //         foreach ($allBids as $bid) {
    //             $bidCode = $bid->code;
    //             $bidSect = $bid->section;
    //             $bidAmt = $bid->amount;
    //             if ($bidCode == $course && $bidSect == $section && $bidAmt >= 10) {
    //                 $currentBids[] = $bid;
    //             }
    //         }

    //         // shortlist successful bids

    //         $noOfBids = count($currentBids);

    //         // Scenario 1: no clearing price, number of bids are less than size
    //         if ($noOfBids < $size) {
    //             $successful = $currentBids;
    //         }

    //         // Scenario 2: clearing price, number of bids are >= size
    //         else {
    //             $allBidAmts = array();
    //             $overCapacity = $noOfBids-$size;

    //             // shortlist all bid amounts
    //             foreach ($currentBids as $bid) {
    //                 $bidAmt = $bid->amount;
    //                 if (!in_array($bidAmt, $allBidAmts)) {
    //                     $allBidAmts[] = $bidAmt;
    //                 }
    //             }
                
    //             // find clearing price
    //             //sort all bids in descending order
    //             rsort($allBidAmts);
                
    //             //find the nth bid amount, with n being the number of vacancies
    //             if (count($allBidAmts) > $size) {
    //                 $clearingprice = $allBidAmts[$size - 1];
    //             }
    //             else {
    //                 $clearingprice = min($allBidAmts);
    //             }

    //             // applying round 1 logic
    //             $clearingpriceBids = array();
    //             foreach ($currentBids as $bid) {
    //                 $bidAmt = $bid->amount;
    //                 // find bids that have amounts higher than clearing price and add them to successful bids
    //                 if ($bidAmt > $clearingprice) {
    //                     $successful[] = $bid;
    //                 }
    //                 // find bids that have amounts equal to clearing price
    //                 if ($bidAmt == $clearingprice)
    //                     $clearingpriceBids[] = $bid;
    //                 }
                
    //             }

    //             // check number of bids at clearing price. 
    //             // if only one bid, it will be successful
    //             // else, do nothing as all bids are dropped
    //             if (count($clearingpriceBids == 1)) {
    //                 foreach ($clearingpriceBids as $bid) {
    //                     $successful[] = $bid;
    //                 }
    //             }
            
            
    //         // enrol the students. No need to deduct anything because already deducted when bidding
    //         foreach ($successful as $enrolment) {
    //             $enrolment_dao->add($enrolment);
    //         }

    //         // refund e$ for failed bids
    //         foreach ($clearingpriceBids as $student) {
    //             $updatedBal = $student->edollar + $clearingprice;
    //             $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
    //             $student_dao->update($studentNew);
    //         }
    //     }
    // }

?>