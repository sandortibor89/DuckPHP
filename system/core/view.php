<?php
namespace core;

class View {
    
    public static function header(array $arguments = []) {
        Load::header($arguments);
    }
    
    public static function footer(array $arguments = []) {
        Load::footer($arguments);
    }
    
    public static function __callStatic($name, $arguments) {
        Load::view($name, reset($arguments));
    }
    
}