<?php
namespace core;

class DatabaseConnection {
    
    private static $instance, $connections, $cache_helper;
    
    private static function getInstance() {
		if (is_null(self::$instance)) {
            self::$instance = new self();
        }	
		return self::$instance;
	}
    
    private function __construct() {
        self::$connections['default']['host'] = MYSQL_HOST;
        self::$connections['default']['username'] = MYSQL_USERNAME;
        self::$connections['default']['password'] = MYSQL_PASSWORD;
        self::$connections['default']['database'] = MYSQL_DATABASE;
        self::$connections['default']['charset'] = MYSQL_CHARSET;
        self::$connections['default']['connection'] = null;
        self::$cache_helper = Helper::cache(['dir' => 'mysql']);
    }
    
    protected static function connect() : \mysqli {
        self::getInstance();
        if (is_null(self::$connections['default']['connection'])) {
			self::$connections['default']['connection'] = @new \mysqli(
                self::$connections['default']['host'],
                self::$connections['default']['username'],
                self::$connections['default']['password'],
                self::$connections['default']['database']
            );
			if(self::$connections['default']['connection'] -> connect_errno) {
				die(self::$connections['default']['connection'] -> connect_error);
			} else {
                self::$connections['default']['connection'] -> query('Set Names "'.self::$connections['default']['charset'].'"');
                return self::$connections['default']['connection'];
            }
		} else {
            return self::$connections['default']['connection'];
        }
    }
    
    protected static function cacheHelper() : \helper\Cache {
        self::getInstance();
        return self::$cache_helper;
    }
    
}