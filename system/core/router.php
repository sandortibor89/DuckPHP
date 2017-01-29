<?php
namespace core;

class Router {

	private static $instance, $language, $controller, $method, $params, $gets;

	private static function getInstance() : self {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		Language::init();
		$route = explode('/', trim(current(explode('?', preg_replace('/'.str_replace('/', '\/',WORKING_DIR).'/', '', SRU, 1))), '/'));
		$route_index_start = (isset($route[0]) && in_array($route[0], Language::getAll())) ? 1 : 0;
		self::$language = ($route_index_start === 0) ? LANGUAGE : $route[0];
		self::$controller = (isset($route[$route_index_start]) && !empty($route[$route_index_start])) ? $route[$route_index_start] : DEFAULT_CONTROLLER;
		self::$method = (isset($route[$route_index_start+1]) && !empty($route[$route_index_start+1])) ? $route[$route_index_start+1] : DEFAULT_METHOD;
		self::$params = [];
		for($i = $route_index_start+2; $i < sizeof($route); $i++) {
			self::$params[] = $route[$i];
		}
		self::$gets = $_GET;
	}

    public static function init() {
        self::getInstance();
        $controller = APP_CONTROLLERS_DIR.DS.strtolower(self::replace('controller')).'.php';
        if (file_exists($controller)) {
            require_once($controller);
			$controller = '\controller\\'.ucfirst(self::replace('controller'));
			if (class_exists($controller, false)) {
				if(method_exists($controller, self::replace('method'))) {
					if(sizeof(self::$params) === 0) {
						call_user_func(array(new $controller, self::replace('method')));
					} else {
						call_user_func_array(array(new $controller, self::replace('method')), self::$params);
					}
				} else if(self::$method !== DEFAULT_METHOD) {
					$params = [];
					if(!in_array(self::$method, self::$params)) {
                        $params[] = self::$method;
                    }
					self::$method = DEFAULT_METHOD;
					self::$params = array_merge($params, self::$params);
                    return self::init();
				} else {
                    die('Method: ('.self::replace('method').') does not exist.');
                }
			} else {
                die('Class: "'.$controller.'" does not exist.');
            }
        } else if (self::$controller !== DEFAULT_CONTROLLER) {
			$params = [self::$controller];
			if(self::$method !== DEFAULT_METHOD) {
				$params[] = self::$method;
			}
			$method = $params[0];
			array_shift($params);
			self::$controller = DEFAULT_CONTROLLER;
			self::$method = $method;
			self::$params = array_merge($params, self::$params);
			return self::init();
        } else {
			$file = fopen($controller, "w") or die("Unable to open file!");
			fwrite($file, "<?php\nnamespace controller;\n\nclass Welcome extends controller {\n\n\tpublic function index() {\n\t\tRouter::info(true);\n\t}\n\n}");
			fclose($file);
            return self::init();
        }
    }

    private static function replace(string $var) : string {
		return preg_replace('/[^a-zA-Z0-9]+/', '', self::$$var);
	}

    public static function url(string $url = null) : string {
        if (is_null($url) || empty($url)) {
            $url = implode('/', array_filter([
                ((self::$language == LANGUAGE) ? '' : self::$language),
                ((self::$controller == DEFAULT_CONTROLLER) ? '' : self::$controller),
                ((self::$method == DEFAULT_METHOD) ? '' : self::$method),
                ((empty(self::$params)) ? '' : implode('/', self::$params)),
                ((empty(self::$gets)) ? '' : '?'.http_build_query(self::$gets)),
            ]));
        } else {
            preg_match_all('/{(.*?)}/', $url, $matches, PREG_SET_ORDER);
            foreach ($matches as $sub_v) {
                $replace = '';
                switch (self::alias($sub_v[1])) {
                    case 'language' :
                        $replace = (self::$language == LANGUAGE) ? '' : self::$language;
                        break;
                    case 'controller' :
                        $replace = (self::$controller == DEFAULT_CONTROLLER) ? '' : self::$controller;
                        break;
                    case 'method' :
                        $replace = (self::$method == DEFAULT_METHOD) ? '' : self::$method;
                        break;
                    case 'params' :
                        $replace = (empty(self::$params)) ? '' : implode('/', self::$params);
                        break;
                    case 'gets' :
                        $replace = (empty(self::$gets)) ? '' : '?'.http_build_query(self::$gets);
                        break;
                }
                $url = str_replace($sub_v[0], $replace, $url);
            }
        }
        return implode('/', [DOMAIN, trim(preg_replace('/\/+/', '/', $url), '/')]);
    }

    public static function redirect(string $url = null) {
        $url = self::url($url);
        if ($url != self::url()) {
            header("Location: $url");
        }
    }

    public static function info(bool $print = false) : array {
		$return = [
			'Language' => self::$language,
			'Controller' => self::$controller,
			'Method' => self::$method,
			'Parameters' => self::$params,
			'GET parameters' => self::$gets
		];
		if ($print) {
			$t = Helper::text();
			$t -> print_a($return);
		}
		return $return;
	}

    private static function alias(string $alias) : string {
        $aliases = [
            'language' => ['l', 'lang'],
            'controller' => ['c'],
            'method' => ['m'],
			'params' => ['p'],
            'gets' => ['g', 'get'],
        ];
        foreach ($aliases as $k => $v) {
            $aliases = array_merge($aliases, array_fill_keys($v, $k));
            $aliases[$k] = $k;
        }
        return $aliases[$alias];
    }

    public static function __callStatic($name, $arguments) {
        $name = self::alias($name);
        $string = (reset($arguments)) ? reset($arguments) : null;
        return is_null($string) ? self::$$name : ($string == self::$$name);
    }

}
