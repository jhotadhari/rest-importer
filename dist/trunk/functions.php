<?php
/*
	grunt.concat_in_order.declare('init');
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// load_plugin_textdomain
function remp_load_textdomain(){
	
	load_plugin_textdomain(
		'remp',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'remp_load_textdomain' );






?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Admin_Notice');
	grunt.concat_in_order.require('init');
*/

Class Remp_Admin_Notice {


	protected $admin_notice = '';
	protected $to_log = false;


	function __construct( $notice, $is_error = false, $to_log = false ) {
		$this->admin_notice = $this->set_admin_notice( $notice, $is_error );
		$this->to_log = $to_log;
		add_action( 'admin_notices', array( $this, 'print_admin_notice') );
	}
	
	protected function set_admin_notice( $notice, $is_error ){
		
		$style = $is_error ? 'style="color: red; font-size: large;"' : '';
		$tag_open = '<p ' . $style . '>';
		$tag_close = '</p>';
			
		if( gettype( $notice ) == 'string' || is_numeric( $notice ) ){
			$admin_notice = $tag_open . $notice . $tag_close;
		} elseif ( gettype( $notice ) == 'array' ){

			// if ( count($notice) !== count($notice, COUNT_RECURSIVE) ) {	// is multidimensional
				
				ob_start();
				print('<pre ' . $style . '>');    
				print_r( $notice );
				print('</pre>');
				$admin_notice = ob_get_contents();
				ob_end_clean();
			
			// } else {	// is onedimensional
			// 	$admin_notice = $tag_open . implode( $tag_close . $tag_open , $notice ) . $tag_close;		
			// }
		} else {
			$admin_notice = $notice;
		}
		
		return $admin_notice;
		
	}
	
	public function print_admin_notice(){
		print( $this->admin_notice );
		
		if ( $this->to_log ){
			error_log( $this->admin_notice );
		}
	}


}

?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Admin_Options');
	grunt.concat_in_order.require('init');
*/
/**
 * CMB2 Plugin Options
 * @version 0.1.0
 */
class Remp_Admin_Options {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'remp_options';
	
	private $tabs = array(
		'sources' => array(
			'title' => 'Sources',
		),
		'mapping' => array(
			'title' => 'Value Mapping',
		),
		'import' => array(
			'title' => 'Requests & Import',
			'metabox_form_args' => array(
				'save_button' => 'Requests/Import'
				)
		),
		'settings' => array(
			'title' => 'Settings',
		),
	);
	
	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_ids = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';
	

	/**
	 * Options Page hook
	 * @var string
	 */
	// protected $options_page = '';
	protected $options_pages = array();

