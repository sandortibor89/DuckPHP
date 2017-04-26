<?php
namespace core;

class Load {

	private static $variables;

	public static function model(string $model, array $args = []) {
		$obj = '\model\\'.ucfirst($model);
		return new $obj($args);
	}

	public static function publicmodel(string $model, array $args = []) {
		$obj = '\publicmodel\\'.ucfirst($model);
		return new $obj($args);
	}

	public static function helper(string $helper, array $args = []) {
		$obj = '\helper\\'.ucfirst($helper);
		$class = new \ReflectionClass($obj);
		$instance = $class -> newInstanceArgs($args);
		return $instance;
	}

	public static function header(array $variables = []) {
		$file = APP_VIEWS_DIR.DS.'header.php';
		self::load($file, $variables);
	}

	public static function footer(array $variables = []) {
		$file = APP_VIEWS_DIR.DS.'footer.php';
		self::load($file, $variables);
	}

	public static function view(string $view, $variables = []) {
		$file = APP_VIEWS_DIR.DS.$view.'.main.php';
		self::load($file, $variables);
	}

	private static function load(string $file, $variables = []) {
		if (file_exists($file)) {
			if (count($variables) > 0 && is_array($variables)) {
				if (count(self::$variables) > 0 && is_array(self::$variables)) {
					self::$variables = array_merge(self::$variables, $variables);
				} else {
					self::$variables = $variables;
				}
			}
			if (count(self::$variables) > 0 && is_array(self::$variables)) {
				foreach (self::$variables as $k => $v) {
					$$k = $v;
				}
			}
			ob_start();
			include_once($file);
			$buffer = ob_get_clean();
			self::replaceVars($buffer, self::$variables);
			self::replaceViews($buffer, self::$variables);
			self::replaceConstants($buffer);
			self::replaceUrls($buffer);
			self::replaceLangKeys($buffer);
			echo $buffer;
		} else {
			die('The view ('.$file.') does not exist.');
		}
	}

	private static function replaceViews(string &$code, $variables) {
		preg_match_all('/@(l?)\[\s*([\w.-]*)\s*\]/Ui', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $v) {
			ob_start();
			self::load(APP_VIEWS_DIR.DS.strtolower($v[2]).'.sub'.((strtolower($v[1]) == 'l') ? '.'.Router::language() : '').'.php', $variables);
			$sub_code = ob_get_clean();
			$code = str_replace($v[0], $sub_code, $code);
		}
	}

	private static function replaceConstants(string &$code) {
		preg_match_all('/@{\s*([\w]*)\s*}/Ui', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $v) {
			$code = str_replace($v[0], constant($v[1]), $code);
		}
	}

	private static function replaceVars(string &$code, $variables) {
		preg_match_all('/{{\s*\$([\w\->]+)\s*(\|{2}\s*(\'(.*)\'|"(.*)")\s*)?}}/Ui', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $v) {
			$var = $variables;
			foreach (explode('->', $v[1]) as $key) {
				if (array_key_exists($key, $var ?? [])) {
					$var = $var[$key];
				} else {
					$var = null;
					break;
				}
			}
			$default = (($v[4]) ? $v[4] : $v[5]);
			$code = str_replace($v[0], ((is_array($var) || empty($var)) ? $default : $var), $code);
		}
	}

	private static function replaceUrls(string &$code) {
		preg_match_all('/@url\[\s*(\S*)\s*]/Ui', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $v) {
			$code = str_replace($v[0], Router::url($v[1]), $code);
		}
	}

	private static function replaceLangKeys(string &$code) {
		preg_match_all("/@(l){\s*(\w[\w.-]*\w)\s*}/Ui", $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $v) {
			switch($v[1]) {
				case 'l': $code = str_replace($v[0], Language::get($v[2]), $code); break;
				case 'L': $code = str_replace($v[0], Language::getUc($v[2]), $code); break;
			}
		}
	}

}
