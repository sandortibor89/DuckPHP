<?php
namespace helper;

class Crypt {
    
    private $instance, $secret_key;
    
	public function __construct() {
        $this -> secret_key = SECRET_KEY;
		if(!function_exists('mcrypt_encrypt')) {
			die('Mcrypt PHP extension required!');
		}
    }
    
	public function encrypt(string $str) : string {
        self::getInstance();
        $key = self::$secret_key;
        $encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $str, MCRYPT_MODE_CBC, md5(md5($key))));
        return $encoded;
    }
    
	public function decrypt(string $str) : string {
        self::getInstance();
        $key = self::$secret_key;
        $decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
        return $decoded;
    }
    
}