	/**
	 * Holds an instance of the object
	 *
	 * @var Remp_Admin_Options
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return Remp_Admin_Options
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	protected function __construct() {
		// Set our title
		$this->title = __( 'REST Importer', 'remp' );
		
		// Set metabox ids
		foreach( $this->tabs as $key => $val ) {
			$this->metabox_ids[$key] = array( 'metabox_id'	=>	$this->key . '_' . $key );
			foreach( $val as $k => $v ) {
				$this->metabox_ids[$key][$k] = $v;
			}
		}
		
	}

	
	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		
		foreach( $this->metabox_ids as $key => $val ) {
			add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' . '__' . $key ) );
			add_action( 'cmb2_after_options-page_form_' . $val['metabox_id'], array( $this, 'enqueue_style_script'), 10, 2 );
		}
		
		add_action( 'cmb2_after_init', array( $this, 'handle_submission') );

	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		foreach( $this->metabox_ids as $key => $val) {
			register_setting( $this->key, $key );
		}
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		
		$this->options_page = add_submenu_page( 'tools.php', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		
		// get active tab
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : array_keys( $this->metabox_ids )[0];
		
		echo '<div class="wrap cmb2-options-page ' . $this->key . '">';
			echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';
			
			// navigation tabs
			echo '<h2 class="nav-tab-wrapper">';
				foreach( $this->metabox_ids as $key => $val) {
					echo '<a href="?page=remp_options&tab=' . $key . '" class="nav-tab' . ($key === $active_tab ? ' nav-tab-active' : '') . '">' . __( $val['title'], 'remp') . '</a>';
				}
			echo '</h2>';

			// form
			cmb2_metabox_form(
				$this->metabox_ids[$active_tab]['metabox_id'],
				$this->key,
				isset( $this->metabox_ids[$active_tab]['metabox_form_args'] ) ? $this->metabox_ids[$active_tab]['metabox_form_args'] : array()
			);
			
		echo '</div>';
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	public function add_options_page_metabox__sources() {
		$tab = 'sources';
		
		$metabox_id = $this->key . '_' . $tab;
		
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		
		$cmb->add_field( array(
			'name' => __( 'Connection Settings', 'remp' ),
			'id'   => 'title',
			'type' => 'title',
		) );
		
		
		
		
		$group_field_id = $cmb->add_field( array(
			'id'          => 'sources',
			'type'        => 'group',
			// 'description' => __( 'Generates reusable form entries', 'remp' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Source {#}', 'remp' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Source', 'remp' ),
				'remove_button' => __( 'Remove Source', 'remp' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Source id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'remp' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'remp' ),
			'type' => 'slug',
			'repeatable' => false,
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Authorization', 'remp' ),
			// 'desc' => __( 'field description (optional)', 'remp' ),
			'id'   => 'authorization',
			'type' => 'radio',
			'default' => 'none',
			'attributes' => array(
				'required'    => true,
			),
			'options' => array(
				'none' => __( 'None', 'remp' ),
				'oauth1_one_leg' => __( 'OAuth 1.0a one-legged', 'remp' ),
				)
		) );

		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Resource Url', 'remp' ),
			'id'   => 'resource_url',
			'type' => 'text',
			'repeatable' => false,
			'attributes' => array(
				'required'    => true,
			)
		) );	
		
		
		
		
		// oauth1_one_leg
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Consumer Key', 'remp' ),
			// 'desc' => __( 'field description (optional)', 'remp' ),
			'id'   => 'consumer_key',
			'type' => 'crypt',
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'authorization' ) ),
				'data-conditional-value' => 'oauth1_one_leg',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Consumer Secret', 'remp' ),
			// 'desc' => __( 'field description (optional)', 'remp' ),
			'id'   => 'consumer_secret',
			'type' => 'crypt',
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'authorization' ) ),
				'data-conditional-value' => 'oauth1_one_leg',
			)
		) );
		
		

	}
	
	

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	public function add_options_page_metabox__mapping() {
		$tab = 'mapping';
		
		$metabox_id = $this->key . '_' . $tab;
		
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
	
		$cmb->add_field( array(
			'name' => __( 'How to store the response in the database?', 'remp' ),
			'id'   => 'title',
			'type' => 'title',
		) );
		
		
		$cmb->add_field( array(
			'id'   => 'mapping_info_wiki',
			'type' => 'info',
			'attributes' => array(
				'required'    => false,
				'paragraph' => true,
			),
			'info' => 
				'<p>' . __( 'Are you confused?', 'remp' ) . ' ' . __( 'Check out the wiki:', 'remp' ) . ' <a href="https://github.com/jhotadhari/rest-importer/wiki/Value-Mapping" target="_blank">Value Mapping</a>' . '</p>' . 
				'<p>' . __( 'Want a real example? Switch to the Import tab, do some test requests and print them as admin notice', 'remp' ) . '</p>',
		) );
		
		
		
		$group_field_id = $cmb->add_field( array(
			'id'          => 'value_map',
			'type'        => 'group',
			// 'description' => __( 'Generates reusable form entries', 'remp' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Map {#}', 'remp' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Map', 'remp' ),
				'remove_button' => __( 'Remove Map', 'remp' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );
		
		
		/* row 1  */
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Map id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'remp' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'remp' ),
			'type' => 'slug',
			'repeatable' => false,
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'WP Object Type', 'remp' ),
			'description' => __( 'Save the response as WordPress objects.', 'remp' ),
			'id'   => 'object_type',
			'type'             => 'radio',
			'show_option_none' => false,
			'options'          => array(
				'post' => __( 'Post', 'remp' ),
				'user' => __( 'User', 'remp' ),
			),
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		

		
		/* row 2 post */
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Post type', 'remp' ),
			'id'   => 'post_type',
			'type'             => 'radio',
			'show_option_none' => false,
			'options_cb' => 'remp_get_post_types_arr',
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'post',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Post status', 'remp' ),
			'id'   => 'post_status',
			'type'             => 'radio',
			'show_option_none' => false,
			'options_cb' => 'get_post_statuses',
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'post',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Post Title', 'remp' ),
			'id'   => 'post_title',
			'description' => __( 'Default post title, may be overwritten by the mapping tree or your filter functions.', 'remp' ),
			'type'       => 'text',
			'attributes' => array(
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'post',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(    
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
				
		
		
		/* row 3 user */
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'User Role', 'remp' ),
			// 'description' => __( 'Save the response as WordPress objects.', 'remp' ),
			'id'   => 'role',
			'type'             => 'radio',
			'show_option_none' => false,
			'options_cb' => array( $this, 'options_cb_role'),
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'user',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Unique user_login and user_email', 'remp' ),
			'id'   => 'info_user_email_login',
			'type' => 'info',
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'user',
				'paragraph' => false,
			),
			'info' => 
				__( 'user_email is required!', 'remp' ) . '<br>'  .
				__( 'If no user_login is set, the user_mail or something random will be used instead.', 'remp' ) . '<br>'  .
				__( 'The user_login is quite fix and can\'t be changed later without magic.', 'remp' ) . '<br>'  .
				__( 'If no valid email is set, import will skip this user.', 'remp' ),
			
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'If either user_login or user_email already exist or both exist and belong to same existing user', 'remp' ),
			'id'  			 => 'user_exists',
			'desc'           => __( 'If user_email and user_login exist but don\'t belong to the same existing user, the entry will be skipped.', 'remp' ),
			'type'             => 'radio',
			'show_option_none' => false,
			'options'          => array(
				'skip' => __( 'Skip entry', 'remp' ),
				'merge_carefully' => __( 'Merge carefully, don\'t overwride', 'remp' ),
				'merge_overwride' => __( 'Merge rude and overwride', 'remp' ),
			),
			'attributes' => array(
				'required'    => true,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'user',
			)
		) );
		
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		

		/* row 4 */
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Define Root', 'remp' ),
			'id'   => 'root',
			'description' => 
				__( 'Where to start traversing the response tree?', 'remp' ) . '<br>' . 
				__( 'Seperator (without quotes): "=>"', 'remp' ) . '<br>' . 
				__( 'Example to loop lovely_people (without quotes): "results=>lovely_people"', 'remp' ) . '<br>' . 
				__( 'If empty, the root will be the actual root of the response.', 'remp' ),
			'type' => 'text',
		) );
		

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Valid Option Keys for Object Post', 'remp' ),
			'description' => 
				__( 'For object-meta all keys are valid.', 'remp' ) . '<br>' . 
				__( 'Some info:', 'remp' ) . ' <a href="https://codex.wordpress.org/Class_Reference/WP_Post#Member_Variables_of_WP_Post" target="_blank">Class Reference/WP Post</a>',
			'id'   => 'post_valid_option_keys',
			'type'             => 'info',
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'post',
				'paragraph' => false,
			),
			'info' => 
				__( 'Everything else will be skipped', 'remp' ) . ':<br>'  .
				remp_get_valid_option_keys( 'post', true ),
			
		) );		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Valid Option Keys for Object User', 'remp' ),
			'description' => 
				__( 'For object-meta all keys are valid.', 'remp' ) . '<br>' . 
				__( 'Some info:', 'remp' ) . ' <a href="https://codex.wordpress.org/Function_Reference/wp_insert_user" target="_blank">Function_Reference/wp_insert_user</a>',
			'id'   => 'user_valid_option_keys',
			'type'             => 'info',
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'user',
				'paragraph' => false,
			),
			'info' => 
				__( 'Everything else will be skipped (user_email is required!)', 'remp' ) . ':<br>'  .
				remp_get_valid_option_keys( 'user', true ),
		) );		
		
		
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		/* row 5 */
		
		$cmb->add_group_field( $group_field_id, array(
			'desc' => 
				__( 'Rebuild the response structure and assign the nodes as values (string|array) to WordPress objects and meta.', 'remp' ) . '<br>' .
				__( 'The root node represents the defined root.', 'remp' ) . '<br>' . 
				__( 'Right click on the fileds and nodes to edit.', 'remp' ),
			'id'   => 'map_tree',
			'type' => 'tree',
		) );
		
	}
	
	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	public function add_options_page_metabox__import() {
		$tab = 'import';
		
		$metabox_id = $this->key . '_' . $tab;
		
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		
		
		$cmb->add_field( array(
			'name' => __( ' ', 'remp' ),	// ???
			'id'   => 'title',
			'type' => 'title',
		) );
		
		
		$group_field_id = $cmb->add_field( array(
			'id'          => 'request',
			'type'        => 'group',
			// 'description' => __( 'Generates reusable form entries', 'remp' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Request {#}', 'remp' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Request', 'remp' ),
				'remove_button' => __( 'Remove Request', 'remp' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );
		
		
		/* row 1 */
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Request id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'remp' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'remp' ),
			'type' => 'slug',
			'repeatable' => false,
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'State', 'remp' ),
			'id'   => 'state',
			'default' => array('disabled'),
			'type' => 'radio',
			'select_all_button' => false,
			'options' => array(
				'disabled' => __( 'Disabled', 'remp' ),
				'save' => __( 'Do now', 'remp' ),
				'cron' => __( 'Do regularly', 'remp' ) . ' (wp_cron)',
			),
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Source', 'remp' ),
			'id'   => 'source',

			'description' => __( 'Choose a source', 'remp' ),
			'type'             => 'radio',
			'options_cb' => array( $this, 'options_cb_source'),
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		/* row 2 */
		
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'wp_cron', 'remp' ),
			'id'   => 'cron_info',
			'type'             => 'info',
			'description' => 
				sprintf( __( 'To change the request parameter during the request use the %s filter. ', 'remp' ), 'remp_request_{$request_id}_params' ) . ' ' . 
				__( 'Where {$request_id} is this Request id. It takes four parameters and the last one is the timestamp of the last request.', 'remp' ) . ' ' . 
				__( 'Read the source code for further information', 'remp' ),
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'state' ) ),
				'data-conditional-value' => 'cron',
				'paragraph' => false,
			),
			'info' => __("It's wp_cron, so it won't be in time but will be done sometime.", "remp" ),
		) );			
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Cron Schedule', 'remp' ),
			'id'   => 'cron_schedule',
			'description'   => __( 'How to add more options:', 'remp' ) . ' <a href="https://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules" target="_blank">Filter_Reference/cron_schedules</a>',
			'type' => 'radio',
			'select_all_button' => false,
			'options_cb' => array( $this, 'options_cb_cron_schedule'),
			'attributes' => array(
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'state' ) ),
				'data-conditional-value' => 'cron',
				'required'    => true,
			)
		) );
		
		
		
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		/* row 3 */
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'What to do?', 'remp' ),
			'id'   => 'output_method',
			'type' => 'multicheck',
			'select_all_button' => false,
			'options' => array(
				'admin_notice' => __( 'Print as admin notice', 'remp' ),
				'log' => __( 'Print to debug log', 'remp' ),
				'save' => __( 'Save response', 'remp' ),
			),
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Save response', 'remp' ),
			'id'   => 'value_map',
			'description' => __( 'Select from value maps', 'remp' ),
			'type' => 'radio',
			// 'select_all_button' => false,
			'options_cb' => array( $this, 'options_cb_value_map'),
			'attributes' => array(
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'output_method' ) ),
				'data-conditional-value' => 'save',
			)
		) );
		
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		
		/* row 4 */
		               
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Parameter', 'remp' ),
			'id'   => 'param',
			'type' => 'key_val',
			'repeatable' => true,
		) );		
		

	}
	
	
	
	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	public function add_options_page_metabox__settings() {
		$tab = 'settings';
		
		$metabox_id = $this->key . '_' . $tab;
		
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		
		
		
		$cmb->add_field( array(
			'name' => __( 'Deactivation Settings', 'remp' ),
			'id'   => 'title',
			'type' => 'title',
		) );
		
		
		$cmb->add_field( array(
			'name' => __( 'Delete Settings and log', 'remp' ),
			'id'   => 'deact_delete',
			'description'   =>
				__( 'Do you want to delete the REST Importer settings and log on plugin deactivation?', 'remp' ) . '<br>' . 
				__( 'This will not delete the imported Posts and Users.', 'remp' ),
			'type' => 'radio',
			'default' => 'no',
			'options' => array( 
				'no'			=> __( 'No, remember everything for next time.', 'remp' ),
				'del_all'   => __( 'Delete all Settings and log.', 'remp' ),
			),
		) );		
		
		
		
	}
	
	protected function get_metabox_by_nonce( $nonce, $return = 'metabox' ) {
		if (! $nonce || ! strpos($nonce, 'nonce_CMB2php') === 0 )
			return false;
		
		$metabox_id = str_replace( 'nonce_CMB2php', '', $nonce );
		
		switch ( $return ){
			case 'metabox':
				return cmb2_get_metabox( $metabox_id, $this->key );
				break;
			case 'metabox_id':
				return $metabox_id;
				break;				
			case 'tab_name'
:
				return str_replace( $this->key . '_', '', $metabox_id );
				break;
			default:
				// silence ...
		}
		
	}
	
	public function handle_submission() {
		
		// is form submission?
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) return false;
		// is remp_options form submission?
		if ( ! $_POST['object_id'] == $this->key ) return false;
		
		// get nonce, metabox, tab_name
		$nonce = array_keys( remp_preg_grep_keys('/nonce_CMB2php\w+/', $_POST ) )[0];
		$tab_name = $this->get_metabox_by_nonce( $nonce, 'tab_name');
		$cmb = $this->get_metabox_by_nonce( $nonce );
		if (! $cmb ) return false;
		
		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			new Remp_Admin_Notice( array('Something went wrong.','Nonce verification failed.'), true );
			return;
		}
		
		// Fetch sanitized values
		$sanitized_values = $cmb->get_sanitized_values( $_POST );


		switch ( $tab_name ){
			case 'import':
				if ( empty($_POST['request']) ) return;
				
				// foreach ( $_POST['request'] as $request_k => $request_v ){
				foreach ( $_POST['request'] as $request ){
				
					// skip request if disabled
					if ( ! isset( $request['state'] ) || empty( $request['state'] ) || $request['state'] === 'disabled' ) continue;

					// skip request if no output
					if ( ! isset( $request['output_method'] ) || empty( $request['output_method'] ) || $request['output_method'] === null ) continue;
					
					// if state do it now, do request 
					if ( $request['state'] === 'save' ){
						remp_request( $request );
					}
					
				}

				break;
				
			default:
				// silence ...
				
		}
		
	}
	
	
	
	// option callback functions
	public function options_cb_source() {
		$array = cmb2_get_option( $this->key, 'sources', null );
		return empty( $array ) ? array() : $this->get_options_key_value( $array );
	}
	public function options_cb_value_map() {
		$array = cmb2_get_option( $this->key, 'value_map', null );
		return empty( $array ) ? array() : $this->get_options_key_value( $array );
	}
	protected function get_options_key_value( $array ){
		$return = array();
		foreach ( $array as $arr ) {
			foreach ( $arr as $key => $entry ) {
				if ( $key == 'id' ){
					$return[$entry] = $entry;
				}
			}
		}
		return $return;	
	}
	
	public function options_cb_role() {
		$editable_roles = get_editable_roles();
		$roles = array();
		foreach ( (array) $editable_roles as $role => $details) {
			if ( $role == 'administrator' ) continue;
			$roles[esc_attr($role)] = translate_user_role($details['name']);
		}
		return $roles;
	}
	
	public function options_cb_cron_schedule() {
		$schedules = wp_get_schedules();
		$arr = array();
		foreach ( (array) $schedules as $key => $val) {
			$arr[$key] = $val['display'];
		}
		return $arr;	
	}
	

	
	
	
	public function enqueue_style_script( $post_id, $cmb ) {
		wp_enqueue_style( 'remp_options_page', plugin_dir_url( __FILE__ ) . 'css/remp_options_page.min.css', false );
		
		wp_enqueue_script('remp_options_page', plugin_dir_url( __FILE__ ) . 'js/remp_options_page.min.js', array( 'jquery' ));
		
		// load cmb2-conditionals script
		// load a version that inits an non page-edit pages as well
		// wp_register_script('cmb2-conditionals', plugin_dir_url( __FILE__ ) . 'includes/jcchavezs/cmb2-conditionals/cmb2-conditionals.js', array( 'jquery', 'cmb2-scripts' ));
		wp_enqueue_script('cmb2-conditionals', plugin_dir_url( __FILE__ ) . 'js/cmb2-conditionals.min.js', array( 'jquery', 'cmb2-scripts' ));
	}
	

	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'remp' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}
	

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'tabs', 'metabox_ids', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get/return the Remp_Admin_Options object
 * @since  0.1.0
 * @return Remp_Admin_Options object
 */
