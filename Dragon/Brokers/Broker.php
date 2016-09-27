<?php

namespace Dragon\Brokers;

use SimpleXMLElement;
use GuzzleHttp\Client;
use Dragon\CurrencyConverter;
use Dragon\Presenters\CraftPresenter;

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

    public function __construct($investment) {
        $this->investment = new CraftPresenter($investment);
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

        return (array) $this->liveData;
    }

    /**
     * Calculate profit from the other things we know
     *
     * @return void
     */
    protected function setProfit()
    {
        $this->liveData->profit = $this->liveData->value - $this->investment->totalCost();
    }

    /**
     * Calculate value from the other things we know
     *
     * @return void
     */
    protected function setValue()
    {
        $value = $this->liveData->units_held * $this->liveData->sell_price;
        if($this->investment->currency->value  !== 'GBP') {
            $convertor = new CurrencyConverter($this->investment->currency,'GBP');
            $value = $convertor->convert($value);
        }

        // Normalise the output based on knowing what units to expect
        $this->liveData->value =  $value / $this->investment->priceUnits;
    }

    protected function getCurlOptions()
    {
        return [
            'headers' => [
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];
    }

}