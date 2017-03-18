<?php
/*
	grunt.concat_in_order.declare('init');
	grunt.concat_in_order.require('_plugin_info');
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


function remp_get_required_php_ver() {
	return '5.6';
}

function remp_plugin_activate(){
    if ( version_compare( PHP_VERSION, remp_get_required_php_ver(), '<') || 
    	! function_exists('curl_version')
    ) {
        wp_die( remp_get_admin_notice() . '<br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}
register_activation_hook( __FILE__, 'remp_plugin_activate' );

function remp_load_functions(){
	if ( ! version_compare( PHP_VERSION, remp_get_required_php_ver(), '<') &&  
		function_exists('curl_version')
	){
		include_once(plugin_dir_path( __FILE__ ) . 'functions.php');
	} else {
		add_action( 'admin_notices', 'remp_print_admin_notice' );
	}
}
add_action( 'plugins_loaded', 'remp_load_functions' );

function remp_print_admin_notice() {
	echo '<strong><span style="color:#f00;">' . remp_get_admin_notice() . '</span></strong>';
};

function remp_get_admin_notice() {
	$plugin_title = 'REST Importer';
	return sprintf(esc_html__( '"%s" plugin requires PHP version %s or greater and cURL enabled!', 'remp' ), $plugin_title, remp_get_required_php_ver());
}




function remp_plugin_deactivate() {
	remp_cron_clear();
}
register_deactivation_hook(__FILE__, 'remp_plugin_deactivate');

?>