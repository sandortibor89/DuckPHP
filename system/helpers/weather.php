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

    public function get(string $location) : array {
        if (!is_null($location) && !empty($location)) {
			if ($result = $this -> cache_helper -> get($location)) {
				return $result;
			} else {
				return $this -> set($location);
			}
        } else {
            return false;
        }
    }

	private function set(string $location) : array {
		$url = "http://query.yahooapis.com/v1/public/yql";
		if (!$return = $this -> cache_helper -> get($location)) {
			if (!$woeid = $this -> cache_helper -> get($location."woeid")) {
				$query = "select woeid from geo.places(1) where text=\"".$location."\"";
				$query_url = $url.'?q='.urlencode($query).'&format=json';
				$session = curl_init($query_url);
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
				$json = json_decode(curl_exec($session), true);
				$woeid = $json['query']['results']['place']['woeid'];
				if (!is_null($woeid)) {
					$this -> cache_helper -> set($location."woeid", $woeid, 60*60*24*30);
				}
			}
			if (!is_null($woeid)) {
				$query = "select item.condition from weather.forecast where woeid in ($woeid) and u=\"c\"";
				$query_url = $url.'?q='.urlencode($query).'&format=json';
				$session = curl_init($query_url);
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
				$json = json_decode(curl_exec($session), true);
				$return['code'] = $json['query']['results']['channel']['item']['condition']['code'];
				$return['temp'] = $json['query']['results']['channel']['item']['condition']['temp'];
				if (!is_null($return['code']) && !is_null($return['temp'])) {
					$this -> cache_helper -> set($location, $return, 60*60);
				}
			}
		}
		return $return ?? null;
    }

}