function remp_admin() {
	return Remp_Admin_Options::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function remp_get_option( $key = '', $default = null ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( remp_admin()->key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( remp_admin()->key, $key, $default );

	$val = $default;

	if ( gettype($opts) === 'array' && !empty($opts) ){
		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}
	}

	return $val;
}

// Get it started
remp_admin();




?>
<?php
/*
	grunt.concat_in_order.declare('Remp_cron');
	grunt.concat_in_order.require('init');
*/


class Remp_cron {
	
	protected $request;
	
	function __construct( $request, $hook ){

		$this->request = $request;
		$recurrence = $request['cron_schedule'];
		
        // delete the task, if the recurrence doesn't match or recurrence doesn't exist
        if ( $recurrence !== wp_get_schedule( $hook ) || ! array_key_exists( $recurrence, wp_get_schedules() ) ) {
            wp_clear_scheduled_hook( $hook );
        }
        
        // add task, if not scheduled and recurrence exists
		if ( ! wp_next_scheduled( $hook ) && array_key_exists( $recurrence, wp_get_schedules() ) ) {
			wp_schedule_event( time(), $recurrence, $hook );
		}

		// task callback
		add_action( $hook, array( $this, 'task_function') );
	
	}
	
	
	public function task_function() {
	  remp_request( $this->request );
	}

}



function remp_cron_init(){
	$requests = remp_get_option( 'request', array() );
	
	// add cron tasks
	$hooks = array();
	foreach ( $requests as $request ){
		if ( $request['state'] === 'cron' ){
			$hook = 'remp_' . $request['id'];
			$hooks[] = $hook;
			new Remp_cron( $request, $hook );
		}
	}
	
	// clear unused cron tasks
	remp_cron_clear( $hooks );
	
}
add_action( 'init', 'remp_cron_init' );



function remp_cron_clear( $ignore = array() ){

	$crons = _get_cron_array();
	foreach( $crons as $cron ){
		foreach( $cron as $cron_key => $cron_val ){
			if ( strpos( $cron_key, 'remp_' ) === 0 && strpos( $cron_key, 'remp_' ) !== false ){

				if ( ! in_array( $cron_key, $ignore ) ) {
					wp_clear_scheduled_hook( $cron_key );
				}
				
			}
		}
	}

}

?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Import');
	grunt.concat_in_order.require('init');
*/


Class Remp_Import {
	
	protected $map_tree = array();
	protected $objs = array();
	protected $obj_data = array();
	protected $obj_meta = array();
	protected $object_defaults = array();
	protected $object_meta_defaults = array();
	protected $request_id = '';

	function __construct( $response_body_arr = null, $args ) {
		if ( $response_body_arr === null ) return false;
		
		// parse_args
		$args = wp_parse_args( $args, array(
			'id' => '',
			'state' => '',
			// 'source' => array(),
			'output_method' => array(),
			'value_map' => array(),
			// 'params' => array(),
		) );
		
		$this->request_id = $args['id'];
		
		// decode map tree to array and skip some jstree markup
		$this->set_map_tree( $args['value_map']['map_tree'] );
		
		// skip some steps, set response_body_arr to new root
		$root = isset( $args['value_map']['root'] ) && strlen( $args['value_map']['root'] ) > 0 ? explode( '=>' , $args['value_map']['root'] ) : array();
		$this->objs = $response_body_arr;
		foreach ( $root as $step ){
			$this->objs = remp_array_key_search( $step , $this->objs, false );
		}
		
		// set object data and meta defaults
		$this->set_object_defaults( $args );
		$this->set_object_meta_defaults( $args );
		$this->something_with_args( $args );

		// start import
		foreach ( $this->objs as $obj ){
			// empty obj data
			$this->obj_data = array();
			$this->obj_meta = array();
			// assign response nodes to obj data
			$this->climb_map_tree( $this->map_tree, $obj );		
			// insert object
			$this->insert_object( $obj );
		}
		
	}

	
	protected function set_map_tree( $map_tree ){
		// map_tree to array
		$map_tree_arr = json_decode( $map_tree, true );
		if ( $map_tree_arr === null ){
			new Remp_Admin_Notice( 'something went wrong parsing the map tree', true , true );
			return false;
		}
		// does map_tree has nodes?
		if ( array_key_exists( 'children', $map_tree_arr[0] ) ) {
			$this->map_tree = $map_tree_arr[0]['children'];
		} else {
			new Remp_Admin_Notice( 'No nodes in map tree?', true , true );
			return false;		
		}
	}
	
	
	protected function climb_map_tree( $map_tree, $obj ){
		foreach ( $map_tree as $branch ){
			
			// check if node has text and both columns have entries
			if ( !empty( $branch['data']['table'] ) && ( isset( $branch['data']['key'] ) && strlen( $branch['data']['key'] ) > 0 ) && ( isset( $branch['text'] ) && strlen($branch['text']) > 0 ) ){
				
				// check if node text exists as key in response
				if ( isset( $obj[$branch['text']] ) && (														// exists?
						( gettype($obj[$branch['text']]) === 'string' && strlen( $obj[$branch['text']] ) > 0 )	// if string, it needs some length
						|| ( is_numeric($obj[$branch['text']]) )												// if numeric ... IT'S OK To BE 0!
						|| ( gettype($obj[$branch['text']]) === 'array' && count( $obj[$branch['text']] ) > 0 )	// if array, it shouldn't be empty
					)) {

					if ( $branch['data']['table'] == 'object' ){
						// save as object data
						$this->obj_data[$branch['data']['key']] = $obj[$branch['text']];
					} elseif ( $branch['data']['table'] == 'object-meta' ) {
						// save as meta
						$this->obj_meta[$branch['data']['key']] = $obj[$branch['text']];
					}

					
				}
				
			}
			
			// climb down
			if ( ( isset( $branch['text'] ) && strlen($branch['text']) > 0 ) && array_key_exists( 'children', $branch ) && count($branch['children']) > 0 ){
				$this->climb_map_tree( $branch['children'], $obj[$branch['text']] );

			}
			
		}
	}
	
	
	protected function set_object_defaults( $args ){
		// all may be done in inherited class method
		// this method should be called when method in inherited class is finsihed
		
		$this->object_defaults = apply_filters( "remp_import_obj_defaults", ( $this->object_defaults ? $this->object_defaults : array() ), $this->request_id );
		$this->object_defaults = apply_filters( "remp_import_obj_defaults_{$this->request_id}", $this->object_defaults, $this->request_id );
	}
	
	protected function set_object_meta_defaults( $args ){
		// all may be done in inherited class method
		// this method should be called when method in inherited class is finsihed
		
		$this->object_meta_defaults = apply_filters( "remp_import_obj_meta_defaults", ( $this->object_meta_defaults ? $this->object_meta_defaults : array() ), $this->request_id );
		$this->object_meta_defaults = apply_filters( "remp_import_obj_meta_defaults_{$this->request_id}", $this->object_meta_defaults, $this->request_id );
	}		
	
	protected function something_with_args( $args ){
		//	hey inherited class, overwride me if you need some args
	}		
	
	protected function insert_object( $obj ){
		//	hey inherited class, please do something here
	}
	
}



?>
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
<?php
/*
	grunt.concat_in_order.declare('remp_request');
	grunt.concat_in_order.require('init');
*/



// wrapper function to init Remp_Request class
function remp_request( $request ){
	
	$error = new WP_Error();
	
	if ( ! isset( $request['id'] ) || empty( $request['id'] ) || $request['id'] === null ){
		$request['id'] = 'No Request ID';
		$error->add( 'remp', 'REST Importer: ' . $request['id'] );
	}

	// skip request if no output
	if ( ! isset( $request['output_method'] ) || empty( $request['output_method'] ) || $request['output_method'] === null ){
		$error->add( 'remp', 'REST Importer: No output method for: ' . $request['id']  );
	}
	
	// get source
	$sources = remp_get_option( 'sources', false );
	if ( $sources && isset( $request['source'] ) ){
		foreach( $sources as $source ){
			if ( $source['id'] == $request['source']) 
				break;
		}
	} else {
		$error->add( 'remp', 'REST Importer: No sources for: ' . $request['id']  );
	}
	
	// get value_map
	$value_maps = remp_get_option( 'value_map', false );
	if ( isset( $request['value_map'] ) && $value_maps ){
		foreach( $value_maps as $value_map ){
			if ( $value_map['id'] == $request['value_map']) 
				break;
		}
	} else {
		$value_map = false;
		if ( in_array('save', $request['output_method']) ){
			$error->add( 'remp', 'REST Importer: Value Map is required if you want to save the response. Request: ' . $request['id']  );
		}
	}
	
	// if errors ...
	if ( count( $error->get_error_messages() ) > 0 ){
		new Remp_Admin_Notice( $error->get_error_messages(), true , true );
		return false;
	}

	// params to key val
	$params = array();
	if ( isset($request['param']) && !empty($request['param']) ){
		foreach( $request['param'] as $param ){
			if ( isset( $param['key'] ) && ! empty( $param['key'] ) ){
				$params[ $param['key'] ] = $param['val'];
			}
		}
	}
	
	// run
	$args = array(
		'id'			=>	remp_slugify( $request['id'] ),
		'state'			=>	$request['state'],
		'source'		=>	$source,
		'output_method'	=>	$request['output_method'],
		'value_map'		=>	$value_map,
		'params'		=>	$params,
	);
	
	$Get = 'Remp_Request' . ( $source['authorization'] == 'none' ? '' : '_' . $source['authorization'] );
	new $Get( $args );

}
?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Request');
	grunt.concat_in_order.require('init');
*/


Class Remp_Request {
	
	protected $request_url;

	function __construct( $args ) {
		$defaults = array(
			'id' => '',
			'state' => '',
			'source' => array(),
			'output_method' => array(),
			'value_map' => array(),
			'params' => array(),
		);	
		$args = wp_parse_args( $args, $defaults );
		
		
		// get remp_log and last_requests
		$remp_log = get_option( 'remp_log', array() );
		$last_requests = array_key_exists( $args['id'], $remp_log ) ? $remp_log[$args['id']] : false;
		$last_request_time = !empty( $last_requests ) ? key( array_slice( $last_requests, -1, 1, true ) ) : false;
		
		// filter the params. useful for cron jobs
		$args['params'] = apply_filters( "remp_request_{$args['id']}_params", $args['params'], $args, $last_requests, $last_request_time );
		
		
		// set request_url
		$this->set_request_url( $args['source'], $args['params'] );
		
		//  get remote data
		$response_body_arr = $this->request_url ? $this->get_remote( $this->request_url ) : false;
		
		
		
		if ( $args['output_method'] == null ) return false;
		
		// Print as admin notice
		if ( in_array('admin_notice', $args['output_method']) ){
			new Remp_Admin_Notice( $response_body_arr );
		}
		
		// Print to debug log
		if ( in_array('log', $args['output_method']) ){
			ob_start();
			print('<pre>');    
			print_r( $response_body_arr );
			print('</pre>');
			$log = ob_get_contents();
			ob_end_clean();
			error_log( $log );
		}		

		// save response
		if ( in_array('save', $args['output_method']) ){
			$Import = 'Remp_Import' . '_' . $args['value_map']['object_type'];
			new $Import( $response_body_arr, $args );
		}
		
		// save request to log
		$entry = apply_filters( "remp_log_entry_{$args['id']}", array(), $args );	// eg: add the parameter to the log
		$log_max = apply_filters( "remp_log_max_{$args['id']}", 5 );
		if ( array_key_exists( $args['id'], $remp_log ) ) {
			// add log
			$remp_log[$args['id']][time()] = $entry;
			// delete old logs
			if ( count( $remp_log[$args['id']] ) > $log_max ){
				$remp_log[$args['id']] = 
				array_slice( $remp_log[$args['id']] , ( $log_max * -1 ), $log_max, true);
			}
		} else {
			// add log
			$remp_log[$args['id']] = array(
				time() => $entry
			);
		}
		update_option( 'remp_log', $remp_log );
		
		
		// request finished, everything done, do something
		do_action( "remp_request_finished_{$args['id']}", $args, $remp_log[$args['id']] );
		
	}
	
	
	
	protected function set_request_url( $source, $params ) {
		$this->request_url = add_query_arg( $params, $source['resource_url'] );
	}
	
	
	
	protected function get_remote( $request_url ) {
		$response = wp_remote_get( $request_url );
		if ( is_wp_error( $response ) ){
			new Remp_Admin_Notice( $response->get_error_messages(), true );
			return false;
		}
		
		$response_body = wp_remote_retrieve_body( $response );
		$response_body_arr = json_decode( $response_body, true );
		
		return $response_body_arr;
	}
	
}




?>
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
<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_key_val');
	grunt.concat_in_order.require('init');
*/

/**
 * A key_val type for CMB2
 *
 * Input field for key value pairs
 *
 * @package    CMB2
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License v2
 * @author     jhotadhari <tellme@waterproof-webdesign.info>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


function cmb2_init_key_val_field() {
	
	/**
	 * Handles 'key_val' custom field type.
	 */
	class CMB2_Render_Key_val_Field extends CMB2_Type_Base {
	
		public static function init() {
			add_filter( 'cmb2_render_class_key_val', array( __CLASS__, 'class_name' ) );
			add_filter( 'cmb2_sanitize_key_val', array( __CLASS__, 'maybe_save_split_values' ), 12, 4 );
	
			/**
			 * The following snippets are required for allowing the key_val field
			 * to work as a repeatable field, or in a repeatable group
			 */
			add_filter( 'cmb2_sanitize_key_val', array( __CLASS__, 'sanitize' ), 10, 5 );
			add_filter( 'cmb2_types_esc_key_val', array( __CLASS__, 'escape' ), 10, 4 );
		}
	
		public static function class_name() { return __CLASS__; }
	
		/**
		 * Handles outputting the key_val field.
		 */
		public function render() {
			
			// make sure we specify each part of the value we need.
			$value = wp_parse_args( $this->field->escaped_value(), array(
				'key' => '',
				'val' => '',
			) );
			
	
			 // render field
			ob_start();
			echo '<div class="alignleft entry">';
				// echo '<p><label for="' . $this->_id( '_key' ) . '">Key</label></p>';
				echo $this->types->input( array(
					'name'  => $this->_name( '[key]' ),
					'id'    => $this->_id( '_key' ),
					'value' => $value['key'],
					'desc'  => '',
				) );
			echo '</div>';
			
			echo '<div class="alignleft seperator">';
			   echo '->';
			echo '</div>';
			
			echo '<div class="alignleft entry">';
				// echo '<p><label for="' . $this->_id( '_val' ) . '">Value</label></p>';
				echo $this->types->input( array(
					'name'  => $this->_name( '[val]' ),
					'id'    => $this->_id( '_val' ),
					'value' => $value['val'],
					'desc'  => '',
				) );
			echo '</div>';
			
			echo $this->_desc();
	
			$output = ob_get_clean();
			
			
			// enqueue_style_script
			add_action( 'admin_footer', array( $this, 'enqueue_style_script' ) );
			
			// grab the data from the output buffer.
			return $this->rendered( $output );
			
		}
		
		public function enqueue_style_script(){
			wp_enqueue_style( 'jstree', plugins_url( 'css/cmb2_field_type_key_val.min.css', __FILE__ ), false );
		}
		
	
		/**
		 * Optionally save the Key_val values into separate fields
		 */
		public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
			if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
				// Don't do the override
				return $override_value;
			}
	
			$keys = array( 'key', 'val' );
	
			foreach ( $keys as $key ) {
				if ( ! empty( $value[ $key ] ) ) {
					update_post_meta( $object_id, $field_args['id'] . 'keyval_'. $key, sanitize_text_field( $value[ $key ] ) );
				}
			}
	
			remove_filter( 'cmb2_sanitize_key_val', array( __CLASS__, 'sanitize' ), 10, 5 );
	
			// Tell CMB2 we already did the update
			return true;
		}
	
		public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
	
			// if not repeatable, bail out.
			if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
				return $check;
			}
	
			foreach ( $meta_value as $key => $val ) {
				$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
			}
	
			return array_filter($meta_value);
		}
	
		public static function escape( $check, $meta_value, $field_args, $field_object ) {
			// if not repeatable, bail out.
			if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
				return $check;
			}
	
			foreach ( $meta_value as $key => $val ) {
				$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
			}
	
			return array_filter($meta_value);
		}
	
	}
	
	
	




	// init class
	CMB2_Render_Key_val_Field::init();
}
add_action( 'cmb2_init', 'cmb2_init_key_val_field' );











