<?php
/**
 * A crypt field type for CMB2
 *
 * A text field, displayed as a password.
 * The value will be stored encrypted.
 * Use the cmb2_crypt_decrypt function to decrypt the value.
 *
 * @package    CMB2
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License v2
 * @author     jhotadhari <tellme@waterproof-webdesign.info>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

 
/**
 * Render callback for fieldtype crypt
 *
 * Hook documented in cmb2/includes/types/CMB2_Types.php
 *
 * @param array  $field              The passed in `CMB2_Field` object
 * @param mixed  $escaped_value      The value of this field escaped.
 *                                   It defaults to `sanitize_text_field`.
 *                                   If you need the unescaped value, you can access it
 *                                   via `$field->value()`
 * @param int    $object_id          The ID of the current object
 * @param string $object_type        The type of object you are working with.
 *                                   Most commonly, `post` (this applies to all post-types),
 *                                   but could also be `comment`, `user` or `options-page`.
 * @param object $field_type_object  This `CMB2_Types` object
 */
function cmb2_crypt_render_callback( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	
	// decrypt value
    $field_type_object->field->value = cmb2_crypt_decrypt( $field_type_object->field->value );
    $field_type_object->field->escaped_value = cmb2_crypt_decrypt( $field_type_object->field->escaped_value );
    
    // render field
	echo $field_type_object->input( array( 
		'type' => 'password'
		) );
    
}
add_action( 'cmb2_render_crypt', 'cmb2_crypt_render_callback', 10, 5 );


/**
 * Sanitize callback for fieldtype crypt.
 *    
 * Filters the value before it is saved.
 *    
 * Filter documented in cmb2/includes/types/CMB2_Field.php
 *
 * @param bool|mixed $override_value Sanitization/Validation override value to return.
 *                                   Default false to skip it.
 * @param mixed      $value      The value to be saved to this field.
 * @param int        $object_id  The ID of the object where the value will be saved
 * @param array      $field_args The current field's arguments
 * @param object     $sanitizer  This `CMB2_Sanitize` object
 */
function cmb2_crypt_sanitize_callback( $override_value, $value ) {
	// encrypt $value
    return cmb2_crypt_encrypt( $value );
}
add_filter( 'cmb2_sanitize_crypt', 'cmb2_crypt_sanitize_callback', 10, 2 );


/**
 * Encrypts the input
 *
 * based on this article by Josh Hartman:
 * https://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
 *
 * @param	mixed	$input	Uncrypted input
 * @param	string	$key	Optional. if unset, AUTH_KEY constant will be used to make a key
 * @return	string			Encrypted input
 */
function cmb2_crypt_encrypt( $input, $key = null){
	// if key is unset, use AUTH_KEY constant
	$key = $key === null ? AUTH_KEY : $key;
	// if key is not valid, convert it to a valid one
	$key = cmb2_crypt_key_validate( $key ) ? $key : cmb2_crypt_key_convert_valid( $key );
	
    $input = serialize($input);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $key);
    $mac = hash_hmac('sha256', $input, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $input.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
    return $encoded;
}


/**
 * Decrypts the input
 *
 * based on this article by Josh Hartman:
 * https://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
 *
 * @param	mixed	$input_encrypted	Encrypted input
 * @param	string	$key				Optional. if unset, AUTH_KEY constant will be used to make a key
 * @return	string						Decrypted input
 */
function cmb2_crypt_decrypt( $input_encrypted, $key = null){
	// if key is unset, use AUTH_KEY constant
	$key = $key === null ? AUTH_KEY : $key;
	// if key is not valid, convert it to a valid one
	$key = cmb2_crypt_key_validate( $key ) ? $key : cmb2_crypt_key_convert_valid( $key );
	
    $input_encrypted = explode('|', $input_encrypted.'|');
    $decoded = base64_decode($input_encrypted[0]);
    $iv = base64_decode($input_encrypted[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', $key);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcmac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}


/**
 * Check if string consits of 64 hexadecimal characters
 *
 * @param	string	$string		string to validate
 * @return	bool
 */
function cmb2_crypt_key_validate( $string ){
	if (! gettype( $string ) === 'sting') return false;
	return ctype_xdigit( $string ) && strlen( $string ) === 64;
}


/**
 * Converts a string to hexadecimal and cuts it to 64 characters
 *
 * @param	string	$string		string to convert
 * @return	string				A 64 hexadecimal characters string
 */
function cmb2_crypt_key_convert_valid( $string ){
	if (! gettype( $string ) === 'sting') return false;
	$string_unpacked = unpack('H*', $string );
	return substr( array_shift( $string_unpacked ), 0, 64 );
}

?>