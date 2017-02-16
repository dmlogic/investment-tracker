<?php
namespace Dragon\Brokers;

use DateTime;
use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class CorneyBarrow extends Broker {

    const API_ENDPOINT = 'https://reserves.corneyandbarrow.com/api/wine_price_data.aspx';

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
        $options = [
            'headers' => [
                'Host'            => 'reserves.corneyandbarrow.com',
                'User-Agent'      => static::USER_AGENT,
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];

        $then = new DateTime('-1 year');
        $now = new DateTime();

        $query = http_build_query([
                'wineId'          => $this->investment->apiUrl,
                'vintage'         => $this->investment->apiId,
                'showComparisons' => 'N',
                'showPriceRange'  => 'N',
                'dateRange'       => 12,
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
        $prices = array_reverse( json_decode($json)->Prices );
        // foreach($prices as $key => $price) {
        //     $dt = new DateTime;
        //     $dt->setTimestamp(substr($price[0],0,10));
        //     dc($key.': '.$dt->format('Y-m-d').' : '.$this->myPrice($price[1]));
        // }

        $valueNow = $this->myPrice($prices[1][1]);

        $this->liveData->sell_price = $prices[1][1];
        $this->liveData->last_price = $valueNow;
        $this->liveData->last_change  = number_format($this->percentageValue($valueNow, $this->myPrice($prices[2][1])) ,2);

        $changeDirection = '';
        if($prices[1][1] > $prices[2][1]) {
            $changeDirection = 'up';
        }elseif($prices[1][1] < $prices[2][1]) {
            $changeDirection = 'down';
        }
        $this->liveData->last_direction = $changeDirection;

        $m3Data  = (isset($prices[4]))  ? $this->myPrice($prices[4][1])  : null;
        $m6Data  = (isset($prices[7]))  ? $this->myPrice($prices[7][1])  : null;
        $m12Data = (isset($prices[13])) ? $this->myPrice($prices[13][1]) : null;
        if($m12Data) {
            $this->liveData->m12 = $this->percentageValue($valueNow, $m12Data);
        }
        if($m3Data) {
            $this->liveData->m3 = $this->percentageValue($valueNow, $m3Data);
        }
        if($m6Data) {
            $this->liveData->m6 = $this->percentageValue($valueNow, $m6Data);
        }
    }

    protected function myPrice($price)
    {
        return (float) $price / $this->investment->priceUnits;
    }

}