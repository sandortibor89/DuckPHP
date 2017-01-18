<?php
namespace core;

if (class_exists('\model\Authentication')) {
    class Controller extends \model\Authentication {}
} else {
    class Controller {}
}