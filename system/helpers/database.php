<?php
namespace helper;

class Database extends DatabaseConnection {
    
    private $core, $connection, $cache_helper, $mcache, $default, $query_data;
    
    public function __construct(array $params = []) {
        $this -> default['cache'] = $params['cache'] ?? MYSQL_CACHE;
        $this -> default['cache_time'] = $params['cache_time'] ?? MYSQL_CACHE_TIME ?? DEFAULT_CACHE_TIME;
        $this -> cache_helper = self::cacheHelper();
        $this -> connection = self::connect();
        $this -> core = new \core\Database($this -> connection);
        $this -> defaulttable($params['table'] ?? '');
    }
    
    private function execute(string $sql) {
		$return = $this -> connection -> query($sql);
		return ($this -> connection -> error) ? die("Mysql query error: ".$this -> connection -> error) : $return;
	}
    
    public function cache(int $time = null) : self {
        $this -> query_data['cache'] = true;
        $this -> query_data['cache_time'] = $time;
        return $this;
    }
    
    public function nocache() : self {
        $this -> query_data['cache'] = false;
        return $this;
    }
    
    public function select(...$args) : self {
        $this -> core -> select = $args;
        return $this;
    }
    
    public function insertGetId(array $simple, array $multiple = null) {
        return $this -> insert($simple,$multiple,true);
    }
    
    public function insert(array $simple, array $multiple = null, bool $getid = false) {
        $this -> core -> insert = [
            'simple' => $simple,
            'multiple' => $multiple,
            'separate' => $getid
        ];
        $table = $this -> core -> table;
        $insert = $this -> core -> insert;
        if (is_array($insert)) {
            $return = [];
            foreach ($insert as $v) {
                $this -> execute("Insert Into $table $v");
                $return[] = $this -> connection -> insert_id;
            }
        } else {
            $this -> execute("Insert Into $table $insert");
            $return = $this -> connection -> affected_rows;
        }
        return $return;
    }
    
    public function update(array $update) : int {
        $this -> core -> update = $update;
        $table = $this -> core -> table;
        $where = $this -> core -> where;
        $update = $this -> core -> update;
        $this -> execute("Update $table Set $update $where");
        $return = $this -> connection -> affected_rows;
        return $return;
    }
    
    public function distinct() : self {
        $this -> query_data['distinct'] = true;
        return $this;
    }
    
    public function defaulttable(string $table) : self {
        $this -> core -> defaulttable = $table;
        return $this;
    }
    
    public function table(string $table) : self {
        $this -> core -> table = $table;
        return $this;
    }
    
    public function join(string $table, array $on) : self {
        $this -> core -> join = [$table, $on];
        return $this;
    }
    
    public function where(...$arguments) : self {
        $this -> core -> where = $arguments;
        return $this;
    }
    
    public function group(string $by) : self {
        $this -> core -> group = $by;
        return $this;
    }
    
    public function groupBy(string $by) : self {
        return $this -> group($by);
    }
    
    public function order(...$arguments) : self {
        $this -> core -> order = $arguments;
        return $this;
    }
    
    public function orderBy(...$arguments) : self {
        call_user_func_array(array($this, 'order'), $arguments);
        return $this;
    }
    
    public function limit(int $limit = null, int $offset = null) : self {
        $this -> core -> limit = [$limit,$offset];
        return $this;
    }
    
    public function sql(bool $all = false) : string {
        $select = $this -> core -> select;
        if (is_array($select)) {
            $table = $this -> core -> table;
            $sql[] = 'Select';
            $sql[] = ($this -> query_data['distinct']) ? 'DISTINCT' : null;
            $sql[] = implode(',', $select);
            $sql[] = 'From';
            $sql[] = $table;
            $sql[] = implode(' ', $this -> core -> join);
            $sql[] = $this -> core -> where;
            $sql[] = $this -> core -> group;
            $sql[] = $this -> core -> order;
            $sql[] = $this -> core -> limit;
            $sql = implode(' ', array_filter($sql));
        } else {
            $sql = $select;
        }
        if (!$all) {
            $sql = (preg_match_all("/.*\Klimit\s+\d+\s*\z/i", $sql)) ? $sql : $sql." Limit 1";
            $sql = preg_replace("/.*\K(limit)\s+(\d+)/is", "$1 1", $sql);
        }
        $this -> query_data = ['cache' => $this -> query_data['cache'],'cache_time' => $this -> query_data['cache_time']];
        return $sql;
    }
    
    public function sqlAll() : string {
        return $this -> sql(true);
    }
    
    public function get(bool $all = false) {
        $sql = $this -> sql($all);
        $cache = $this -> query_data['cache'] ?? $this -> default['cache'];
        if ($cache) {
            $cache_key = md5($sql);
            $cache_time = $this -> query_data['cache_time'] ?? $this -> default['cache_time'];
        }
        if (!is_null($this -> mcache) && array_key_exists($cache_key, $this -> mcache) && $cache) {
            $result_array = $this -> mcache[$cache_key];
        } else {
            $cache_data = ($cache) ? $this -> cache_helper -> get($cache_key) : null;
            if (!is_null($cache_data) && $cache) {
                $result_array = $cache_data;
                $this -> mcache[$cache_key] = $result_array;
            } else {
                if ($result = $this -> execute($sql)) {
                    while ($row = $result -> fetch_array(MYSQLI_ASSOC)) {
                        $result_array[] = $row;
                    }
                    if ($cache) {
                        $this -> mcache[$cache_key] = $result_array;
                        $this -> cache_helper -> set($cache_key, $result_array, $cache_time);
                    }
                }
                $result_array = $result_array ?? [];
            }
        }
        return ($all) ? $result_array : (($result_array) ? ((count($r = reset($result_array)) === 1) ? reset($r) : $r) : $result_array);
    }
    
    public function getAll() {
        return $this -> get(true);
    }
    
}