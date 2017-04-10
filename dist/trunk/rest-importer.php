<?php
/*
Plugin Name: REST Importer
Plugin URI: https://github.com/jhotadhari/rest-importer
Description: Get remote data and save it as posts or users. Customize the way the data gets stored.
Version: 0.1.5
Author: jhotadhari
Author URI: http://waterproof-webdesign.info/
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: remp
Domain Path: /languages
Tags: REST,import,remote,json
*/

/*
	grunt.concat_in_order.declare('_plugin_info');
*/

?>
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
	
	if ( remp_get_option( 'deact_delete' , 'no') === 'del_all' ){
		delete_option( 'remp_options' );
		delete_option( 'remp_log' );
	}
	
}
register_deactivation_hook(__FILE__, 'remp_plugin_deactivate');

?>
<?php
/*
	grunt.concat_in_order.declare('cmb2_init');
	grunt.concat_in_order.require('init');
*/



//cmb2 init
function remp_cmb2_init() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/webdevstudios/cmb2/init.php';
}
add_action('admin_init', 'remp_cmb2_init', 3);
add_action('init', 'remp_cmb2_init', 3);






//cmb2-qtranslate init
function remp_cmb2_init_qtranslate() {
	wp_register_script('cmb2_qtranslate_main', plugin_dir_url( __FILE__ ) . '/includes/jmarceli/integration-cmb2-qtranslate/dist/scripts/main.js', array('jquery'));
	wp_enqueue_script('cmb2_qtranslate_main');
}
add_action('admin_enqueue_scripts', 'remp_cmb2_init_qtranslate');
//add_action('wp_enqueue_scripts', 'remp_cmb2_init_qtranslate');



//cmb2-conditionals
// it will initialize itself
// but the js needs to be enqueued for the Remp_Admin_Options
include_once plugin_dir_path( __FILE__ ) . 'includes/jcchavezs/cmb2-conditionals/cmb2-conditionals.php';
?>