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