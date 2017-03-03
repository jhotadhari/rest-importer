<?php
/*
	grunt.concat_in_order.declare('remp_preg_grep_keys');
	grunt.concat_in_order.require('init');
*/

function remp_preg_grep_keys( $pattern, $input, $flags = 0 ){
	$keys = preg_grep( $pattern, array_keys( $input ), $flags );
	$vals = array();
	foreach ( $keys as $key )    {
		$vals[$key] = $input[$key];
	}
	return $vals;
}

?>