<?php
/*
	grunt.concat_in_order.declare('Islcrm_defaults');
	grunt.concat_in_order.require('init');
*/


class Islcrm_defaults {


	protected $defaults = array();

	public function add_default( $arr ){
		$defaults = $this->defaults;
		$this->defaults = array_merge( $defaults , $arr);
	}
	
	public function get_default( $key ){
		if ( array_key_exists($key, $this->defaults) ){
			return $this->defaults[$key];

		}
			return null;
	}


}



function islcrm_init_defaults(){
	global $islcrm_defaults;
	
	$islcrm_defaults = new Islcrm_defaults();
	
	// $defaults = array(
	// 	// silence ...
	// );
	
	// $islcrm_defaults->add_default( $defaults );	
}
add_action( 'admin_init', 'islcrm_init_defaults', 1 );
add_action( 'init', 'islcrm_init_defaults', 1 );



?>