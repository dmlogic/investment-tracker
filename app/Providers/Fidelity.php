<?php

namespace App\Providers;

use Dmlogic\DataItem;
use App\Providers\Provider;
use Symfony\Component\DomCrawler\Crawler;

class Fidelity extends Provider {

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
     * @return DataItem
     */
    public function getData()
    {
        $lastTrade = $this->getCrawledData();

        $output = new DataItem($lastTrade);
        $output->type       = $this->investment['type'];
        $output->name       = $this->investment['name'];
        $output->cost       = $this->investment['cost'];
        $output->currency   = $this->investment['lookup_currency'];
        $output->units_held = $this->investment['units_held'];

        return $output;
    }

    /**
     * Crawl the HL website to pick out recent trade data
     *
     * @return array
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
        return $this->parseHTML($html);
    }

    /**
     * Pick the bits we want out of the HL markup
     *
     * @param  string $html
     * @return array
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

        $change = trim(preg_replace( '/[^0-9.]/', '', $change->text()));
        $price = $crawler->filter('.ofLikeComponent .ofMedium strong')->first()->text();
        $price = trim(preg_replace( '/[^0-9.]/', '', $price ));
        return [
            'sellPrice'     => $price,
            'lastPrice'     => $price,
            'lastChange'    => $change,
            'lastDirection' => $changeDirection
        ];
    }
}