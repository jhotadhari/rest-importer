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