<?php

namespace Dragon\Brokers;
// http://www.morningstar.co.uk

use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class MorningStar extends Broker {

    protected function lookupData()
    {
        $this->liveData = new StdClass;

        $this->liveData->type       = $this->investment->investmentType->value;
        $this->liveData->name       = $this->investment->title;
        $this->liveData->cost_price = $this->investment->totalCost();
        $this->liveData->currency   = $this->investment->currency;
        $this->liveData->units_held = $this->investment->unitsHeld;

        $this->getLiveData();
        $this->getHistoricData();
    }

    protected function getLiveData()
    {
        $response = $this->connector->get($this->investment->apiUrl,$this->scrapeHeaders());
        $html     = $response->getBody()->__toString();

        $pattern = '#<table class="snapshotTextColor snapshotTextFontStyle snapshotTable overviewKeyStatsTable" border="0">(.+)<\/table>#s';
        if(!preg_match($pattern, $html,$priceTable)) {
            return;
        }
        $crawler = new Crawler($priceTable[0]);
        $price = $this->plainNumber( $crawler->filter('td.text')->eq(0)->text() );
        $change = $this->plainNumber(  $crawler->filter('td.text')->eq(1)->text() );
        if(false === strpos($change, '-') ) {
            $changeDirection = 'up';
        } else {
            $changeDirection = 'down';
        }

        $this->liveData->sell_price     = $price;
        $this->liveData->last_price     = $price;
        $this->liveData->last_change    = $change;
        $this->liveData->last_direction = $changeDirection;
    }

    protected function getHistoricData()
    {
        $response = $this->connector->get($this->investment->apiId,$this->scrapeHeaders());
        $html     = $response->getBody()->__toString();
        $pattern = '#<table class="snapshotTextColor snapshotTextFontStyle snapshotTable returnsTrailingTable" border="0">(.+)<\/table>#s';
        if(!preg_match($pattern, $html,$priceTable)) {
            return;
        }
        $crawler = new Crawler($priceTable[0]);

        $this->liveData->m3 =  $crawler->filter('td.col2')->eq(4)->text();
        $this->liveData->m6 =  $crawler->filter('td.col2')->eq(5)->text();
        $this->liveData->m12 =  $crawler->filter('td.col2')->eq(7)->text();
    }

    protected function scrapeHeaders()
    {
        return [
            'headers' => [
                'Host'            => 'www.morningstar.co.uk',
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];
    }
}