?>
<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_tree');
	grunt.concat_in_order.require('init');
*/

/**
 * A tree field type for CMB2
 *
 * based on jstree (https://github.com/vakata/jstree/) and jstree-grid (https://github.com/deitch/jstree-grid)
 *
 * @package    CMB2
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License v2
 * @author     jhotadhari <tellme@waterproof-webdesign.info>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

 
/**
 * Render callback for fieldtype tree
 *
 * Hook documented in cmb2/includes/types/CMB2_Types.php
 *
 * @param array  $field              The passed in `CMB2_Field` object
 * @param mixed  $escaped_value      The value of this field escaped.
 *                                   It defaults to `sanitize_text_field`.
 *                                   If you need the unescaped value, you can access it
 *                                   via `$field->value()`
 * @param int    $object_id          The ID of the current object
 * @param string $object_type        The type of object you are working with.
 *                                   Most commonly, `post` (this applies to all post-types),
 *                                   but could also be `comment`, `user` or `options-page`.
 * @param object $field_type_object  This `CMB2_Types` object
 */
function cmb2_tree_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	
	echo '<div class="cmb2-tree-wrapper">';
	
		echo '<div class="jstree jstree-grid-wrapper"></div>';
		
		echo $field_type_object->input( array(
			'type' => 'hidden',
			'class' => 'tree-data',
			) );
		
		// // debug
		// echo $field_type_object->textarea( array(
		// 	'class' => 'tree-data',
		// 	'rows'  => 10,
		// 	) );
	
	
	echo '</div>';
	echo '<br class="clear">';

	add_action( 'admin_footer', 'cmb2_tree_enqueue_style_script' );
}
add_action( 'cmb2_render_tree', 'cmb2_tree_render_callback', 10, 5 );



