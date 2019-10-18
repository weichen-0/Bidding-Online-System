<?php
    require_once '../include/common.php';
    require_once '../include/protect_student.php';

    // to update minimum price everytime there is a new bid placed or the bid page gets refreshed
    function clearing_logic() {
        $bid_dao = new BidDAO();
        $bids = $bid_dao->retrieveAll();

        $round_dao = new RoundDAO();

        $section_dao = new SectionDAO();
        $enrolment_dao = new EnrolmentDAO();
        $student_dao = new StudentDAO();
        $minbid_dao = new MinBidDAO();
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


        foreach ($bid_dict as $key => $bid_list) {
            $course_section = explode(' ', $key);
            $size = $section_dao->retrieve($course_section[0], $course_section[1])->size;

            $num_bids = count($bid_list);
            $bid_list = $sort_class->sort_it($bid_list, "desc_bid_obj_amt");

            // find total available seats
            $enrolled = count($enrolment_dao->retrieveBySection($course_section[0], $course_section[1]));
            $vacancies = $size - $enrolled;

            // get minimum bid
            $min_bid = $minbid_dao->retrieve($course_section[0], $course_section[1]);
            if ($min_bid == null) {
                $min_bid = $bid_list[$vacancies - 1]->amount;
            }

            // Scenario 1: number of bids are less than available seats
            if ($num_bids < $vacancies) {
                $vacancies = $vacancies - $num_bids;

            // Scenario 2: number of bids are >= size
            } else {
                $min_bid = $min_bid + 1;

            }

            $minbid_dao->set($course_section[0], $course_section[1], $min_bid);
            $bid_dict[$key] = $bid_list;
        }
    
        return $bid_dict;
    }

?>