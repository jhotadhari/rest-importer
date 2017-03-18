<?php
/*
	grunt.concat_in_order.declare('Remp_Request');
	grunt.concat_in_order.require('init');
*/


Class Remp_Request {
	
	protected $request_url;

	function __construct( $args ) {
		$defaults = array(
			'id' => '',
			'state' => '',
			'source' => array(),
			'output_method' => array(),
			'value_map' => array(),
			'params' => array(),
		);	
		$args = wp_parse_args( $args, $defaults );
		
		
		// get remp_log and last_requests
		$remp_log = get_option( 'remp_log', array() );
		$last_requests = array_key_exists( $args['id'], $remp_log ) ? $remp_log[$args['id']] : false;
		$last_request_time = !empty( $last_requests ) ? key( array_slice( $last_requests, -1, 1, true ) ) : false;
		
		// filter the params. useful for cron jobs
		$args['params'] = apply_filters( "remp_request_{$args['id']}_params", $args['params'], $args, $last_requests, $last_request_time );
		
		// set request_url
		$this->set_request_url( $args['source'], $args['params'] );
		
		//  get remote data
		$response_body_arr = $this->request_url ? $this->get_remote( $this->request_url ) : false;
		
		
		
		if ( $args['output_method'] == null ) return false;
		
		// Print as admin notice
		if ( in_array('admin_notice', $args['output_method']) ){
			new Remp_Admin_Notice( $response_body_arr );
		}
		
		// Print to debug log
		if ( in_array('log', $args['output_method']) ){
			ob_start();
			print('<pre>');    
			print_r( $response_body_arr );
			print('</pre>');
			$log = ob_get_contents();
			ob_end_clean();
			error_log( $log );
		}		

		// save response
		if ( in_array('save', $args['output_method']) ){
			$Import = 'Remp_Import' . '_' . $args['value_map']['object_type'];
			new $Import( $response_body_arr, $args );
		}
		
		// save request to log
		$entry = apply_filters( "remp_log_entry_{$args['id']}", array(), $args );	// eg: add the parameter to the log
		$log_max = apply_filters( 'remp_log_max', 5 );
		if ( array_key_exists( $args['id'], $remp_log ) ) {
			// add log
			$remp_log[$args['id']][time()] = $entry;
			// delete old logs
			if ( count( $remp_log[$args['id']] ) > $log_max ){
				$remp_log[$args['id']] = 
				array_slice( $remp_log[$args['id']] , ( $log_max * -1 ), $log_max, true);
			}
		} else {
			// add log
			$remp_log[$args['id']] = array(
				time() => $entry
			);
		}
		update_option( 'remp_log', $remp_log );
		
		
		// request finished, everything done, do something
		do_action( "remp_request_finished_{$args['id']}", $args, $remp_log[$args['id']] );
		
	}
	
	
	
	protected function set_request_url( $source, $params ) {
		$this->request_url = add_query_arg( $params, $source['resource_url'] );
	}
	
	
	
	protected function get_remote( $request_url ) {
		$response = wp_remote_get( $request_url );
		if ( is_wp_error( $response ) ){
			new Remp_Admin_Notice( $response->get_error_messages(), true );
			return false;
		}
		
		$response_body = wp_remote_retrieve_body( $response );
		$response_body_arr = json_decode( $response_body, true );
		
		return $response_body_arr;
	}
	
}




?>