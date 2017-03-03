<?php
/*
	grunt.concat_in_order.declare('remp_cmb2_field_type_crypt');
	grunt.concat_in_order.require('init');
*/

function remp_cmb2_field_type_crypt() {
	if ( ! function_exists( 'cmb2_crypt_render_callback' ) )
		include_once plugin_dir_path( __FILE__ ) . 'includes/jhotadhari/cmb2_field_type_crypt/cmb2_field_type_crypt.php';
}
add_action( 'init', 'remp_cmb2_field_type_crypt' );


?>