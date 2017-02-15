<?php
namespace Dragon\Brokers;

use DateTime;
use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class CellarWatch extends Broker {

    const API_ENDPOINT = 'https://www.cellar-watch.com/chart/fusionchartxmldata.do';

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

    }

    /**
     * Crawl the Fidelity website to pick out recent trade data
     *
     */
    protected function getCrawledData()
    {
        $then = new DateTime('-1 year');
        $now = new DateTime();
        $options = [
            'headers' => [
                'Host'            => 'www.cellar-watch.com',
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];

        $query = http_build_query([
                'rebase'    => 'false',
                'type'    => '1yr',
                'startTime' => $then->getTimestamp(),
                'endTime'   => $now->getTimestamp(),
                'series'   => '0|'.$this->investment->apiUrl.'|'.$this->investment->apiId,
            ]);
        // exit(static::API_ENDPOINT.'?'.$query);
        $response = $this->connector->get(static::API_ENDPOINT.'?'.$query,$options);
        $this->parseResponse($response->getBody()->__toString());
    }

    /**
     * Pick the bits we want out of the Fidelity markup
     * which is extra special
     *
     * @param  string $html
     */
    protected function parseResponse($json)
    {
        $obj = json_decode($json)[0];

        // 12 back is 3 months ago
        $m3Data = (isset($obj->data[40])) ? $obj->data[40] : null;
        // 26 back is 6 months ago
        $m6Data = (isset($obj->data[26])) ? $obj->data[26] : null;
        // first is 12 months ago
        $m12Data = array_shift($obj->data);

        // last is today (sell price)
        $today = array_pop($obj->data);
        $previous = array_pop($obj->data);

        $this->liveData->sell_price = $today->value;
        $this->liveData->last_price = $today->value;
        $this->liveData->last_change  = number_format($this->percentageValue($today->value, $previous->value) ,2);
        $changeDirection = '';
        if($today->value > $previous->value) {
            $changeDirection = 'up';
        }elseif($today->value < $previous->value) {
            $changeDirection = 'down';
        }
        $this->liveData->last_direction = $changeDirection;

        if($m12Data) {
            $this->liveData->m12 = $this->percentageValue($this->liveData->sell_price, $m12Data->value);
        }
        if($m3Data) {
            $this->liveData->m3 = $this->percentageValue($this->liveData->sell_price, $m3Data->value);
        }
        if($m6Data) {
            $this->liveData->m6 = $this->percentageValue($this->liveData->sell_price, $m6Data->value);
        }
    }

}