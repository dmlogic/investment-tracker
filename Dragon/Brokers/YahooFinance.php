<?php

namespace Dragon\Brokers;

use DateTime;
use StdClass;

class YahooFinance extends HargreavesLansdown {

    /**
     * Where to get historical chart data from
     */
    const DATA_URL = 'http://query.yahooapis.com/v1/public/yql';

    /**
     * Starts the crawl
     *
     * @return void
     */
    protected function lookupData()
    {
        $this->liveData = new StdClass;

        $this->liveData->type       = $this->investment->investmentType->value;
        $this->liveData->name       = $this->investment->title;
        $this->liveData->cost_price = $this->investment->totalCost();
        $this->liveData->currency   = $this->investment->currency;
        $this->liveData->units_held = $this->investment->unitsHeld;

        // $this->getLiveData(); // Not sure about Yahoo's Bid price
        $this->getCrawledData();
        $this->getHistoricalData();

    }

    protected function getLiveData()
    {
        $query = '?q=select * from yahoo.finance.quotes where symbol in ("'.$this->investment->apiId.'")&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=';

        $response = $this->connector->get(static::DATA_URL.$query,$this->getCurlOptions());
        $this->parseLiveJson(json_decode($response->getBody()));
    }

    protected function parseLiveJson($json)
    {
        $change = (float) $json->query->results->quote->Change;
        if($change > 0) {
            $changeDirection = 'up';
        } elseif($change < 0) {
            $changeDirection = 'down';
        } else {
            $changeDirection = '';
        }

        $this->liveData->sell_price     = (float) $json->query->results->quote->Bid;
        $this->liveData->last_price     = (float) $json->query->results->quote->LastTradePriceOnly;
        $this->liveData->last_change    = $this->plainNumber($change);
        $this->liveData->last_direction = $changeDirection;
    }

    protected function getHistoricalData()
    {
        $m3Date = (new DateTime('-3 months'))->format('Y-m-d');
        $m12Date = (new DateTime('-12 months'))->format('Y-m-d');

        $query = sprintf('?q=select * from yahoo.finance.historicaldata where symbol = "%s" and startDate = "%s" and endDate = "%s"&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys&callback=',
                        $this->investment->apiId,$m12Date,$m3Date);

        $response = $this->connector->get(static::DATA_URL.$query,$this->getCurlOptions());
        $this->parseHistoricalJson(json_decode($response->getBody()));
    }

    protected function parseHistoricalJson($data)
    {
        $results = $data->query->results->quote;

        $m3Data = array_shift($results);
        $m12Data = array_pop($results);

        $this->liveData->m12 = $this->percentageValue($this->liveData->sell_price, $m12Data->Close);
        $this->liveData->m3 = $this->percentageValue($this->liveData->sell_price, $m3Data->Close);

        $key = round(count($results) /2);
        $this->liveData->m6 = $this->percentageValue($this->liveData->sell_price, $results[$key]->Close);

    }
}