<?php
namespace helper;

class Text {
	
	public function ucfirst(string $string, string $encoding = null) : string {
		if(is_null($encoding)) {
			$encoding = 'UTF-8';
		}
		return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding).mb_substr($string, 1, mb_strlen($string, $encoding) - 1, $encoding);
	}
	
	public function random(int $length = null, string $string = null) : string {
		$length = (is_null($length)) ? 6 : $length;
		$chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
		$random = array();
		$chars_length = strlen($chars) - 1;
		for($i=0; $i<$length; $i++) {
			$random[] = $chars[rand(0, $chars_length)];
		}
		return urlencode(implode($random).((is_null($string)) ? '' : '_'.$string));
	}
	
	public function print_a(array $a = null) {
		print '<pre>';
		print_r($a ?? []);
		print '</pre>';
	}
    
    public function getRandomNumbers(int $n, int $min, int $max) : array {
        $return = [];
        while(true) {
            if (!in_array($r = mt_rand($min, $max), $return)) {
                $return[] = $r;
            }
            if (count($return) == $n) {
                break;
            }
        }
        return $return;
    }
	
}
?>