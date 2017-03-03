<?php
/*
	grunt.concat_in_order.declare('zzz_debug');
	grunt.concat_in_order.require('init');
*/

function zzz_debug(){


// 
$user_exists = array();
	
// $user_exists['user_email'] = false;	



// if( $a = email_exists( 'email@domain.com' ) ) {

// }



// var_dump( $a );

// $existing_user_data = get_userdata( 18 );
$user = new WP_User( 18 );

print('<pre>');
print_r($user->data);


var_dump( $user->get('user_nicename') );
var_dump( $user->get('user_login') );
var_dump( $user->get('not_set') );


					$result = wp_insert_user( array(
						'ID' => $user->ID,
						'user_login' => $user->get('user_login'),
						// 'user_pass' => $user->get('user_pass'),
						'user_nicename' => 'is_set new',
						'not_set' => 'is_set',
					));
	
					var_dump( $user->get('user_nicename') );
					var_dump( $user->get('not_set') );
					var_dump( $result );
print('</pre>');


	
}
// add_action('admin_notices','zzz_debug')




?>