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

			// 'mail' => array(),

			'params' => array(),
		);	
		$args = wp_parse_args( $args, $defaults );
		
		$this->set_request_url( $args['source'], $args['params'] );
		
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

		

		

		
		
		
		
		
		
		
		// send mail
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
		// if ( array_key_exists('message', $response_body_arr ) && ! array_key_exists('lead', $response_body_arr )){
		// 	new Remp_Admin_Notice( remp_array_column_recursive( $response_body_arr, 'messageCode'), true );
		// 	return false;
		// }
		
		return $response_body_arr;
	}
	
}





?>