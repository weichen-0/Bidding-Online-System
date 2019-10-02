<?php
    require_once '../include/common.php';
    require_once '../include/protect_admin.php';
    
    $round_dao = new RoundDAO();
    $round_num = $round_dao->retrieveRound();

    // retrieve all the bids in system
    $bid_dao = new BidDAO();
    $allBids = $bid_dao->retrieveAll(); 

    // retrieve all courses and their sections to get their capacities
    $section_dao = new SectionDAO();
    $allSections = $section_dao->retrieveAll();

    // to enroll the students
    $enrolment_dao = new EnrolmentDAO();
    
    // to refund e$
    $student_dao = new StudentDAO();

    // round 1
    if ($round_num == 1) {

        // check bids for each course and section
        foreach ($allSections as $section) {
            $course = $section->course;
            $section = $section->section;
            $size = $section->size;

            $currentBids = array();
            
            // check each bid to see if they are for the specified course and section
            // if yes, and bid amt is 10 or more, add to array of bids
            foreach ($allBids as $bid) {
                $bidCode = $bid->code;
                $bidSect = $bid->section;
                $bidAmt = $bid->amount;
                if ($bidCode == $course && $bidSect == $section && $bidAmt >= 10) {
                    $currentBids[] = $bid;
                }
            }

            // shortlist successful bids

            $noOfBids = count($currentBids);

            // Scenario 1: no clearing price, number of bids are less than size
            if ($noOfBids < $size) {
                $successful = $currentBids;
            }

            // Scenario 2: clearing price, number of bids are >= size
            else {
                $allBidAmts = array();
                $overCapacity = $noOfBids-$size;

                // shortlist all bid amounts
                foreach ($currentBids as $bid) {
                    $bidAmt = $bid->amount;
                    if (!in_array($bidAmt, $allBidAmts)) {
                        $allBidAmts[] = $bidAmt;
                    }
                }
                
                // find clearing price: sort all bids in descending order
                //then choose the nth bid amount, where n is the number of vacancies
                rsort($allBidAmts);
                $clearingprice = $allBidAmts[$size - 1];
                

                // applying round 1 logic
                $clearingpriceBids = array();
                foreach ($currentBids as $bid) {
                    $bidAmt = $bid->amount;
                    // find bids that have amounts higher than clearing price and add them to successful bids
                    if ($bidAmt > $clearingprice) {
                        $successful[] = $bid;
                    }
                    // find bids that have amounts equal to clearing price
                    if ($bidAmt == $clearingprice)
                        $clearingpriceBids[] = $bid;
                    }
                
                }

                // check number of bids at clearing price. 
                // if only one bid, it will be successful
                // else, do nothing as all bids are dropped
                if (count($clearingpriceBids == 1)) {
                    foreach ($clearingpriceBids as $bid) {
                        $successful[] = $bid;
                    }
                }
            
            
            // enrol the students. No need to deduct anything because already deducted when bidding
            foreach ($successful as $enrolment) {
                $enrolment_dao->add($enrolment);
            }

            // refund e$ for failed bids
            foreach ($clearingpriceBids as $student) {
                $updatedBal = $student->edollar + $clearingprice;
                $studentNew = new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal);
                $student_dao->update($studentNew);
            }
        }
    }










?>