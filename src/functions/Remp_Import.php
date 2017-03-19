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

	function __construct( $response_body_arr = null, $args ) {
		if ( $response_body_arr === null ) return false;
		
		// parse_args
		$args = wp_parse_args( $args, array(
			'id' => '',
			'state' => '',
			// 'source' => array(),
			'output_method' => array(),
			'value_map' => array(),
			// 'mail' => array(),
			// 'params' => array(),
		) );
		
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
			
			if ( !empty( $branch['data']['table'] ) && !empty( $branch['data']['key'] ) && !empty( $branch['text'] ) ){
				if ( $branch['data']['table'] == 'object' ){
					// if node value exists in response, save as post data
					// if ( array_key_exists( $branch['text'], $obj) ) {
					if ( !empty( $obj[$branch['text']] ) ) {
						$this->obj_data[$branch['data']['key']] = $obj[$branch['text']];
					}
				} elseif ( $branch['data']['table'] == 'object-meta' ){
					// if node value exists in response, save as post meta
					// if ( array_key_exists( $branch['text'], $obj) ) {
					if ( !empty( $obj[$branch['text']] ) ) {
						$this->obj_meta[$branch['data']['key']] = $obj[$branch['text']];
					}
				}
			}
			
			// climb down
			if ( !empty( $branch['text'] ) && array_key_exists( 'children', $branch ) && count($branch['children']) > 0 ){
				$this->climb_map_tree( $branch['children'], $obj[$branch['text']] );

			}
			
		}
	}
	
	
	protected function set_object_defaults( $args ){
		//	??? change filter name ! with variables
		$this->object_defaults = apply_filters('remp_import_obj_defaults', ( $this->object_defaults ? $this->object_defaults : array() ) );
	}
	
	
	protected function set_object_meta_defaults( $args ){
		//	??? change filter name ! with variables
		$this->object_meta_defaults = apply_filters('remp_import_obj_meta_defaults', ( $this->object_meta_defaults ? $this->object_meta_defaults : array() ) );
	}		
	
	protected function something_with_args( $args ){
		//	hey inherited class, overwride me if you need some args
	}		
	
	
		
	protected function insert_object( $obj ){
		// silence ... 
	}
	
}



?>