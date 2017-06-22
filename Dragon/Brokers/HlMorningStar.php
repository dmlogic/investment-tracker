<?php

namespace Dragon\Brokers;

use DateTime;
use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class HlMorningStar extends HargreavesLansdown {

    /**
     * Where to get historical chart data from
     */

    const DATA_URL = 'http://tools.morningstar.co.uk/api/rest.svc/timeseries_ohlcv/t92wz0sj7c?currencyId=USD&idtype=Morningstar&frequency=daily&performanceType=&outputType=COMPACTJSON';

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

        $this->getCrawledData();
        $this->getHistoricalData();

    }

    protected function getHistoricalData()
    {
        $m3Date = (new DateTime('-3 months'));
        $m6Date = (new DateTime('-6 months'));
        $m12Date = (new DateTime('-12 months'));

        $query = sprintf('&startDate=%s&&id=%s',$m12Date->format('Y-m-d'),$this->investment->apiId);

        $response = $this->connector->get(static::DATA_URL.$query,$this->getCurlOptions());
        $this->parseHistoricalMarkup(json_decode($response->getBody(),true),$m3Date->format('Y-m-d'),$m6Date->format('Y-m-d'),$m12Date->format('Y-m-d'));
    }

    protected function parseHistoricalMarkup($data,$m3Date,$m6Date,$m12Date)
    {
        foreach($data as $day) {
            $date = (new \DateTime)->setTimestamp(substr($day[0],0,10))->format('Y-m-d');
            // Close = $day[4]
            if($date === $m3Date) {
                $this->liveData->m3 = $this->percentageValue($this->liveData->sell_price, $day[4]);
                break; // oldest is last
            }
            if($date === $m6Date) {
                $this->liveData->m6 = $this->percentageValue($this->liveData->sell_price, $day[4]);
                continue;
            }
            if($date === $m12Date) {
                $this->liveData->m12 = $this->percentageValue($this->liveData->sell_price, $day[4]);
                continue;
            }
        }
    }
}