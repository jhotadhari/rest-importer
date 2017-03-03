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