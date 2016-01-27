<?php

namespace App\Brokers;

use Dmlogic\DataItem;
use SimpleXMLElement;
use GuzzleHttp\Client;
use App\CurrencyConverter;

abstract class Broker {

    /**
     * Ddefault curl values
     */
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0';
    const ACCEPT     = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

    protected $liveData;

    public function __construct() {
        $this->connector = new Client();
    }

    protected function plainNumber($value)
    {
        return trim(preg_replace( '/[^0-9.]/', '', $value ));
    }

    public function getData()
    {
        $this->lookupData();
        $this->setValue();
        $this->setProfit();

        return $this->liveData->getAll();
    }

    protected function setProfit()
    {
        $this->liveData->profit = $this->liveData->value - $this->investment['cost_price'];
    }

    protected function setValue()
    {
        $value = $this->liveData->units_held * $this->liveData->sell_price;
        if($this->investment['currency'] !== 'GBP') {
            $convertor = new CurrencyConverter($this->investment['currency'],'GBP');
            $value = $convertor->convert($value);
        }

        $this->liveData->value =  $value / $this->investment['price_units'];

    }

}