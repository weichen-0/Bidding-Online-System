<?php
require_once 'common.php';

// removes whitespaces at the start and end of each field in a csv row
function trim_row($row) {
	
	$trimmed = array();
	foreach ($row as $field) {
		$trimmed[] = trim($field);
	} 
	return $trimmed;
}

// checks the row of the csv file for any blank fields, if yes then return a list of errors
function common_validate_row($header, $row) {

	$row_errors = array();
	// safe to assume each row has same num of fields as header row according to wiki
	for ($i = 0; $i < count($header); $i++) {
		if ($row[$i] === '') {
			$field = $header[$i];
			$row_errors[] = "blank $field";
		}
	}
	return $row_errors;
}

function student_validate_row($row) {
	
    $row_errors = array();
    $student_dao = new StudentDAO();

	// check if userid field exceeds 128 characters
	$userid = $row[0];
	if (strlen($userid) > 128) {
		$row_errors[] = "invalid userid";
	}

	// check if there is an existing user with the same userid
	if ($student_dao->retrieve($userid) != null) {
		$row_errors[] = "duplicate userid";
	}

	// check if edollar field is a numeric value greater or equal to 0.0 (not more the 2dp)
	$edollar = $row[4];
	$more_than_2dp = preg_match('/\.\d{3,}/', $edollar);
	if (!is_numeric($edollar) || $edollar < 0.0 || $more_than_2dp) {
		$row_errors[] = "invalid e-dollar";
	}

	// check if password field exceed 128 characters
	$pwd = $row[1];
	if (strlen($pwd) > 128) {
		$row_errors[] = "invalid password";
	}

	// check if name field exceed 100 characters
	$name = $row[2];
	if (strlen($name) > 100) {
		$row_errors[] = "invalid name";
	}

	return $row_errors;
}

function course_validate_row($row) {

	$row_errors = array();

	// check if date is in Ymd format
	$date = DateTime::createFromFormat('Ymd', $row[4]);
	if (!$date || $date->format('Ymd') !== $row[4]) {
		$row_errors[] = "invalid exam date";
	}

	// check if exam start is in H:mm format
	$start = DateTime::createFromFormat('G:i', $row[5]);
	if (!$start || $start->format('G:i') !== $row[5]) {
		$row_errors[] = "invalid exam start";
	}

	// check if exam end is in H:mm format and is after start time
	$end = DateTime::createFromFormat('G:i', $row[6]);
	$endBeforeStart = $end <= $start;
	if (!$end || $end->format('G:i') !== $row[6] || $endBeforeStart) {
		$row_errors[] = "invalid exam end";
	}

	// check if title field exceeds 100 characters
	$title = $row[2];
	if (strlen($title) > 100) {
		$row_errors[] = "invalid title";
	}

	// check if description field exceeds 1000 characters
	$description = $row[3];
	if (strlen($description) > 1000) {
		$row_errors[] = "invalid description";
	}

	return $row_errors;
}


function section_validate_row($row) {

	$row_errors = array();

	// check if course is in course.csv
	$course = $row[0];
	$course_dao = new CourseDAO(); 
	if ($course_dao->retrieve($course) == null) {
		$row_errors[] = "invalid course";

	// IF COURSE VALID, check if section is an S followed by positive numeric number (1-99)
	} else {
		$section = $row[1];
		if (!preg_match('/^S[1-9][0-9]{0,1}$/', $section)) {
			$row_errors[] = "invalid section";
		}
	}

	// check if day field is a numeric value between 1 to 7 (inclusive)
	$day = $row[2];
	if (!is_numeric($day) || $day < 1 || $day > 7) {
		$row_errors[] = "invalid day";
	}

	// check if class start is in H:mm format
	$start = DateTime::createFromFormat('G:i', $row[3]);
	if (!$start || $start->format('G:i') !== $row[3]) {
		$row_errors[] = "invalid start";
	}

	// check if class end is in H:mm format and is after start time
	$end = DateTime::createFromFormat('G:i', $row[4]);
	$endBeforeStart = $end <= $start;
	if (!$end || $end->format('G:i') !== $row[4] || $endBeforeStart) {
		$row_errors[] = "invalid end";
	}

	// check if instructor field exceeds 100 characters
	$instructor = $row[5];
	if (strlen($instructor) > 100) {
		$row_errors[] = "invalid instructor";
	}

	// check if venue field exceeds 100 characters
	$venue = $row[6];
	if (strlen($venue) > 100) {
		$row_errors[] = "invalid venue";
	}

	// check if size field is a positive numeric number
	$size = $row[7];
	if (!is_numeric($size) || $size < 1) {
		$row_errors[] = "invalid size";
	}
	
	return $row_errors;
}

function prereq_validate_row($row) {

	$row_errors = array();
	$course_dao = new CourseDAO();

	// check if course is in course.csv
	$course = $row[0];
	if ($course_dao->retrieve($course) == null) {
		$row_errors[] = "invalid course";
	}

	// check if prerequisite is in course.csv
	$prereq = $row[1];
	if ($course_dao->retrieve($prereq) == null) {
		$row_errors[] = "invalid prerequisite";
	}

	return $row_errors;
}

