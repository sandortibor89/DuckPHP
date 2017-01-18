<?php
namespace core;

class Language {
    
    private static $instance, $text_helper, $languages, $language_keys;
    
    private static function getInstance() : self {
		if (is_null(self::$instance)) {
            self::$instance = new self();
        }	
		return self::$instance;
	}
    
    public function __construct() {
        $language_files = array_diff(scandir(APP_LANGUAGES_DIR), ['.', '..']);
        $public_language_files = array_diff(scandir(APP_PUBLIC_LANGUAGES_DIR), ['.', '..']);
        $walk_function = function (&$item) {
            $explode = explode('.', $item);
            if (count($explode) === 2 && strlen($language = reset($explode)) === 2 && end($explode) === 'php') {
                $item = $language;
            } else {
                $item = null;
            }
        };
        array_walk($language_files, $walk_function);
        array_walk($public_language_files, $walk_function);
        self::$languages['root'] = array_filter($language_files);
        self::$languages['public'] = array_filter($public_language_files);
        self::$languages['all'] = array_unique(array_merge(self::$languages['root'], self::$languages['public']));
        define('LANGUAGE', ((empty($lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2))) ? DEFAULT_LANGUAGE : ((in_array($lang, self::$languages['all'])) ? $lang : DEFAULT_LANGUAGE)));
        self::$text_helper = Helper::text();
    }
    
    public static function init() {
        self::getInstance();
    }
    
    private static function loadLanguageKeys() {
        if (empty(self::$language_keys)) {
            $language = Router::language();
            $language_file = APP_LANGUAGES_DIR.DS.$language.'.php';
            $public_language_file = APP_PUBLIC_LANGUAGES_DIR.DS.$language.'.php';
            $language_file = (file_exists($language_file)) ? ((is_array($include = include_once($language_file))) ? $include : []) : [];
            $public_language_file = (file_exists($public_language_file)) ? ((is_array($include = include_once($public_language_file))) ? $include : []) : [];
            self::$language_keys = array_replace_recursive($public_language_file, $language_file);
        }
    }
    
    public static function getAll() : array {
        return self::$languages['all'];
    }
    
    public static function get(string $key = null, bool $uc = false) : string {
        self::loadLanguageKeys();
        if (is_null($key) || empty($key)) {
            return '';
        } else {
            $keys = explode('.', $key);
            $language_keys = self::$language_keys;
            for ($i = 0; $i<count($keys); $i++) {
                $language_keys = $language_keys[$keys[$i]];
                if (!empty($language_keys[$keys[$i]]) && !is_array($language_keys[$keys[$i]]) && $i == count($keys)-1) {
                    $language_keys = $language_keys[$keys[$i]];
                }
            }
            return (is_array($language_keys) || empty($language_keys)) ? "[$key]" : (($uc) ? self::$text_helper -> ucfirst($language_keys) : $language_keys);
        }
    }
    
    public static function getUc(string $key = null) : string {
        return self::get($key, true);
    }
    
}