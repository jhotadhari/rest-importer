<?php
/*
	grunt.concat_in_order.declare('islcrm_options_page');
	grunt.concat_in_order.require('init');
*/
/**
 * CMB2 Plugin Options
 * @version 0.1.0
 */
class Islcrm_admin_options {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'islcrm_options';

	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'islcrm_option_metabox';

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Holds an instance of the object
	 *
	 * @var Islcrm_admin_options
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return Islcrm_admin_options
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
		$this->title = __( 'IS24 Lead import for WP-CRM', 'islcrm' );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		
		add_action( 'cmb2_after_options-page_form_' . $this->metabox_id, array( $this, 'enqueue_style'), 10, 2 );
		
	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page( 'options-general.php', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields
		/*
		$cmb->add_field( array(
			'name' => __( 'Test Text', 'islcrm' ),
			'desc' => __( 'field description (optional)', 'islcrm' ),
			'id'   => 'test_text',
			'type' => 'text',
			'default' => 'Default Text',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Test Color Picker', 'islcrm' ),
			'desc'    => __( 'field description (optional)', 'islcrm' ),
			'id'      => 'test_colorpicker',
			'type'    => 'colorpicker',
			'default' => '#bada55',
		) );
		*/

	}
	
	
	public function enqueue_style( $post_id, $cmb ) {
		wp_enqueue_style( 'islcrm_options_page', plugin_dir_url( __FILE__ ) . 'css/islcrm_options_page.css', false );
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

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'islcrm' ), 'updated' );
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
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get/return the Islcrm_admin_options object
 * @since  0.1.0
 * @return Islcrm_admin_options object
 */
function islcrm_admin() {
	return Islcrm_admin_options::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function islcrm_get_option( $key = '', $default = null ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( islcrm_admin()->key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( islcrm_admin()->key, $key, $default );

	$val = $default;

	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}

// Get it started
islcrm_admin();




?>