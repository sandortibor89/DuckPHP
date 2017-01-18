<?php
namespace helper;

class Database {
    
    private static $instance, $connection, $cache_helper, $mcache, $select, $sql;
    public static $host, $username, $password, $database, $cache, $charset, $table;
    
    public static function getInstance($params = null) {
		if (is_null(self::$instance)) {
            self::$instance = new self();
        }	
		return self::$instance;
	}
    
    private function __construct() {
        self::$host = self::$host ?? MYSQL_HOST;
        self::$username = self::$username ?? MYSQL_USERNAME;
        self::$password = self::$password ?? MYSQL_PASSWORD;
        self::$database = self::$database ?? MYSQL_DATABASE;
        self::$charset = self::$charset ?? MYSQL_CHARSET;
        self::$cache = self::$cache ?? MYSQL_CACHE;
        self::$cache_helper = Helper::cache(['dir' => 'sql', 'time' => '5']);
    }
    
    private static function connect() {
		if (is_null(self::$connection)) {
			self::$connection = @new \mysqli(self::$host, self::$username, self::$password, self::$database);
			if(self::$connection -> connect_errno) {
				die(self::$connection -> connect_error);
			}
			self::execute('Set Names "'.self::$charset.'"');
		}
	}
    
    private static function execute(string $sql) {
		self::connect();
		$microtime = microtime(true);
		$return = self::$connection -> query($sql);
		$microtime = microtime(true) - $microtime;
        echo "Time: ".$microtime."\n";
		return (self::$connection -> error) ? false : $return;
	}
    
    public static function cache(int $time = null) : self {
        self::getInstance();
        self::$sql['cache_time'] = $time;
        self::$sql['cache'] = true;
        return self::$instance;
    }
    
    public static function nocache() : self {
        self::getInstance();
        self::$sql['cache_time'] = null;
        self::$sql['cache'] = false;
        return self::$instance;
    }
    
    public static function select($sql) : self {
        self::getInstance();
        self::$select = $sql;
        return self::$instance;
    }
    
    public static function table($table) : self {
        self::getInstance();
        self::$table = $table;
        return self::$instance;
    }
    
    public static function where($a, $b, $c = null) : self {
        self::getInstance();
        self::$table = $table;
        return self::$instance;
    }
    
    public static function sql(bool $all = false) : string {
        if (is_null(self::$select)) {
            $sql = 'Select * From `'.self::$table.'`';
        } else {
            $sql = self::$select;
        }
        if (!$all) {
            $sql = (preg_match_all("/.*\Klimit\s+\d+\s*\z/i", $sql)) ? $sql : $sql." Limit 1";
            $sql = preg_replace("/.*\K(limit)\s+(\d+)/is", "$1 1", $sql);
        }
        return $sql;
    }
    
    public static function sqlAll() : string {
        return self::sql(true);
    }
    
    public static function get(bool $all = false) {
        $sql = self::sql($all);
        $cache = self::$sql['cache'] ?? self::$cache;
        if ($cache) {
            $cache_key = md5($sql);
        }
        if (!is_null(self::$mcache) && array_key_exists($cache_key, self::$mcache) && $cache) {
            $result_array = self::$mcache[$cache_key];
        } else {
            if ($cache) {
                $cache_data = self::$cache_helper -> get($cache_key);
            } else {
                $cache_data = null;
            }
            if (!is_null($cache_data) && $cache) {
                $result_array = $cache_data;
                self::$mcache[$cache_key] = $result_array;
            } else {
                $result = self::execute($sql);
                while($row = $result -> fetch_array(MYSQLI_ASSOC)) {
                    $result_array[] = $row;
                }
                if ($result !== false && $cache) {
                    self::$mcache[$cache_key] = $result_array;
                    self::$cache_helper -> set($cache_key, $result_array, (self::$sql['cache_time'] ?? null));
                }
                self::reset();
            }
        }
        return ($all) ? $result_array : reset($result_array);
    }
    
    public static function getAll() {
        return self::get(true);
    }
    
    private static function reset() {
        self::$sql = [];
    }
    
}