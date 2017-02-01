<?php
namespace helper;

class Date {

    private $date_format;

    public function __construct() {
        $this -> date_format = [
            'hu' => 'Y. m. d. H:i',
            'ru' => 'd.m.Y H:i',
            'en' => 'd M Y H:i'
        ];
    }

    public function format(string $date = null) : string {
        $date = strtotime($date);
        return date($this -> date_format[Router::language()],$date);
    }

}
