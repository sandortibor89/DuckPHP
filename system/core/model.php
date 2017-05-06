<?php
namespace core;

class Model extends Authentication {

	public function __get($name) {
		if ($name === 'global') {
			return parent::init();
		}
	}

	public static function __callStatic($name, $arguments) {
		return Load::model($name, $arguments);
	}

}
