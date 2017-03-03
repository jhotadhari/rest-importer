<?php
/*
	grunt.concat_in_order.declare('remp_oauth_init');
	grunt.concat_in_order.require('init');
*/

function remp_oauth_init() {
	$path = plugin_dir_path( __FILE__ ) . 'includes/eher/oauth/src/Eher/OAuth/';
	
	include_once $path . 'Consumer.php';
	include_once $path . 'SignatureMethod.php';
	include_once $path . 'HmacSha1.php';
	include_once $path . 'OAuthException.php';
	include_once $path . 'Util.php';
	include_once $path . 'Request.php';
}
add_action( 'init', 'remp_oauth_init' );

?>