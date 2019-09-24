<?php
class Sort {

	function array($a, $b) {
		$file_cmp = strcmp($a['file'], $b['file']);
		if ($file_cmp == 0) {
			return $a['line'] - $b['line'];
		}
		return $file_cmp;
	}

	function string($a, $b) {
		return strcmp($a, $b);
	}

	function course($a, $b) {
		$a_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$a['course']);  
		$b_arr = preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i',$b['course']);
		
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
		return $this->string($a['userid'], $b['userid']);
	}

	function prereq($a, $b) {
		$course_cmp = $this->course($a, $b);
		if ($course_cmp == 0) {
			return $this->course($a['prerequisite'], $b['prerequisite']);
		}
		return $course_cmp;
	}

	function bid($a, $b) {
		$course_cmp = $this->course($a['course'], $b['course']);
		if ($course_cmp != 0) {
			return $course_cmp;
		}
		$section_cmp = $this->section($a)
	}

	function sort_it($list, $type) {
		usort($list, array($this, $type));
		return $list;
	}
	
}

?>