<?php
/* Framework */
/* by Tibor Sándor */

define('FW_PHP_VERSION', '7.1');

if (version_compare(PHP_VERSION, FW_PHP_VERSION, '<')) {
	die('Upgrade your PHP version ('.PHP_VERSION.') to '.FW_PHP_VERSION.' or newer!');
}

define('ROOT_DIR', __DIR__);
define('DS', DIRECTORY_SEPARATOR);
define('WORKING_DIR', '');
define('APPS_DIR', ROOT_DIR.DS.'applications');

if (file_exists($aliases = APPS_DIR.DS.'aliases.php')) {
	$aliases = require_once($aliases);
} else {
	$aliases = [];
}

define('SHH', $_SERVER['HTTP_HOST']);
define('SRS', $_SERVER['REQUEST_SCHEME']);
define('SRU', $_SERVER['REQUEST_URI']);
define('SHAL', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
define('PROTOCOL', (SRS ?? 'http').'://');
$sd = explode('.', SHH);
$sd_size = sizeof($sd);

for($i = 1; $i <= $sd_size; $i++) {
	$domain = array_slice($sd, $sd_size-$i, $sd_size);
	$domain = implode('.', $domain);
	$subdomains = null;
	$subdomains = ($domain != SHH) ? str_replace('.'.$domain, '', SHH) : null;
	$domain = in_array($domain, array_keys($aliases)) ? $aliases[$domain] : $domain;
	if (file_exists($app_dir = APPS_DIR.DS.$domain)) {
		define('_DOMAIN', $domain);
		break;
	}
}

if (defined('_DOMAIN')) {
	define('SUBDOMAINS', $subdomains);
} else {
	die('The application directory ('.$app_dir.') does not exist!');
}

$dirs_check = [];

define('BACKUPS_DIR', ROOT_DIR.DS.'backups');
$dirs_check[] = BACKUPS_DIR;
define('DOMAIN', PROTOCOL.SHH.((!empty(WORKING_DIR)) ? '/'.WORKING_DIR : ''));
define('APPS_URL', DOMAIN.'/applications');
define('APP_DIR', APPS_DIR.DS._DOMAIN);
define('APP_URL', APPS_URL.'/'._DOMAIN);
define('APP_PUBLIC_DIR', APPS_DIR.DS._DOMAIN.DS.'public');
define('APP_PUBLIC_URL', APPS_URL.'/'._DOMAIN.DS.'public');
define('APP_PUBLIC_ASSETS_DIR', APP_PUBLIC_DIR.DS.'assets');
define('APP_PUBLIC_ASSETS_URL', APP_PUBLIC_URL.'/assets');
define('APP_PUBLIC_CACHE_DIR', APP_PUBLIC_DIR.DS.'cache');
$dirs_check[] = APP_PUBLIC_CACHE_DIR;
define('APP_PUBLIC_CACHE_URL', APP_PUBLIC_URL.'/cache');
define('APP_PUBLIC_FILES_DIR', APP_PUBLIC_DIR.DS.'files');
$dirs_check[] = APP_PUBLIC_FILES_DIR;
define('APP_PUBLIC_FILES_URL', APP_PUBLIC_URL.'/files');
define('APP_PUBLIC_LANGUAGES_DIR', APP_PUBLIC_DIR.DS.'languages');
$dirs_check[] = APP_PUBLIC_LANGUAGES_DIR;
define('APP_PUBLIC_LANGUAGES_URL', APP_PUBLIC_URL.'/languages');
define('APP_PUBLIC_MODELS_DIR', APP_PUBLIC_DIR.DS.'models');
$dirs_check[] = APP_PUBLIC_MODELS_DIR;
define('APP_PUBLIC_MODELS_URL', APP_PUBLIC_URL.'/models');
define('APP_PUBLIC_CSS_DIR', APP_PUBLIC_ASSETS_DIR.DS.'css');
$dirs_check[] = APP_PUBLIC_CSS_DIR;
define('APP_PUBLIC_CSS_URL', APP_PUBLIC_URL.'/css');
define('APP_PUBLIC_SCSS_DIR', APP_PUBLIC_ASSETS_DIR.DS.'scss');
$dirs_check[] = APP_PUBLIC_SCSS_DIR;
define('APP_PUBLIC_SCSS_URL', APP_PUBLIC_URL.'/scss');
define('APP_PUBLIC_IMG_DIR', APP_PUBLIC_ASSETS_DIR.DS.'img');
$dirs_check[] = APP_PUBLIC_IMG_DIR;
define('APP_PUBLIC_IMG_URL', APP_PUBLIC_URL.'/img');
define('APP_PUBLIC_JS_DIR', APP_PUBLIC_ASSETS_DIR.DS.'js');
$dirs_check[] = APP_PUBLIC_JS_DIR;
define('APP_PUBLIC_JS_URL', APP_PUBLIC_URL.'/js');
define('APP_PUBLIC_PLUGINS_DIR', APP_PUBLIC_ASSETS_DIR.DS.'plugins');
$dirs_check[] = APP_PUBLIC_PLUGINS_DIR;
define('APP_PUBLIC_PLUGINS_URL', APP_PUBLIC_URL.'/plugins');
define('APP_ROOT_DIR', APPS_DIR.DS._DOMAIN.DS.'root');
define('APP_ROOT_URL', APPS_URL.'/'._DOMAIN.DS.'root');
define('APP_SUB_DIR', APP_ROOT_DIR.DS.($sub_dir = (is_null(SUBDOMAINS) || SUBDOMAINS == 'www') ? 'www' : (file_exists(APP_ROOT_DIR.DS.SUBDOMAINS) ? SUBDOMAINS : 'www')));
define('APP_SUB_URL', APP_ROOT_URL.'/'.$sub_dir);
define('APP_ASSETS_DIR', APP_SUB_DIR.DS.'assets');
define('APP_ASSETS_URL', APP_SUB_URL.'/assets');
define('APP_FILES_DIR', APP_SUB_DIR.DS.'files');
$dirs_check[] = APP_FILES_DIR;
define('APP_FILES_URL', APP_SUB_URL.'/files');
define('APP_LANGUAGES_DIR', APP_SUB_DIR.DS.'languages');
$dirs_check[] = APP_LANGUAGES_DIR;
define('APP_LANGUAGES_URL', APP_SUB_URL.'/languages');
define('APP_MODELS_DIR', APP_SUB_DIR.DS.'models');
$dirs_check[] = APP_MODELS_DIR;
define('APP_MODELS_URL', APP_SUB_URL.'/models');
define('APP_VIEWS_DIR', APP_SUB_DIR.DS.'views');
$dirs_check[] = APP_VIEWS_DIR;
define('APP_VIEWS_URL', APP_SUB_URL.'/views');
define('APP_CONTROLLERS_DIR', APP_SUB_DIR.DS.'controllers');
$dirs_check[] = APP_CONTROLLERS_DIR;
define('APP_CONTROLLERS_URL', APP_SUB_URL.'/controllers');
define('APP_AJAX_DIR', APP_SUB_DIR.DS.'ajax');
$dirs_check[] = APP_AJAX_DIR;
define('APP_AJAX_URL', APP_SUB_URL.'/ajax');
define('APP_CSS_DIR', APP_ASSETS_DIR.DS.'css');
$dirs_check[] = APP_CSS_DIR;
define('APP_CSS_URL', APP_ASSETS_URL.'/css');
define('APP_SCSS_DIR', APP_ASSETS_DIR.DS.'scss');
$dirs_check[] = APP_SCSS_DIR;
define('APP_SCSS_URL', APP_ASSETS_URL.'/scss');
define('APP_IMG_DIR', APP_ASSETS_DIR.DS.'img');
$dirs_check[] = APP_IMG_DIR;
define('APP_IMG_URL', APP_ASSETS_URL.'/img');
define('APP_JS_DIR', APP_ASSETS_DIR.DS.'js');
$dirs_check[] = APP_JS_DIR;
define('APP_JS_URL', APP_ASSETS_URL.'/js');
define('APP_PLUGINS_DIR', APP_ASSETS_DIR.DS.'plugins');
$dirs_check[] = APP_PLUGINS_DIR;
define('APP_PLUGINS_URL', APP_ASSETS_URL.'/plugins');

foreach ($dirs_check as $v) {
	if (!is_dir($v)) {
		mkdir($v, 0700, true);
	}
}

$config = [
	'error_reporting'			=> true,
	'default_timezone'			=> "Europe/Budapest",
	'default_language'			=> "hu",
	'default_controller'		=> "welcome",
	'default_method'			=> "index",
	'mysql_host'				=> null,
	'mysql_username'			=> null,
	'mysql_password'			=> null,
	'mysql_database'			=> null,
	'mysql_charset'				=> null,
	'mysql_cache'				=> true,
	'mysql_cache_time'			=> 3600, //1 hour
	'default_cache_time'		=> 86400, //1 day
	'default_cookie_time'		=> 86400, //1 day
	'google_maps_api_key'		=> null,
	'openweathermap_api_key'	=> null,
	'secret_key'				=> "s3cr3t_k3y",
];

$public_config_file = APP_PUBLIC_DIR.DS.'config.php';
$public_config = [];
$app_config_file = APP_SUB_DIR.DS.'config.php';
$app_config = [];

function createFile(string $filename, string $content) {
	$file = fopen($filename, "w") or die("Unable to open file!");
	fwrite($file, $content);
	fclose($file);
}

if (file_exists($app_config_file)) {
	$app_config = require_once($app_config_file);
}

if (file_exists($public_config_file)) {
	$public_config = require_once($public_config_file);
} else {
	createFile($public_config_file, "<?php\nreturn ".var_export($config, true).";");
}

if (is_array($public_config) && count($public_config) > 0)	{ $config = array_merge($config, $public_config); }
if (is_array($app_config) && count($app_config) > 0)		{ $config = array_merge($config, $app_config); }

foreach ($config as $k => $v) {
	define(strtoupper($k), $v);
}

if (ERROR_REPORTING) {
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set('error_reporting', E_ALL ^ E_NOTICE);
} else {
	error_reporting(0);
	ini_set('error_reporting', 0);
}

date_default_timezone_set(DEFAULT_TIMEZONE);

spl_autoload_register(function ($class) {
	$explode = explode('\\', $class);
	$namespace = reset($explode);
	$class = end($explode);
	$directory = (in_array($namespace, array('core', 'helper'))) ? ROOT_DIR.DS.(($namespace == 'core') ? 'system'.DS.$namespace : 'system'.DS.$namespace.'s') : (($namespace == 'publicmodel') ? APP_PUBLIC_DIR.DS.'models' : APP_SUB_DIR.DS.$namespace.(($namespace == 'ajax') ? '' : 's'));
	$_class = $directory.DS.strtolower($class).'.php';
	if(file_exists($_class)) { require_once($_class); }
});

$class_alias = [
	'core' => [
		'Router'				=> ['Router','helper','model','controller'],
		'Language'				=> 'model',
		'Helper'				=> ['helper','controller','publicmodel','model'],
		'Controller'			=> 'controller',
		'Publicmodel'			=> ['publicmodel','model','controller'],
		'Model'					=> ['model','controller'],
		'View'					=> 'controller',
		'DatabaseConnection'	=> 'helper'
	]
];

foreach ($class_alias as $k => $v) {
	$alias_original_1 = '\\'.$k.'\\';
	foreach ($v as $kk => $vv) {
		$alias_original_2 = $alias_original_1.$kk;
		$vv = (is_array($vv)) ? $vv : [$vv];
		foreach ($vv as $vvv) {
			if ($kk == $vvv) {
				class_alias($alias_original_2, $kk);
			} else {
				class_alias($alias_original_2, '\\'.$vvv.'\\'.$kk);
			}
		}
	}
}

Router::init();
