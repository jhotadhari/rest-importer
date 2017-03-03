<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_clearfix');
	grunt.concat_in_order.require('init');
*/

function cmb2_clearfix_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	echo '<br class="clearfix">';
}
add_action( 'cmb2_render_clearfix', 'cmb2_clearfix_render_callback', 10, 5 );


?>