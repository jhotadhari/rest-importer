<?php
/*
	grunt.concat_in_order.declare('remp_request');
	grunt.concat_in_order.require('init');
*/



// wrapper function to init Remp_Request class
function remp_request( $request ){

	// skip request if no output
	if ( ! isset( $request['output_method'] ) || empty( $request['output_method'] ) || $request['output_method'] === null ) return false;

	// params to key val
	$params = array();
	if ( isset($request['param']) && !empty($request['param']) ){
		foreach( $request['param'] as $param ){
			if ( isset( $param['key'] ) && ! empty( $param['key'] ) ){
				$params[ $param['key'] ] = $param['val'];
			}
		}
	}
	
	// get source
	$sources = remp_get_option( 'sources', null );
	foreach( $sources as $source ){
		if ( $source['id'] == $request['source']) 
			break;
	}
	
	
	// get value_map
	$value_maps = remp_get_option( 'value_map', false );
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
		'params'		=>	$params,
	);
	
	$Get = 'Remp_Request' . ( $source['authorization'] == 'none' ? '' : '_' . $source['authorization'] );
	new $Get( $args );

}
?>