/**
 * enqueue styles and scripts
 */
function cmb2_tree_enqueue_style_script(){
	wp_enqueue_style( 'jstree', plugins_url( 'includes/vakata/jstree/dist/themes/default/style.min.css', __FILE__ ), false );
	wp_enqueue_style( 'jstree_custom', plugins_url( 'css/cmb2_filed_type_tree.min.css', __FILE__ ), false );
	wp_enqueue_script('jstree', plugins_url( 'includes/vakata/jstree/dist/jstree.min.js', __FILE__ ), array( 'jquery' ));
	wp_enqueue_script('jstreegrid', plugins_url( 'includes/deitch/jstree-grid/jstreegrid.js', __FILE__ ), array( 'jquery', 'jstree' ));
	wp_enqueue_script('cmb2_field_type_tree', plugins_url( 'js/cmb2_field_type_tree.min.js', __FILE__ ), array( 'jquery', 'jstree', 'jstreegrid' ));
}



/**
 * Sanitize callback for fieldtype tree.
 *    
 * Filters the value before it is saved.
 *    
 * Filter documented in cmb2/includes/types/CMB2_Field.php
 *
 * @param bool|mixed $override_value Sanitization/Validation override value to return.
 *                                   Default false to skip it.
 * @param mixed      $value      The value to be saved to this field.
 * @param int        $object_id  The ID of the object where the value will be saved
 * @param array      $field_args The current field's arguments
 * @param object     $sanitizer  This `CMB2_Sanitize` object
 */
