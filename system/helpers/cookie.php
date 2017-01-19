<?php
namespace helper;

class Cookie {
    
    private $crypt_helper, $default_expire;
    
    public function __construct(array $a = []) {
        $this -> crypt_helper = Helper::crypt();
        $this -> default_expire = $a['time'] ?? DEFAULT_COOKIE_TIME;
    }
    
    public function set(string $name, string $value, int $expire = null, string $path = null, string $domain = null, bool $secure = false, bool $httponly = true) : bool {
        if (is_null($expire)) { $expire = time() + $this -> default_expire; }
		if (is_null($path)) { $path = '/'.WORKING_DIR; }
        $value = $this -> crypt_helper -> encrypt($value);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    public function get(string $name) : string {
        $value = $_COOKIE[$name] ?? null;
		if(is_null($value)) {
            return false;
        } else {
            return $this -> crypt_helper -> decrypt($value);
        }
    }
    
    public function delete(string $name, string $path = null, string $domain = null, string $secure = null) {
        if (is_null($path)) { $path = '/'.WORKING_DIR; }
        return setcookie($name, '', time() - 3600, $path, $domain, $secure);
    }
    
}