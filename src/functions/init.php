<?php
/*
	grunt.concat_in_order.declare('init');
*/


// load_plugin_textdomain
function islcrm_load_textdomain(){
	
	load_plugin_textdomain(
		'islcrm',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'islcrm_load_textdomain' );






?>