function cmb2_tree_sanitize_callback( $override_value, $value ) {
    return $value;
}
add_filter( 'cmb2_sanitize_tree', 'cmb2_tree_sanitize_callback', 10, 2 );


?>
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
<?php
/*
	grunt.concat_in_order.declare('remp_array_column_recursive');
	grunt.concat_in_order.require('init');
*/

function remp_array_column_recursive(array $haystack, $needle) {
	$found = array();
	array_walk_recursive($haystack, function($value, $key) use (&$found, $needle) {
		if ($key == $needle)
			$found[] = $value;
	});
return $found;
}	

?>
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
<?php
/*
	grunt.concat_in_order.declare('remp_get_post_types');
	grunt.concat_in_order.require('init');
*/


// get post types ... _builtin + custom
function remp_get_post_types( $return_type = null, $exclude = null){
	if ($return_type == null){
		$post_types = array('post', 'page');

		foreach ( get_post_types( array( '_builtin' => false), 'names' ) as $post_type ) {
		   array_push($post_types, $post_type);
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $val ) use ( $exclude ){
					return ( in_array( $val, $exclude ) ? false : true );
				} );
		}
	}
	
	if ($return_type == 'array_key_val'){
	
		$post_types = array(
			'post' => __('Post','remp'),
			'page' => __('Page','remp')
			);
		
		foreach ( get_post_types( array( '_builtin' => false), 'objects' ) as $post_type ) {
		   $post_types[$post_type->name] =  __($post_type->labels->name,'remp');
		}

		if ( $exclude == null){
			return $post_types;

		} else {
			if ( gettype( $exclude ) != 'array')
				$exclude = array($exclude);
			
			return array_filter( $post_types, function( $key ) use ( $exclude ){
					return ( in_array( $key, $exclude ) ? false : true );
				}, ARRAY_FILTER_USE_KEY );
		}
	}
	
}

// wrapper function to use as options_cb
function remp_get_post_types_arr(){
	 return remp_get_post_types( 'array_key_val' );
}


?>
<?php
/*
	grunt.concat_in_order.declare('remp_get_valid_option_keys');
	grunt.concat_in_order.require('init');
*/


