<?php

namespace App\Providers;

use SimpleXMLElement;
use GuzzleHttp\Client;

abstract class Provider {

    /**
     * Ddefault curl values
     */
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0';
    const ACCEPT     = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

    public function __construct() {
        $this->connector = new Client();
    }

}