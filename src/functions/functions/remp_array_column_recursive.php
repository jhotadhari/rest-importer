<?php
/*
	grunt.concat_in_order.declare('remp_array_column_recursive');
	grunt.concat_in_order.require('init');
*/

function remp_array_column_recursive(array $haystack, $needle) {
	$found = array();
	array_walk_recursive($haystack, function($value, $key) use (&$found, $needle) {
		if ($key == $needle)
			$found[] = $value;
	});
return $found;
}	

?>

