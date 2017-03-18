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