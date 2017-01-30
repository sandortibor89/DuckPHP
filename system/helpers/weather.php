<?php
namespace helper;

class Weather {

    private $api_key, $cache_helper;

	public function __construct(array $data = []) {
		$this -> api_key = $data['api_key'] ?? OPENWEATHERMAP_API_KEY;
		if (!is_null($this -> api_key)) {
			$this -> cache_helper = Helper::cache([
				'time' => 60*30,
				'dir' => "weather"
			]);
		}
	}

	public function get(string $location) : array {
		if (!is_null($this -> api_key)) {
			if (!$return = $this -> cache_helper -> get($location)) {
				$return = $this -> set($location);
			}
		} else {
			$return = [];
		}
		return $return;
	}

	private function set(string $location) : array {
		$url = "http://api.openweathermap.org/data/2.5/weather?q=".urlencode($location)."&appid=".$this -> api_key."&units=metric";
		$obj = json_decode(file_get_contents($url), true);
		$return['day'] = substr($obj['weather'][0]['icon'], -1) === "n" ? "night" : "day";
		$return['code'] = $obj['weather'][0]['id'];
		$return['temp'] = round($obj['main']['temp']);
		if (!empty($return['code']) && !empty($return['temp'])) {
			$this -> cache_helper -> set($location, $return);
			return $return;
		} else {
			return [];
		}
    }

}
