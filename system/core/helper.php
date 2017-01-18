<?php
namespace core;

class Helper {
    
    public static function __callStatic($name, $arguments) {
        return Load::helper($name, $arguments);
    }
    
}