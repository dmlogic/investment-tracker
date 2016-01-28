<?php

namespace App\Brokers;

use Dmlogic\DataItem;
use SimpleXMLElement;
use GuzzleHttp\Client;
use App\CurrencyConverter;

abstract class Broker {

    /**
     * Data about the investment being parsed
     *
     * @var array
     */
    protected $investment;

    /**
     * Default curl values
     */
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0';
    const ACCEPT     = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

    /**
     * This is what gets looked up
     * @var Dmlogic\DataItem
     */
    protected $liveData;

    public function __construct(array $investment) {
        $this->investment = $investment;
        $this->connector = new Client;
    }

    /**
     * Strip out surplus markup to get a simple number
     *
     * @param  string $value
     * @return string
     */
    protected function plainNumber($value)
    {
        return trim(preg_replace( '/[^0-9.]/', '', $value ));
    }

    /**
     * Public interface to get data
     *
     * @return array
     */
    public function getData()
    {
        $this->lookupData();
        $this->setValue();
        $this->setProfit();

        return $this->liveData->getAll();
    }

    /**
     * Calculate profit from the other things we know
     *
     * @return void
     */
    protected function setProfit()
    {
        $this->liveData->profit = $this->liveData->value - $this->investment['cost_price'];
    }

    /**
     * Calculate value from the other things we know
     *
     * @return void
     */
    protected function setValue()
    {
        $value = $this->liveData->units_held * $this->liveData->sell_price;
        if($this->investment['currency'] !== 'GBP') {
            $convertor = new CurrencyConverter($this->investment['currency'],'GBP');
            $value = $convertor->convert($value);
        }

        // Normalise the output based on knowing what units to expect
        $this->liveData->value =  $value / $this->investment['price_units'];

    }

}