<?php
namespace core;

if (class_exists('\model\Authentication')) {
	class Authentication extends \model\Authentication {

		private static $instance;
		protected $authentication;

		public function __construct() { parent::__construct(); $this -> authentication = $this; }

		private static function getInstance() : self {
			if (is_null(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		public static function init() {
			return self::getInstance();
		}

	}
} else {
	class Authentication {

		private static $instance;

		public function __construct() { $this -> authentication = $this; }

		private static function getInstance() : self {
			if (is_null(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		public static function init() {
			return self::getInstance();
		}

	}
}
?>
