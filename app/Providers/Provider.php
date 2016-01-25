<?php

namespace App\Providers;

use SimpleXMLElement;
use GuzzleHttp\Client;

abstract class Provider {

    public function __construct() {
        $this->connector = new Client();
    }

}