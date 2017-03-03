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
			// 'description' => __( 'Generates reusable form entries', 'cmb2' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Source {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Source', 'cmb2' ),
				'remove_button' => __( 'Remove Source', 'cmb2' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Source id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'cmb2' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'cmb2' ),
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
			),
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
			'description' =>
				__( 'Are you confused?', 'cmb2' ) . '<br>' . 
				__( 'Check out response example at the bottom of the page.', 'cmb2' ),
			'id'   => 'title',
			'type' => 'title',
		) );
		
		

		
		
		
		$group_field_id = $cmb->add_field( array(
			'id'          => 'value_map',
			'type'        => 'group',
			// 'description' => __( 'Generates reusable form entries', 'cmb2' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Map {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Map', 'cmb2' ),
				'remove_button' => __( 'Remove Map', 'cmb2' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );
		
		
		/* row 1  */
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Map id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'cmb2' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'cmb2' ),
			'type' => 'slug',
			'repeatable' => false,
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'WP Object Type', 'remp' ),
			'description' => __( 'Save the response as WordPress objects.', 'cmb2' ),
			'id'   => 'object_type',
			'type'             => 'radio',
			'show_option_none' => false,
			'options'          => array(
				'post' => __( 'Post', 'cmb2' ),
				'user' => __( 'User', 'cmb2' ),
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
			'description' => __( 'Default post title, may be overwritten by the mapping tree or your filter functions.', 'cmb2' ),
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
			// 'description' => __( 'Save the response as WordPress objects.', 'cmb2' ),
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
				'info' => 
					__( 'user_email is required!', 'cmb2' ) . '<br>'  .
					// __('Make sure to assign an email address to the user.', 'cmb2' ) . '<br>'  .

					__( 'If no user_login is set, the user_mail or something random will be used instead.', 'cmb2' ) . '<br>'  .
					__( 'The user_login is quite fix and can\'t be changed later without magic.', 'cmb2' ) . '<br>'  .
					__( 'If no valid email is set, import will skip this user.', 'cmb2' ),
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'If either user_login or user_email already exist or both exist and belong to same existing user', 'remp' ),
			'id'  			 => 'user_exists',
			'desc'           => __( 'If user_email and user_login exist but don\'t belong to the same existing user, the entry will be skipped.', 'cmb2' ),
			'type'             => 'radio',
			'show_option_none' => false,
			'options'          => array(
				'skip' => __( 'Skip entry', 'cmb2' ),
				'merge_carefully' => __( 'Merge carefully, don\'t overwride', 'cmb2' ),
				'merge_overwride' => __( 'Merge rude and overwride', 'cmb2' ),
			),
			'attributes' => array(
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
				__( 'Where to start traversing the response tree?', 'cmb2' ) . '<br>' . 
				__( 'Seperator (without quotes): "=>"', 'cmb2' ) . '<br>' . 
				__( 'Example to loop lovely_people (without quotes): "results=>lovely_people"', 'cmb2' ) . '<br>' . 
				__( 'If empty, the root will be the actual root of the response.', 'cmb2' ),
			'type' => 'text',
		) );
		

		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Valid Option Keys for Object Post', 'remp' ),
			'description' => 
				__( 'For object-meta all keys are valid.', 'cmb2' ) . '<br>' . 
				__( 'Some info:', 'cmb2' ) . ' <a href="https://codex.wordpress.org/Class_Reference/WP_Post#Member_Variables_of_WP_Post" target="_blank">Class Reference/WP Post</a>',
			'id'   => 'post_valid_option_keys',
			'type'             => 'info',
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'post',
				'paragraph' => false,
				'info' => 
					__( 'Everything else will be skipped', 'cmb2' ) . ':<br>'  .
					remp_get_valid_option_keys( 'post', true ),
			)
		) );		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Valid Option Keys for Object User', 'remp' ),
			'description' => 
				__( 'For object-meta all keys are valid.', 'cmb2' ) . '<br>' . 
				__( 'Some info:', 'cmb2' ) . ' <a href="https://codex.wordpress.org/Function_Reference/wp_insert_user" target="_blank">Function_Reference/wp_insert_user</a>',
			'id'   => 'user_valid_option_keys',
			'type'             => 'info',
			'attributes' => array(
				'required'    => false,
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'object_type' ) ),
				'data-conditional-value' => 'user',
				'paragraph' => false,
				'info' => 
					__( 'Everything else will be skipped (user_email is required!)', 'cmb2' ) . ':<br>'  .
					remp_get_valid_option_keys( 'user', true ),
			)
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
		

		
		
		
		
		
		
		$cmb->add_field( array(
			'name' => __( 'Example respond', 'remp' ),
			'id'   => 'exp_respond',
			'description' => 
				__( 'This is just an example of a response tree.', 'cmb2' ) . '<br>' . 
				__( 'Want a real one? Switch to the Import tab, do some test requests and print them as admin notice.', 'cmb2' ),
			'type' => 'exp_respond',
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
		// add_action( "cmb2_save_options-page_fields_{$metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			// 'id'         => $this->metabox_id,
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
			// 'description' => __( 'Generates reusable form entries', 'cmb2' ),
			'repeatable'  => true, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Request {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Request', 'cmb2' ),
				'remove_button' => __( 'Remove Request', 'cmb2' ),
				'sortable'      => false, // beta
				'closed'     	=> true,
			),
		) );
		
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Request id', 'remp' ),
			'id'   => 'id',
			'description' => 
				__( 'A unique identifier.', 'cmb2' ) . '<br>' . 
				__( 'This should be a slug. However, it will be slugified automatically.', 'cmb2' ),
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
				'save' => __( 'Do now', 'cmb2' ),
				// 'cron' => __( 'Do regularly (cron)', 'cmb2' ),
			),
			'attributes' => array(
				'required'    => true,
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Source', 'remp' ),
			'id'   => 'source',

			'description' => __( 'Choose a source', 'cmb2' ),
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
		
		
		
		
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'What to do?', 'remp' ),
			'id'   => 'output_method',
			'type' => 'multicheck',
			'select_all_button' => false,
			'options' => array(
				'admin_notice' => __( 'Print as admin notice', 'cmb2' ),
				'log' => __( 'Print to debug log', 'cmb2' ),
				'save' => __( 'Save response', 'cmb2' ),
				// 'mail' => __( 'Send mails', 'cmb2' ),
			),
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Save response', 'remp' ),
			'id'   => 'value_map',
			'description' => __( 'Select from value maps', 'cmb2' ),
			'type' => 'radio',
			// 'select_all_button' => false,
			'options_cb' => array( $this, 'options_cb_value_map'),
			'attributes' => array(
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'output_method' ) ),
				'data-conditional-value' => 'save',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Send mails', 'remp' ),
			'id'   => 'mail',
			'description' => __( 'Select from mail templates', 'cmb2' ),
			'type' => 'multicheck',
			'select_all_button' => false,
			// 'options_cb' => array( $this, 'options_cb_mail'),
			'options' => array(
				'test1' => __( 'test 1', 'cmb2' ),
				'test2' => __( 'test 2', 'cmb2' ),
				'test3' => __( 'test 3', 'cmb2' ),
			),
			'attributes' => array(
				'data-conditional-id'    => wp_json_encode( array( $group_field_id, 'output_method' ) ),
				'data-conditional-value' => 'mail',
			)
		) );
		
		$cmb->add_group_field( $group_field_id, array(
			'id'   => $tab . '_' . rand(100,999),
			'type' => 'clearfix',
		) );
		
		
		
		
		
		               
		$cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Parameter', 'remp' ),
			'id'   => 'param',
			'type' => 'key_val',

			'repeatable' => true,
		) );		
		
		
		
		// $cmb->add_field( array(
		// 	'name' => __( 'Scout Id', 'remp' ),
		// 	// 'desc' => __( 'field description (optional)', 'remp' ),
		// 	'id'   => 'scoutid',
		// 	'type' => 'text',
		// 	'attributes' => array(
		// 		// 'required'    => true,
		// 	)
		// ) );

		// $cmb->add_field( array(
		// 	'name' => __( 'From', 'remp' ),
		// 	'id'   => 'from',
		// 	'type' => 'text_datetime_timestamp',
		// 	'attributes' => array(
		// 		'required'    => true,
		// 	)
		// ) );
		
		// $cmb->add_field( array(
		// 	'name' => __( 'To', 'remp' ),
		// 	'id'   => 'to',
		// 	'type' => 'text_datetime_timestamp',
		// 	'attributes' => array(
		// 		'required'    => true,
		// 	)
		// ) );
		

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
		
		// if (! empty($_POST)){
		// 	die(print_r($_POST));
		// }
		
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
				
				// if (! empty($_POST['request'])){
					// die(print_r($_POST['request']));
				// }
				
				// foreach ( $_POST['request'] as $request_k => $request_v ){
				foreach ( $_POST['request'] as $request ){
				
					// $request['id']
					// $request['state']
					// $request['source']

					// $request['output_method']
					// $request['value_map']
					// $request['mail']
					
					// $request['param']
				
					// skip request if disabled
					if ( ! isset( $request['state'] ) || empty( $request['state'] ) || $request['state'] === 'disabled' ) continue;
					

					// skip request if no output

					if ( ! isset( $request['output_method'] ) || empty( $request['output_method'] ) || $request['output_method'] === null ) continue;
					
					
					// params to key val
					$params = array();
					foreach( $request['param'] as $param ){
						if ( isset( $param['key'] ) && ! empty( $param['key'] ) ){
							$params[ $param['key'] ] = $param['val'];
						}
					}
					
					// get source
					$sources = cmb2_get_option( $this->key, 'sources', null );
					foreach( $sources as $source ){
						if ( $source['id'] == $request['source']) 
							break;
					}
					
					
					// get value_map
					$value_maps = cmb2_get_option( $this->key, 'value_map', false );
					if ( $value_maps ){
						foreach( $value_maps as $value_map ){
							if ( $value_map['id'] == $request['value_map']) 
								break;
						}
					} else {
						$value_map = false;
					}
					
					
					
					// run
					$args = array(
						'id'			=>	remp_slugify( $request['id'] ),
						'state'			=>	$request['state'],
						'source'		=>	$source,
						'output_method'	=>	$request['output_method'],
						'value_map'		=>	$value_map,

						// 'mail'		=>	$request['output_method'],

						'params'		=>	$params,
					);
					$Get = 'Remp_Request' . ( $source['authorization'] == 'none' ? '' : '_' . $source['authorization'] );
					new $Get( $args );
					
				}

				// query paramters
				// $params = array();
				// $params['from'] = date("Y-m-d\TH:i:s", $sanitized_values['from']);
				// $params['to'] = date("Y-m-d\TH:i:s", $sanitized_values['to']);
				// $params['test'] = array_key_exists( 'test', $sanitized_values) && $sanitized_values['test'] === 'on' ? 'true' : 'false';
				// new Remp_Request( $params );
				
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
		foreach ($editable_roles as $role => $details) {
			if ( $role == 'administrator' ) continue;
			$roles[esc_attr($role)] = translate_user_role($details['name']);
		}
		return $roles;
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

	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}

// Get it started
remp_admin();




?>