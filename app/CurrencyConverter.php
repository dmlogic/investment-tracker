<?php

namespace App;

class CurrencyConverter {

    private $from;
    private $to;

    public function __construct($from, $to) {

        $this->from = $from;
        $this->to = $to;
    }

    public function convert($value)
    {
        $url = sprintf('http://api.fixer.io/latest?base=%s&symbols=%s',$this->from,$this->to);
        $result = json_decode(file_get_contents($url),true);
        return $value * $result['rates'][$this->to];
    }
}