function remp_get_valid_option_keys( $object_type = null, $markup = false ){
	
	if ( $markup ) {
		$return = '';
	} else {

		$return = array();
	}
	
	
	switch ($object_type){
		case 'post':
			
			// filter ???
			$valids = array(
				'post_author',
				'post_content',
				'post_title',
				'post_excerpt',
				'post_status',
				// 'comment_status',
				// 'ping_status',
				'post_password',
				'post_name',
				// 'to_ping',
				// 'pinged',
				// 'post_content_filtered',
				// 'post_parent',
				'post_type',
				// 'post_mime_type',
			);
			
			
			break;

		case 'user':
			
			// filter ???
			$valids = array(
				// 'ID',	// no not valid
				'user_email',
				'user_pass',

				'user_login',
				'user_nicename',
				'user_url',

				'display_name',
				'nickname',
				'first_name',
				'last_name',
				'description',
				'role',
				);
			
			break;

		default:
			return $return;

	}
	
	if ( $markup && $valids ) {
		$return .= '<ul>';
		foreach( $valids as $valid ){
			$return .= '<li>' . $valid . '</li>';
		}
		$return .= '</ul>';
	} else {
		$return = $valids;
	}
	
	return $return;
	
}



?>
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
<?php
/*
	grunt.concat_in_order.declare('remp_slugify');
	grunt.concat_in_order.require('init');

*/

function remp_slugify($text){
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);		// replace non letter or digits by -
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);	// transliterate
	$text = preg_replace('~[^-\w]+~', '', $text);		 // remove unwanted characters
	$text = trim($text, '-');		// trim
	$text = preg_replace('~-+~', '-', $text);		// remove duplicate -
	$text = strtolower($text);		// lowercase
	
	if (empty($text)) {
		return 'n-a';
	}
	
	return $text;
}

?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Import_post');
	grunt.concat_in_order.require('init');

	grunt.concat_in_order.require('Remp_Import');
*/

