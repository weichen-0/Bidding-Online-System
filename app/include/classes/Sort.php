<?php
class Sort {

	// function array($a, $b) {
	// 	$file_cmp = strcmp($a['file'], $b['file']);
	// 	if ($file_cmp == 0) {
	// 		return $a['line'] - $b['line'];
	// 	}
	// 	return $file_cmp;
	// }

	// function string($a, $b) {
	// 	return strcmp($a, $b);
	// }

	function course($a, $b) {
		$a_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$a['course']);  
		$b_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$b['course']);
		
		$letter_cmp = strcmp($a_arr[0], $b_arr[0]);
		if ($letter_cmp == 0) {
			return $a_arr[1] - $b_arr[1];
		}
		return $letter_cmp;
	}

	function prereq_course ($a, $b) {
		$a_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$a['prerequisite']);  
		$b_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$b['prerequisite']);
		
		$letter_cmp = strcmp($a_arr[0], $b_arr[0]);
		if ($letter_cmp == 0) {
			return $a_arr[1] - $b_arr[1];
		}
		return $letter_cmp;
	}

	function section($a, $b) {
		$course_cmp = $this->course($a, $b);
		if ($course_cmp == 0) {
			return strcmp($a['section'], $b['section']);
		}
		return $course_cmp;
	}

	function student($a, $b) {
		return strcmp($a['userid'], $b['userid']);
	}

	function prereq($a, $b) {
		$course_cmp = $this->course($a, $b);
		if ($course_cmp == 0) {
			return $this->prereq_course($a, $b);
		}
		return $course_cmp;
	}

	function bid($a, $b) {
		$course_section_cmp = $this->section($a, $b);
		if ($course_section_cmp != 0) {
			return $course_section_cmp;
		}
		$bid_cmp = $a['amount'] - $b['amount'];
		if ($bid_cmp != 0) {
			return $bid_cmp;
		}
		return $this->student($a, $b);
	}

	// sort bid objects by amount (highest to lowest)
	function clear_round($a, $b) {
		return $b->amount - $a->amount;
	}

	// sort bid objects by userid (a to z)
	function section_dump ($a, $b) {
		return $this->string($a->userid, $b->userid);
	}

	// sort bid objects by amount (highest to lowest), followed by userid (a to z)
	function bid_dump ($a, $b) {
		$amt_cmp = $this->clear_round($a, $b);
		if ($amt_cmp != 0) {
			return $amt_cmp;
		}
		return $this->section_dump($a, $b);
	}

	function course_completed($a, $b) {
		$course_cmp = $this->course($a, $b);
		if ($course_cmp != 0) {
			return $course_cmp;
		}
		return $this->student($a, $b);
	}

	function enrolment ($a, $b) {
		return $this->course_completed($a, $b);
	}

	function string ($a, $b) {
		return strcmp($a, $b);
	}

	function sort_it($list, $type) {
		usort($list, array($this, $type));
		return $list;
	}
	
}

?>