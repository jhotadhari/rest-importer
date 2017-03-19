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