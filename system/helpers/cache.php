<?php
namespace helper;

class Cache {

    private $dir, $lang, $time;

    public function __construct($a = []) {
        $this -> dir = (isSet($a['dir'])) ? APP_PUBLIC_CACHE_DIR.DS.$a['dir'] : APP_PUBLIC_CACHE_DIR;
		$this -> lang = (isSet($a['lang'])) ? ((in_array($a['lang'], Language::getAll())) ? $a['lang'] : Router::language()) : Router::language();
		$this -> time = $a['time'] ?? DEFAULT_CACHE_TIME;
    }

    public function get(string $name, bool $ret_array = true) {
        $file = $this -> set_file($name);
        if(file_exists($file)) {
            $content = explode(PHP_EOL, file_get_contents($file));
            if(time() - $content[0] <= filemtime($file)) {
                return json_decode($content[1], $ret_array);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function set(string $name, $data, int $time = null) : bool {
        $time = $time ?? $this -> time;
        return file_put_contents($this -> set_file($name), $time.PHP_EOL.json_encode($data));
    }

    private function set_file(string $name) : string {
		if(!is_dir($this -> dir)) {
            mkdir($this -> dir, 0700, true);
        }
		return $this -> dir.DS.$this -> lang.'_'.md5($name).'.html';
	}

}
