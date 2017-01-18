<?php
namespace helper;

class Weather {
    
    private $cache_helper;
    
    public function __construct() {
        $this -> cache_helper = Helper::cache([
            'time' => '1800',
            'dir' => 'weather'
        ]);
    }
    
    public function get($location) {
        if (!is_null($location) && !empty($location)) {
            if ($result = $this -> db -> nocache() -> get('*', ['Where' => '`location` = "'.$location.'"'])) {
                if (date('Y-m-d H:i:s', strtotime("+1 hour", strtotime($result['updated']))) < date('Y-m-d H:i:s')) {
                    return $this -> set($location);
                } else {
                    unset($result['id']);
                    unset($result['inserted']);
                    unset($result['updated']);
                    return array_filter($result);
                }
            } else {
                return $this -> set($location);
            }
        } else {
            return false;
        }
    }
    
    private function set($location) {
        $url = "http://query.yahooapis.com/v1/public/yql";
        $query = 'select item.condition from weather.forecast where woeid in (select woeid from geo.places(1) where text="'.$location.'") and u="c"';
        $query_url = $url.'?q='.urlencode($query).'&format=json';
        $session = curl_init($query_url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($session);
        $sql['location'] = $location;
        $sql['code'] = json_decode($json, true)['query']['results']['channel']['item']['condition']['code'];
        $sql['temp'] = json_decode($json, true)['query']['results']['channel']['item']['condition']['temp'];
        $sql['updated'] = date('Y-m-d H:i:s');
        if ($result = $this -> db -> nocache() -> get('*', ['Where' => '`location` = "'.$location.'"'])) {
            if ($this -> db -> table('weather') -> update($sql, $result['id'])) {
                unset($sql['updated']);
                return $sql;
            } else {
                return false;
            }
        } else {
            $sql['inserted'] = date('Y-m-d H:i:s');
            if ($this -> db -> insert($sql)) {
                
                unset($sql['updated']);
                unset($sql['inserted']);
                return $sql;
            } else {
                return false;
            }
        }
    }
    
}