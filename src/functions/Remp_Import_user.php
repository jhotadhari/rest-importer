<?php
/*
	grunt.concat_in_order.declare('Remp_Import_user');
	grunt.concat_in_order.require('init');
	grunt.concat_in_order.require('Remp_Import');
*/


Class Remp_Import_user extends Remp_Import {
	
	protected $opt_user_exists;
	
	function __construct( $response_body_arr = null, $args ) {
		parent::__construct( $response_body_arr, $args );
	}
	
	
	protected function set_object_defaults( $args ){
		$this->object_defaults = array(
			'user_email'	=> '',		
			'role'			=> $args['value_map']['role'],
			'user_pass'		=> '',
		);
		// filter
		parent::set_object_defaults( $args );
	}
	
	
	protected function insert_object( $obj ){
		
		// skip keys if not valid
		$valids = remp_get_valid_option_keys( 'user' );
		$obj_data = $this->obj_data;
		foreach ( $obj_data as $key => $val ){
			if ( ! in_array( $key, $valids ) ){
				unset( $obj_data[$key] );
			}
		}

		$this->obj_data = apply_filters( "remp_insert_{$this->request_id}_obj_data", wp_parse_args( $obj_data, $this->object_defaults ), $this->request_id, $obj );
		$this->obj_meta = apply_filters( "remp_insert_{$this->request_id}_obj_meta", wp_parse_args( $this->obj_meta, $this->object_meta_defaults ), $this->request_id, $obj );
		
		// is email set 
		if ( empty( $this->obj_data['user_email'] ) ){
			new Remp_Admin_Notice( 'user_email not set.', true );
			return false;
		}

		// is email valid
		if ( gettype( $this->obj_data['user_email'] ) !== 'string' || ! is_email( $this->obj_data['user_email'] ) ){
			new Remp_Admin_Notice( 'user_email not vaild: ' . $this->obj_data['user_email'] , true );
			return false;
		}

		// is user_login set? if not, set from email
		$this->obj_data['user_login'] = empty( $this->obj_data['user_login'] ) || gettype( $this->obj_data['user_login'] ) !== 'string'
			? $this->obj_data['user_email']
			: $this->obj_data['user_login'];
			
		// is pass a string value? if not set random
		$this->obj_data['user_pass'] = empty( $this->obj_data['user_pass'] ) || gettype( $this->obj_data['user_pass'] ) !== 'string'
			? wp_hash_password( wp_generate_password( 12, true ) )
			: wp_hash_password( $this->obj_data['user_pass'] );
	
		
		// Does the user exist?
		$user_exists = array();
		$user_exists_msg = array('User already exists:');
		// user_email
		if( $id = email_exists( $this->obj_data['user_email'] ) ) {
			$user_exists['user_email'] = $id;
			$user_exists_msg[] = 'id: ' . $id . ' user_email: ' . $this->obj_data['user_email'];
		}
		// user_login
		// is user_login not set, set from email/random
		// is user_login set, but user_login already exists, remember
		if ( gettype( $this->obj_data['user_login'] ) !== 'string' || empty( $this->obj_data['user_login'] ) ){
			$this->set_user_login();
		} elseif ( $id = username_exists( $this->obj_data['user_login'] ) ) {
			$user_exists['user_login'] = $id;
			$user_exists_msg[] = 'id: ' . $id . ' user_login: ' . $this->obj_data['user_login'];
		}	
		
		
		// create, merge or skip
		if ( count( $user_exists ) == 0 ) {
		// user doesn't exist
			$user_id = $this->insert_user( $obj );
			if ( is_wp_error( $user_id ) ) {
				// ??? error handler
				return false;
			} else {


				// ??? hook here
				new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

				return true;
			}
		} else {
		// user exists
			if ( $this->opt_user_exists == 'skip' ) {
				$skip = true;
				$user_exists_msg[] = '... entry skipped';
			} else {
				if ( count( $user_exists ) == 1 ) {
					if ( ! empty($user_exists['user_email']) ) {
					// user_email exists	user_login doesn't exist
						new Remp_Admin_Notice( 'user_email exists	user_login doesnt exist' );		// ??? debug only

						$user_id = $this->merge_existing_user( get_user_by( 'ID',  $user_exists['user_email'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					} elseif ( ! empty($user_exists['user_login']) ) {
					// user_login exists	user_email doesn't exist
						new Remp_Admin_Notice( 'user_login exists	user_email doesnt exist' );		// ??? debug only

						$user_id = $this->merge_existing_user(  get_user_by( 'ID',  $user_exists['user_login'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					}
				} elseif ( count( $user_exists ) == 2 ) {
					if ( $user_exists['user_email'] == $user_exists['user_login'] ) {
					// user_login user_email belong to same existing user
						new Remp_Admin_Notice( 'user_login user_email belong to same existing user' );		// ??? debug only

						$user_id = $this->merge_existing_user(  get_user_by( 'ID',  $user_exists['user_login'] ), $obj );
						if ( is_wp_error( $user_id ) ) {
							// ??? error handler
							new Remp_Admin_Notice( $user_id->get_error_messages(), true );		// ??? debug only

							return false;
						} else {


							// ??? hook here
							new Remp_Admin_Notice( 'user created/updated. id: ' . $user_id );		// ??? debug only

							return true;
						}
					} else {
					// user_login user_email belong to different existing users
						$skip = true;
						$user_exists_msg[] = '... entry skipped';
					}
				}
			}
		}
		
		
		if ( $skip ){
			new Remp_Admin_Notice( $user_exists_msg , true );
			return false;
		}
		
		// ??? debug
		new Remp_Admin_Notice( 'something went wrong' , true );	// this msg should never appear
		
	}
	
	
	protected function set_user_login( $set_as_email = true ){
		if ( $set_as_email ){
			if ( username_exists( $this->obj_data['user_email'] ) || strlen( $this->obj_data['user_email'] ) > 60 ) {
				$this->set_user_login( false );
				return;
			}
			$this->obj_data['user_login'] = $this->obj_data['user_email'];
		} else {
			$user_login = rand(100000,999999);
			if ( username_exists( $user_login ) ) {
				$this->set_user_login( false );
				return;
			}
			$this->obj_data['user_login'] = $user_login;
		}
	}
	
	
	protected function insert_user( $obj ){
	
		// create/update user
		$user_id = wp_insert_user( $this->obj_data ) ;
	
		// update meta
		if ( ! is_wp_error( $user_id ) ) {
			foreach ( $this->obj_meta as $key => $value ) {
				update_user_meta( $user_id, $key, $value);
			}
		}
		
		// hook
		$this->insert_finished( $obj, $user_id );
		// return id or error
		return $user_id;
		
	}
	
	
	protected function merge_existing_user( $user, $obj ){

		if ( $this->opt_user_exists == 'merge_overwride' ) {
			$this->obj_data['ID'] = $user->ID;
			$user_id = $this->insert_user( $obj );
		}
		
		
		if ( $this->opt_user_exists == 'merge_carefully' ) {

			$user_id = $user->ID;
			$user_login = $user->get('user_login');
			
			foreach ( $this->obj_data as $new_k => $new_v ){
			
				if ( empty( $user->get($new_k) ) ) {
					$result = wp_insert_user( array(
						'ID' => $user_id,
						'user_login' => $user_login,
						$new_k => $new_v,
					));
					if ( is_wp_error( $result ) ) {
						// ??? error handler
						// return false;	// no proceed
					}
					
				}
			
			}
			
			foreach ( $this->obj_meta as $new_k => $new_v ){
				$existing_meta = get_user_meta( $user_id, $new_k, true);
			
				if ( empty( $existing_meta ) ) {
					$result = update_user_meta( $user_id, $new_k, $new_v );
					if ( ! $result ) {
						// ??? error handler
						// return false;	// no proceed
					}
					
				}
			
			}
		}
	
		// hook
		$this->insert_finished( $obj, $user_id );
		return $user_id;
		
	}
	
	protected function insert_finished( $obj, $user_id ){
		// do something special
		do_action( "remp_insert_{$this->request_id}_finished", $this->request_id, $obj, $user_id, $this->obj_data, $this->obj_meta );
	}
		
	
	protected function something_with_args( $args ){
		$this->opt_user_exists = $args['value_map']['user_exists'];
	}		
	
}


?>