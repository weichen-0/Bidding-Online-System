<?php
class Sort {

	function array($a, $b) {
		$file_cmp = strcmp($a['file'], $b['file']);
		if ($file_cmp == 0) {
			return $a['line'] - $b['line'];
		}
		return $file_cmp;
	}

	function not_array($a, $b) {
		return strcmp($a, $b);
	}

	function sort_it($list, $type) {
		usort($list, array($this, $type));
		return $list;
	}
	
}

?>