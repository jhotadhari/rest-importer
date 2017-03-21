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