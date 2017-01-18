<?php
namespace core;

class Model {
    
    public static function __callStatic($name, $arguments) {
        return Load::model($name, $arguments);
    }
    
}