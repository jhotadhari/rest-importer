<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_exp_respond');
	grunt.concat_in_order.require('init');
*/

function cmb2_exp_respond_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
// function cmb2_exp_respond_render_callback( ) {
	
	echo $field_type_object->_desc( true );
	
	echo '<br>';
	
	$arr = array(
		'results' => array(
			'lovely_people' => array(
					0 => array(
						'name' => 'Aila Starmanova',
	
						'children' => array(
							0 => array(
								'name' => 'Treebeard Perez',
	
							),
							1 => array(
								'name' => 'Staninslav Abdul-Basir Haddad',
	
							),
							2 => array(
								'name' => 'Fernanda Larsen',
	
							),
						),
					),
					1 => array(
						'name' => 'Surendranath Morrison',
						'children' => array(
							0 => array(
								'name' => 'Nico Hill',
	
							),
							1 => array(
								'name' => 'Lete Yue Hsu',
	
							),
						),
					),
				),
			),
		);
	
	print('<pre style="font-size:small;">');
	print_r($arr);
	print('</pre>');
	
	
	
	echo '<br class="clearfix">';
}
add_action( 'cmb2_render_exp_respond', 'cmb2_exp_respond_render_callback', 10, 5 );
// add_action( 'admin_notices', 'cmb2_exp_respond_render_callback' );


?>