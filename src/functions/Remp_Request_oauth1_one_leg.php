<?php
/*
	grunt.concat_in_order.declare('Remp_Request_oauth1_one_leg');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('Remp_Request');
*/

Class Remp_Request_oauth1_one_leg extends Remp_Request {
	
	function __construct( $args ) {
		parent::__construct( $args );
	}
	
	protected function set_request_url( $source, $params ) {
		$error = new WP_Error();
		
		$consumer_key = cmb2_crypt_decrypt( $source['consumer_key'] );
		$consumer_secret = cmb2_crypt_decrypt( $source['consumer_secret'] );
		
		// check for errors
		if (! isset( $consumer_key ) ) $error->add( 'remp', 'No consumer key is set!' );
		if (! isset( $consumer_secret ) ) $error->add( 'remp', 'No consumer secret is set!' );
		if ( count( $error->get_error_messages() ) > 0 ){
			new Remp_Admin_Notice( $error->get_error_messages(), true , true );
			return false;
		}
		
		$consumer = new Eher\OAuth\Consumer(  $consumer_key, $consumer_secret );
		
		$request = Eher\OAuth\Request::from_consumer_and_token(
			$consumer,
			null,
			'GET',
			$source['resource_url'],
			$params
		);
		$request->sign_request( new Eher\OAuth\HmacSha1(), $consumer, null);
	
		$this->request_url = $request->to_url();
	}
	
}

?>