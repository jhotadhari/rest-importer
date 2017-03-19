<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_info');
	grunt.concat_in_order.require('init');
*/

function cmb2_info_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	
	$info = $field_type_object->field->args( 'info' );
	
	$paragraph = array_key_exists('paragraph', $field_type_object->field->args( 'attributes' ) ) ?  $field_type_object->field->args( 'attributes' )['paragraph'] : false;
	$tag = $paragraph ? 'p' : 'span';
	
	echo !empty($info) ? sprintf( "\n" . '<%1$s class="cmb2-metabox-info">%2$s</%1$s>' . "\n", $tag, $info ) : '';
	
	// hidden field. nonsence, just to set data-conditional
	echo $field_type_object->input( array(
		'type' => 'hidden',
		) );
	
	// no need to echo desc, the hidden input does it already
	// echo $field_type_object->_desc( false );
	
}
add_action( 'cmb2_render_info', 'cmb2_info_render_callback', 10, 5 );


?>