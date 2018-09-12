<?php

namespace Dragon;

use GuzzleHttp\Client;

class CurrencyConverter {

    private $from;
    private $to;

    public function __construct($from, $to) {

        $this->from = strtoupper($from);
        $this->to = strtoupper($to);
    }

    public function convert($value)
    {
        $url = sprintf('https://www.oanda.com/currency/converter/update?base_currency_0=%s&quote_currency=%s&end_date=2018-09-11&view=details&id=6&action=C&',$this->to,$this->from);
        $options = [
            'headers' => [
                'X-Requested-With'  => 'XMLHttpRequest',
            ],
        ];
        $guzzle = new Client;
        $response = $guzzle->get($url,$options );
        $result = json_decode($response->getBody()->__toString());
        return $value * $result->data->bid_ask_data->bid;
    }
}