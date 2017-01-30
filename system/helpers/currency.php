<?php
namespace helper;

class Currency {

	private $cache_helper;

	public function __construct() {
		$this -> cache_helper = Helper::cache([
			'time' => 60*60*24,
			'dir' => "currency"
		]);
	}

	public function get(array $currency) : float {
		$from = strtoupper($currency['from']);
		$to = strtoupper($currency['to']);;
		$amount = $currency['amount'] ?? 1;
		if (!empty($from) && !empty($to)) {
			if ($return = $this -> cache_helper -> get($from.$to)) {
				$return = $amount * $return;
			} else {
				$return = $amount * $this -> set($from, $to);
			}
		}
		return $return ?? 0;
	}

	private function set(string $from, string $to) : float {
		$url = "http://api.fixer.io/latest?base=$from&symbols=$to";
		if ($rate = json_decode(file_get_contents($url), true)['rates'][$to]) {
			$this -> cache_helper -> set($from.$to, $rate);
		}
		return $rate ?? 0;
	}

}
