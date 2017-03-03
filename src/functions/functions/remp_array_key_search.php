<?php
/*
	grunt.concat_in_order.declare('remp_array_key_search');
	grunt.concat_in_order.require('init');
*/


function remp_array_key_search( $needle, $haystack, $recursive = true ) {
	$result = false;
	if ( is_array( $haystack ) ) {
		foreach ( $haystack as $k => $v) {
			
			$result = $k === $needle ? $v : ( $recursive ? remp_array_key_search( $needle, $v ) : false ) ;
			
			if ( $result ) {
				break;
			}
		}
	}
	return $result;
}


?>