function course_completed_validate_row($row) {

	$row_errors = array();
	$student_dao = new StudentDAO();
	$course_dao = new CourseDAO();
    $prereq_dao = new PrereqDAO();
    $course_completed_dao = new CourseCompletedDAO();

	// check if userid is in student.csv
	$userid = $row[0];
	if ($student_dao->retrieve($userid) == null) {
		$row_errors[] = "invalid userid";
	}

	// check if course is in course.csv
	$code = $row[1];
	if ($course_dao->retrieve($code) == null) {
		$row_errors[] = "invalid course";
	}

	// IF NO ERROR ABOVE: check if student has completed (list of) prerequisite course
	if (empty($row_errors)) {
        $prereqs = $prereq_dao->retrieve($code);
        $courses_completed = $course_completed_dao->retrieve($userid);
		foreach ($prereqs as $prereq) {
			if (!in_array($prereq, $courses_completed)) {
				$row_errors[] = "invalid course completed";
				break;
			}
		}
	}

	return $row_errors;
}

function bid_validate_row($row) {

	$row_errors = array();
	$student_dao = new StudentDAO();
	$course_dao = new CourseDAO();
    $section_dao = new SectionDAO();
    $round_dao = new RoundDAO();
    $bid_dao = new BidDAO();
    $prereq_dao = new PrereqDAO();
    $course_completed_dao = new CourseCompletedDAO();

    // check if userid is in student.csv
    $userid = $row[0];
    $student = $student_dao->retrieve($userid);
	if ($student == null) {
		$row_errors[] = "invalid userid";
	}

	// check if amount is a positive number (>= 10) and not more than 2dp
	$amt = $row[1];
	$more_than_2dp = preg_match('/\.\d{3,}/', $amt);
	if (!is_numeric($amt) || $amt < 10 || $more_than_2dp) {
		$row_errors[] = "invalid amount";
	}

    // check if course code is in course.csv
    $code = $row[2];
    $course = $course_dao->retrieve($code);
	if ($course == null) {
		$row_errors[] = "invalid course";

	// IF COURSE VALID, check if section is in section.csv
	} else {
        $section = $section_dao->retrieve($code, $row[3]);
		if ($section == null) {
			$row_errors[] = "invalid section";
		}
    }
    
    if (empty($row_errors)) {
        // if round 1, check if students are bidding courses under their school
        if ($round_dao->retrieveRound() == 1 && $student->school != $course->school) {
            $row_errors[] = "not own school course";
		}
		
		// check if student has completed prerequisites for course
		$prereqs = $prereq_dao->retrieve($code);
		$courses_completed = $course_completed_dao->retrieve($userid);
		foreach ($prereqs as $prereq) {
			if (!in_array($prereq, $courses_completed)) {
				$row_errors[] = "incomplete prerequisites";
				break;
			}
		}

		// check if student has already completed this course
		if (in_array($code, $courses_completed)) {
			$row_errors[] = "course completed";
		}

		// check if student has a previous bid for the same course (whether update is required)
		$prev_bid = null;
		$bids = $bid_dao->retrieveByUser($userid);
		foreach ($bids as $bid) {
			if ($bid->code == $code) {
				$prev_bid = $bid;
				break;
			}
		}

		if (is_null($prev_bid)) {
			// check if class timing clashes with all previously bidded sections
			foreach ($bids as $bid) {
				$prev_section = $section_dao->retrieve($bid->code, $bid->section);
				if ($section->classClashWith($prev_section)) {
					$row_errors[] = "class timetable clash";
					break;
				}
			}

			// check if exam timing clashes with all previously bidded courses
			foreach ($bids as $bid) {
				$prev_course = $course_dao->retrieve($bid->code);
				if ($course->examClashWith($prev_course)) {
					$row_errors[] = "exam timetable clash";
					break;
				}
			}
		}

		// check if student has already bidded for 5 sections
		$overlimit_with_bid_update = (!is_null($prev_bid) && count($bids) >= 6);
		$overlimit_without_bid_update = (is_null($prev_bid) && count($bids) >= 5);
        if ($overlimit_with_bid_update || $overlimit_without_bid_update) {
            $row_errors[] = "section limit reached";
        }

		// check if student has enough edollars whether updating bid or not
		$insuff_edollar_with_refund = (!is_null($prev_bid) && $amt > ($prev_bid->amount + $student->edollar));
		$insuff_edollar_without_refund = (is_null($prev_bid) && $amt > $student->edollar);
		if ($insuff_edollar_with_refund || $insuff_edollar_without_refund) {
			$row_errors[] = "not enough e-dollar";
		}

		// if all validations passed and prev bid found, remove and refund it
		if (!is_null($prev_bid) && empty($row_errors)) {
			$student_dao->update(new Student($userid, $student->password, $student->name, $student->school, $student->edollar + $prev_bid->amount));
			$bid_dao->remove($prev_bid);
		}
	}
	
	return $row_errors;
}

?>