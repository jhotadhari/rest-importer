<?php
/*
	grunt.concat_in_order.declare('remp_get_valid_option_keys');
	grunt.concat_in_order.require('init');
*/


function remp_get_valid_option_keys( $object_type = null, $markup = false ){
	
	if ( $markup ) {
		$return = '';
	} else {

		$return = array();
	}
	
	
	switch ($object_type){
		case 'post':
			
			// filter ???
			$valids = array(
				'post_author',
				'post_content',
				'post_title',
				'post_excerpt',
				'post_status',
				// 'comment_status',
				// 'ping_status',
				'post_password',
				'post_name',
				// 'to_ping',
				// 'pinged',
				// 'post_content_filtered',
				// 'post_parent',
				'post_type',
				// 'post_mime_type',
			);
			
			
			break;

		case 'user':
			
			// filter ???
			$valids = array(
				// 'ID',	// no not valid
				'user_email',
				'user_pass',

				'user_login',
				'user_nicename',
				'user_url',

				'display_name',
				'nickname',
				'first_name',
				'last_name',
				'description',
				'role',
				);
			
			break;

		default:
			return $return;

	}
	
	
			
	if ( $markup && $valids ) {
		
$return .= '<ul>';
		foreach( $valids as $valid ){


			
$return .= '<li>' . $valid . '</li>';
		}

		
$return .= '</ul>';
	} else {

		$return = $valids;
	}
	
	
	return $return;
	
	
}



?>