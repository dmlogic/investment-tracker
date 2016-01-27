<?php

namespace App\Brokers;

use Dmlogic\DataItem;
use Symfony\Component\DomCrawler\Crawler;

class Fidelity extends Broker {

    /**
     * Data about the investment being parsed
     * @var array
     */
    protected $investment;

    public function __construct(array $investment) {
        parent::__construct();
        $this->investment = $investment;
    }

    /**
     * Public interface to get data
     *
     */
    public function lookupData()
    {
        $this->liveData = new DataItem;
        $this->liveData->type       = $this->investment['type'];
        $this->liveData->name       = $this->investment['name'];
        $this->liveData->cost_price = $this->investment['cost_price'];
        $this->liveData->currency   = $this->investment['currency'];
        $this->liveData->units_held = $this->investment['units_held'];

        $this->getCrawledData();

    }

    /**
     * Crawl the HL website to pick out recent trade data
     *
     */
    protected function getCrawledData()
    {
        $options = [
            'headers' => [
                'Host'            => 'www.fidelity.co.uk',
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];

        $response = $this->connector->get($this->investment['url'],$options);
        $html = $response->getBody()->__toString();
        $this->parseHTML($html);
    }

    /**
     * Pick the bits we want out of the HL markup
     *
     * @param  string $html
     */
    protected function parseHTML($html)
    {
        $crawler = new Crawler($html);
        $change = $crawler->filter('#daychange1')->first();
        if(false === strpos($change->attr('class'), 'Loss') ) {
            $changeDirection = 'up';
        } else {
            $changeDirection = 'down';
        }

        $change = $this->plainNumber( $change->text() );
        $price = $crawler->filter('.ofLikeComponent .ofMedium strong')->first()->text();
        $price = $this->plainNumber( $price ) * 100;

        $this->liveData->sell_price     = $price;
        $this->liveData->last_price     = $price;
        $this->liveData->last_change    = $change;
        $this->liveData->last_direction = $changeDirection;
    }
}