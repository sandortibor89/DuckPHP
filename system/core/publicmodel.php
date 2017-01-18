<?php
namespace core;

class Publicmodel {
    
    public static function __callStatic($name, $arguments) {
        return Load::publicmodel($name, $arguments);
    }
    
}