Class Remp_Import_post extends Remp_Import {
	
	function __construct( $response_body_arr = null, $args ) {
		parent::__construct( $response_body_arr, $args );
	}
	
	protected function set_object_defaults( $args ){
		$this->object_defaults = array(
			'post_author'	=> 1,	// if your admin found a way to a new user_id, please kindly filter this array in parent method
			'post_type'		=> $args['value_map']['post_type'],
			'post_status'	=> $args['value_map']['post_status'],
			'post_title'	=> array_key_exists( 'post_title', $args['value_map'] ) ? $args['value_map']['post_title'] : '',
		);
		
		// filter
		parent::set_object_defaults( $args );
	}
	
	
	protected function  insert_object( $obj ){

		// skip keys if not valid
		$valids = remp_get_valid_option_keys( 'post' );

		$obj_data = $this->obj_data;
		foreach ( $obj_data as $key => $val ){
			if ( ! in_array( $key, $valids ) ){
				unset( $obj_data[$key] );
			}
		}
		
		// get the object data
		// filter: example: set an ID, to overwride an existing post
		$this->obj_data = apply_filters( "remp_insert_{$this->request_id}_obj_data", wp_parse_args( $obj_data, $this->object_defaults ), $this->request_id, $obj );
		
		// get the object meta
		// filter: example: do some magic juggling with the meta
		$this->obj_meta = apply_filters( "remp_insert_{$this->request_id}_obj_meta", wp_parse_args( $this->obj_meta, $this->object_meta_defaults ), $this->request_id, $obj );

		// create (or update if ID is passed) post
		$post_id = wp_insert_post( $this->obj_data, true );

		// Loop through meta and save
		foreach ( $this->obj_meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		
		// do something special
		do_action( "remp_insert_{$this->request_id}_finished", $this->request_id, $obj, $post_id, $this->obj_data, $this->obj_meta );
		
	}
	
}


?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Import_user');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('Remp_Import');
*/


Class Remp_Import_user extends Remp_Import {
	
	protected $opt_user_exists;
	
	function __construct( $response_body_arr = null, $args ) {
		parent::__construct( $response_body_arr, $args );
	}
	
	
	protected function set_object_defaults( $args ){
		$this->object_defaults = array(
			'user_email'	=> '',		
			'role'			=> $args['value_map']['role'],
			'user_pass'		=> '',
		);
		// filter
		parent::set_object_defaults( $args );
	}
	
	
	protected function insert_object( $obj ){
		
		// skip keys if not valid
		$valids = remp_get_valid_option_keys( 'user' );
		$obj_data = $this->obj_data;
		foreach ( $obj_data as $key => $val ){
			if ( ! in_array( $key, $valids ) ){
				unset( $obj_data[$key] );
			}
		}

		$this->obj_data = apply_filters( "remp_insert_{$this->request_id}_obj_data", wp_parse_args( $obj_data, $this->object_defaults ), $this->request_id, $obj );
		$this->obj_meta = apply_filters( "remp_insert_{$this->request_id}_obj_meta", wp_parse_args( $this->obj_meta, $this->object_meta_defaults ), $this->request_id, $obj );
		
		// is email set 
		if ( empty( $this->obj_data['user_email'] ) ){
			new Remp_Admin_Notice( 'user_email not set.', true );
			return false;
		}

		// is email valid
		if ( gettype( $this->obj_data['user_email'] ) !== 'string' || ! is_email( $this->obj_data['user_email'] ) ){
			new Remp_Admin_Notice( 'user_email not vaild: ' . $this->obj_data['user_email'] , true );
			return false;
		}

		// is user_login set? if not, set from email
		$this->obj_data['user_login'] = empty( $this->obj_data['user_login'] ) || gettype( $this->obj_data['user_login'] ) !== 'string'
			? $this->obj_data['user_email']
			: $this->obj_data['user_login'];
			
		// is pass a string value? if not set random
		$this->obj_data['user_pass'] = empty( $this->obj_data['user_pass'] ) || gettype( $this->obj_data['user_pass'] ) !== 'string'
			? wp_hash_password( wp_generate_password( 12, true ) )
			: wp_hash_password( $this->obj_data['user_pass'] );
	
		
		// Does the user exist?
		$user_exists = array();
		$user_exists_msg = array('User already exists:');
		// user_email
		if( $id = email_exists( $this->obj_data['user_email'] ) ) {
			$user_exists['user_email'] = $id;
			$user_exists_msg[] = 'id: ' . $id . ' user_email: ' . $this->obj_data['user_email'];
		}
		// user_login
		// is user_login not set, set from email/random
		// is user_login set, but user_login already exists, remember
		if ( gettype( $this->obj_data['user_login'] ) !== 'string' || empty( $this->obj_data['user_login'] ) ){
			$this->set_user_login();
		} elseif ( $id = username_exists( $this->obj_data['user_login'] ) ) {
			$user_exists['user_login'] = $id;
			$user_exists_msg[] = 'id: ' . $id . ' user_login: ' . $this->obj_data['user_login'];
		}	
		
		
		// create, merge or skip
		if ( count( $user_exists ) == 0 ) {
		// user doesn't exist
			$user_id = $this->insert_user( $obj );
			if ( is_wp_error( $user_id ) ) {
				// ??? error handler
				return false;
			} else {


				// ??? hook here
				new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

				return true;
			}
		} else {
		// user exists
			if ( $this->opt_user_exists == 'skip' ) {
				$skip = true;
				$user_exists_msg[] = '... entry skipped';
			} else {
				if ( count( $user_exists ) == 1 ) {
					if ( ! empty($user_exists['user_email']) ) {
					// user_email exists	user_login doesn't exist
						new Remp_Admin_Notice( 'user_email exists	user_login doesnt exist' );		// ??? debug only

						$user_id = $this->merge_existing_user( get_user_by( 'ID',  $user_exists['user_email'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					} elseif ( ! empty($user_exists['user_login']) ) {
					// user_login exists	user_email doesn't exist
						new Remp_Admin_Notice( 'user_login exists	user_email doesnt exist' );		// ??? debug only

						$user_id = $this->merge_existing_user(  get_user_by( 'ID',  $user_exists['user_login'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					}
				} elseif ( count( $user_exists ) == 2 ) {
					if ( $user_exists['user_email'] == $user_exists['user_login'] ) {
					// user_login user_email belong to same existing user
						new Remp_Admin_Notice( 'user_login user_email belong to same existing user' );		// ??? debug only

						$user_id = $this->merge_existing_user(  get_user_by( 'ID',  $user_exists['user_login'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					} else {
					// user_login user_email belong to different existing users
						$skip = true;
						$user_exists_msg[] = '... entry skipped';
					}
				}
			}
		}
		
		
		if ( $skip ){
			new Remp_Admin_Notice( $user_exists_msg , true, false );
			return false;
		}
		
		// ??? debug
		new Remp_Admin_Notice( 'something went wrong' , true, true );	// this msg should never appear
		
	}
	
	
	protected function set_user_login( $set_as_email = true ){
		if ( $set_as_email ){
			if ( username_exists( $this->obj_data['user_email'] ) || strlen( $this->obj_data['user_email'] ) > 60 ) {
				$this->set_user_login( false );
				return;
			}
			$this->obj_data['user_login'] = $this->obj_data['user_email'];
		} else {
			$user_login = rand(100000,999999);
			if ( username_exists( $user_login ) ) {
				$this->set_user_login( false );
				return;
			}
			$this->obj_data['user_login'] = $user_login;
		}
	}
	
	
	protected function insert_user( $obj ){
	
		// create/update user
		$user_id = wp_insert_user( $this->obj_data ) ;
	
		// update meta
		if ( ! is_wp_error( $user_id ) ) {
			foreach ( $this->obj_meta as $key => $value ) {
				update_user_meta( $user_id, $key, $value);
			}
		}
		
		// hook
		$this->insert_finished( $obj, $user_id );
		// return id or error
		return $user_id;
		
	}
	
	
	protected function merge_existing_user( $user, $obj ){

		if ( $this->opt_user_exists == 'merge_overwride' ) {
			$this->obj_data['ID'] = $user->ID;
			$user_id = $this->insert_user( $obj );
			return $user_id;
		}
		
		
		if ( $this->opt_user_exists == 'merge_carefully' ) {

			$user_id = $user->ID;
			$user_login = $user->get('user_login');
			
			
			$userdata = array(
				'ID' => $user_id,
				'user_login' => $user_login,
			);
			
			foreach ( $this->obj_data as $new_k => $new_v ){
				if ( empty( $user->get($new_k) ) ) {
					$userdata[$new_k] = $new_v;
				} else {
					$userdata[$new_k] = $user->get($new_k);
				}
			}                                      
			
			$result = wp_insert_user( $userdata );
			if ( is_wp_error( $result ) ) {
				// ??? error handler
			}
			
			foreach ( $this->obj_meta as $new_k => $new_v ){
				$existing_meta = get_user_meta( $user_id, $new_k, true);
			
				if ( empty( $existing_meta ) ) {
					$result = update_user_meta( $user_id, $new_k, $new_v );
					if ( ! $result ) {
						// ??? error handler
					}
					
				}
			
			}
		}
	
		// hook
		$this->insert_finished( $obj, $user_id );
		return $user_id;
		
	}
	
	protected function insert_finished( $obj, $user_id ){
		// do something special
		do_action( "remp_insert_{$this->request_id}_finished", $this->request_id, $obj, $user_id, $this->obj_data, $this->obj_meta );
	}
		
	
	protected function something_with_args( $args ){
		$this->opt_user_exists = $args['value_map']['user_exists'];
	}		
	
}


?>
<?php
/*
	grunt.concat_in_order.declare('Remp_Request_oauth1_one_leg');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('Remp_Request');
*/

Class Remp_Request_oauth1_one_leg extends Remp_Request {
	
	function __construct( $args ) {
		parent::__construct( $args );
	}
	
	protected function set_request_url( $source, $params ) {
		$error = new WP_Error();
		
		$consumer_key = cmb2_crypt_decrypt( $source['consumer_key'] );
		$consumer_secret = cmb2_crypt_decrypt( $source['consumer_secret'] );
		
		// check for errors
		if (! isset( $consumer_key ) ) $error->add( 'remp', 'No consumer key is set!' );
		if (! isset( $consumer_secret ) ) $error->add( 'remp', 'No consumer secret is set!' );
		if ( count( $error->get_error_messages() ) > 0 ){
			new Remp_Admin_Notice( $error->get_error_messages(), true , true );
			return false;
		}
		
		$consumer = new Eher\OAuth\Consumer(  $consumer_key, $consumer_secret );
		
		$request = Eher\OAuth\Request::from_consumer_and_token(
			$consumer,
			null,
			'GET',
			$source['resource_url'],
			$params
		);
		$request->sign_request( new Eher\OAuth\HmacSha1(), $consumer, null);
	
		$this->request_url = $request->to_url();
	}
	
}

?>
<?php
/*
	grunt.concat_in_order.declare('cmb2_field_type_slug');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('remp_slugify');
*/

/**
 * A slug slug type for CMB2
 *
 * The value will slugified before saving
 *
 * @package    CMB2
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License v2
 * @author     jhotadhari <tellme@waterproof-webdesign.info>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

 
/**
 * Render callback for fieldtype slug
 *
 * Hook documented in cmb2/includes/types/CMB2_Types.php
 *
 * @param array  $field              The passed in `CMB2_Field` object
 * @param mixed  $escaped_value      The value of this field escaped.
 *                                   It defaults to `sanitize_text_field`.
 *                                   If you need the unescaped value, you can access it
 *                                   via `$field->value()`
 * @param int    $object_id          The ID of the current object
 * @param string $object_type        The type of object you are working with.
 *                                   Most commonly, `post` (this applies to all post-types),
 *                                   but could also be `comment`, `user` or `options-page`.
 * @param object $field_type_object  This `CMB2_Types` object
 */
function cmb2_slug_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
  
	// render field
	echo $field_type_object->input( array( 
			'type' => 'text'
		) );
	
}
add_action( 'cmb2_render_slug', 'cmb2_slug_render_callback', 10, 5 );




/**
 * Sanitize callback for fieldtype text.
 *    
 * Filters the value before it is saved.
 *    
 * Filter documented in cmb2/includes/types/CMB2_Field.php
 *
 * @param bool|mixed $override_value Sanitization/Validation override value to return.
 *                                   Default false to skip it.
 * @param mixed      $value      The value to be saved to this field.
 * @param int        $object_id  The ID of the object where the value will be saved
 * @param array      $field_args The current field's arguments
 * @param object     $sanitizer  This `CMB2_Sanitize` object
 */
function cmb2_slug_sanitize_callback( $override_value, $value ) {
    return remp_slugify( $value );
}
add_filter( 'cmb2_sanitize_slug', 'cmb2_slug_sanitize_callback', 10, 2 );


?>