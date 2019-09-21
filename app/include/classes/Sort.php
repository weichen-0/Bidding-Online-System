<?php
class Sort {

	function error($a, $b) {
		$file_cmp = strcmp($a['file'], $b['file']);
		if ($file_cmp == 0) {
			return $a['line'] - $b['line'];
		}
		return $file_cmp;
	}

	function sort_errors($list) {
		usort($list, array($this, 'error'));
		return $list;
	}
	
}

?>