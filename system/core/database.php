<?php
namespace core;

class Database {
    
    private $connection, $table, $select, $insert, $join, $where, $group, $order, $limit;
    
    public function __construct($connection) {
        $this -> connection = $connection;
    }
    
    public function __set($name, $data) {
        call_user_func([$this,'set'.$name], $data);
    }
    
    public function __get($name) {
        return call_user_func([$this,'get'.$name]);
    }
    
    private function arrayType(array $array, string $type) : bool {
        foreach ($array as $v) {
            switch($type) {
                case 'int': if (!is_int($v)) { return false; } break;
                case 'string': if (!is_string($v)) { return false; } break;
                case 'array': if (!is_array($v)) { return false; } break;
            }
        }
        return true;
    }
    
    private function format(string $string = null, bool $as = false) : string {
        if (!is_null($string)) {
            $explode = explode('.', $string, 2);
            if ($as && count($explode) === 2) {
                $string = '`'.end($explode).'` As `'.reset($explode).'`';
            } else {
                $string = '`'.reset($explode).'`'.(count($explode) === 2 ? '.`'.end($explode).'`' : '');
            }
        }
        return $string ?? '';
    }
    // lehetne akár egy függvény is a kettő
    private function formatArrayToString(array $array) : string {
        $cbefore = count($array);
        $walk = array_walk($array, function(&$value) {
            $value = (is_string($value) && strlen($value) > 0) ? "`".trim($value , " \t\n\r\0\x0B'\"")."`" : null;
        });
        $array = array_filter($array);
        $cafter = count($array);
        return ($cbefore === $cafter && $cafter > 0 && $walk) ? implode(",", $array) : '';
    }
    
    private function sqlValueFormatToStr($strorarray) : string {
        $array = is_array($strorarray) ? $strorarray : [$strorarray];
        $cbefore = count($array);
        $walk = array_walk($array, function(&$value) {
            if (is_bool($value)) {
                $value = (int)$value;
            } elseif (is_null($value)) {
                $value = "Null";
            } elseif (!is_array($value) && strlen($value) > 0) {
                $value = "\"".$this -> connection -> real_escape_string(trim($value , " \t\n\r\0\x0B'\""))."\"";
            } else {
                $value = null;
            }
        });
        $array = array_filter($array);
        $cafter = count($array);
        return ($cbefore === $cafter && $cafter > 0 && $walk) ? implode(",", $array) : '';
    }
    
    private function recursiveFormat(array $array, bool $where = false) : string {
        $return = [];
        foreach ($array as $k => $v) {
            if (is_string($v)) {
                if (preg_match("/\A\s*(or|and)\s*\z/i", $v) && 
                    count($return) > 0 && 
                    !preg_match("/\A\s*(or|and)\s*\z/i", end($return))) {
                    $return[] = ucfirst(strtolower($v));
                }
            } elseif ($this -> isArgument($v)) {
                $a = array_shift($v);
                $b = array_shift($v);
                $c = array_shift($v);
                if (is_null($c)) { $c = $b; $b = '='; }
                if (count($return) > 0 && !preg_match("/\A\s*(or|and)\s*\z/i", end($return))) {
                    $return[] = 'And';
                }
                if (preg_match("/\A\s*(in)\s*\z/i", $b)) {
                    $c = '"'.implode('","', $c).'"';
                    $return[] = $this -> format($a).' In('.$c.')';
                } elseif (preg_match("/\A\s*(not)\s*\z/i", $b)) {
                    $c = '"'.implode('","', $c).'"';
                    $return[] = $this -> format($a).' Not In('.$c.')';
                } else {
                    $return[] = $this -> format($a).' '.$b.' '.(($where) ? '"'.$c.'"' : $this -> format($c));
                    
                }
            } else {
                if (count($return) > 0 && !preg_match("/\A\s*(or|and)\s*\z/i", end($return))) {
                    $return[] = 'And';
                }
                $return[] = '('.$this -> recursiveFormat($v, $where).')';
            }
        }
        
        return implode(' ',$return);
    }
    
    private function isArgument(array $array) : bool {
        if (count($array) < 2) { return false; }
        $strtolower = function ($value) { return is_string($value) ? strtolower($value) : $value; };
        if (in_array('or', array_map($strtolower, $array))) { return false; }
        if (in_array('and', array_map($strtolower, $array))) { return false; }
        if (!is_string(reset($array))) { return false; }
        $array = is_array($a = array_shift($array)) ? $a : [$a];
        
        if (count($array) === 1) {
            if (is_array(reset($array))) { return false; }
        } elseif (count($array) === 2) {
            if (!is_string(reset($array))) { return false; }
            if (strlen(reset($array)) > 2) { return false; }
            if (!(strtolower(reset($array)) === 'in' && is_array(end($array)))) { return false; }
            /*
            if (is_array(end($array))) {
                foreach (end($array) as $v) {
                    if (is_array($v)) { return false; }
                }
            }
            */
        } else {
            return false; 
        }
        return true;
    }
    
    private function recursiveOrder(array $array) : string {
        $return = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return[] = $this -> recursiveOrder($v);
            } elseif (is_string($k) && preg_match("/\A\s*(asc|desc)\s*\z/i", $v)) {
                $return[] = $this -> format($k).' '.ucfirst(strtolower($v));
            } elseif (is_string($v) && !is_string($k)) {
                $return[] = $this -> format($v).' Asc';
            }
        }
        return implode(', ',$return);
    }
    
    /* Table */
    
    private function setDefaulttable(string $table) {
        $this -> setTable($table, true);
    }
    
    private function setTable(string $table, bool $default = false) {
        if (is_string($table) && strlen($table) > 0) {
            if ($default) {
                $this -> table['default'] = $table;
            } else {
                $this -> table['table'] = $table;
            }
        }
    }
    
    private function getTable() : string {
        $table = $this -> format($this -> table['table'], true);
        $this -> table['table'] = null;
        if (strlen($table) < 1) {
            $table = $this -> format($this -> table['default'], true);
            if (strlen($table) < 1) {
                die("Database table error: Not Set table.");
            }
        }
        return $table;
    }
    
    /* /table */
    
    /* Select */
    
    private function setSelect($data) {
        if (count($data) === 1 && is_string(reset($data)) && (preg_match("/\A\s*select\s+/i", reset($data)) || reset($data) === '*')) {
            $this -> select = reset($data);
        } else {
            if (!is_string($this -> select)) {
                $this -> select = array_merge($this -> select ?? [], $data);
            }
        }
    }
    
    private function getSelect(array $array = null) {
        if (is_null($array)) {
            $array = $this -> select;
            $this -> select = [];
        }
        if (is_array($array)) {
            $return = [];
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    $return = array_merge($return, $this -> getSelect($v));
                } else {
                    if (is_string($k)) {
                        $return[] = $this -> format($k)." As `$v`";
                    } else {
                        $return[] = $this -> format($v);
                    }
                }
            }
        } elseif (is_string($array)) {
            $return = ($array === '*') ? ['*'] : $array;
        }
        return $return ?? null;
    }
    
    /* /Select */
    
    /* Insert */
    
    private function setInsert(array $array) {
        $this -> insert = $array;
    }
    
    private function getInsert() {
        $array = $this -> insert;
        $this -> insert = null;
        if (is_null($array['multiple'])) {
            $keys = $this -> formatArrayToString(array_keys($array['simple']));
            if (!$keys) { die("Database insert error: keys are not correct."); }
            $values = $this -> sqlValueFormatToStr($array['simple']);
            if (!$values) { die("Database insert error: values are not correct."); }
            if (count($keys) !== count($values)) { die("Database insert error: keys and values count not identical."); }
            return ($array['separate']) ? ["($keys) Values($values)"] : "($keys) Values($values)";
        } else {
            $keys = $this -> formatArrayToString($array['simple']);
            if (!$keys) { die("Database insert error: keys are not correct."); }
            $values = [];
            foreach ($array['multiple'] as $k) {
                $value = $this -> sqlValueFormatToStr($k);
                if (!$value) { die("Database insert error: values are not correct."); }
                if (count($keys) !== count($value)) { die("Database insert error: keys and values count not identical."); }
                $values[] = $value;
            }
            if ($array['separate']) {
                $return = [];
                foreach ($values as $v) {
                    $return[] = "($keys) Values($v)";
                }
                return $return;
            } else {
                return "($keys) Values(".implode("),(", $values).")";
            }
        }
    }
    
    /* /Insert */
    
    /* Join */
    
    private function setJoin(array $array) {
        $this -> join[reset($array)][] = end($array);
    }
    
    private function getJoin() : array {
        $array = $this -> join ?? [];
        $this -> join = [];
        $return = [];
        foreach ($array as $k => $v) {
            if (is_string($k)) {
                $r = [];
                foreach ($v as $kk => $vv) {
                    $r[] = $this -> recursiveFormat($vv);
                }
                $return[] = 'Inner Join '.$this -> format($k, true).' On '.implode(' And ', $r);
            }
        }
        return $return;
    }   
    
    /* /Join */
    
    /* Where */
    
    private function setWhere(array $array) {
        $this -> where[] = $array;
    }
    
    private function getWhere() : string {
        $array = $this -> where ?? [];
        $this -> where = [];
        $return = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return[] = $this -> recursiveFormat($v, true);
            }
        }
        return 'Where '.implode(' And ', $return);
    }
    
    /* /Where */
    
    /* GroupBy */
    
    private function setGroup(string $group) {
        $this -> group = $group;
    }
    
    private function getGroup() : string {
        $group = $this -> group;
        $this -> group = null;
        return ($group) ? 'Group By '.$this -> format($group) : '';
    }
    
    /* /GroupBy */
    
    /* OrderBy */
    
    private function setOrder(array $order) {
        $this -> order = array_merge($this -> order ?? [], $order);
    }
    
    private function getOrder() : string {
        $order = $this -> order;
        $this -> order = [];
        return ($order) ? 'Order By '.$this -> recursiveOrder($order) : '';
    }
    
    /* /OrderBy */
    
    /* Limit */
    
    private function setLimit(array $limit) {
        $this -> limit['limit'] = reset($limit);
        $this -> limit['offset'] = end($limit);
    }
    
    private function getLimit() : string {
        $limit = $this -> limit['limit'];
        $offset = $this -> limit['offset'];
        $this -> limit = [];
        if (is_null($limit)) {
            return '';
        } else {
            return 'Limit '.$limit.(is_null($offset) ? '' : ' Offset '.$offset);
        }
    }
    
    /* /Limit */
    
}