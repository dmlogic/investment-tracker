<?php
/**

 */
namespace Dragon\Brokers;

use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class WineOwners extends Broker {

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
                'Host'            => 'www.fidelity.co.uk',
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];

        $response = $this->connector->get($this->investment->apiUrl,$options);
        $this->html = $response->getBody()->__toString();
        $this->parseHTML();
    }

    /**
     * Pick the bits we want out of the Fidelity markup
     * which is extra special
     *
     * @param  string $html
     */
    protected function parseHTML()
    {
        $crawler = new Crawler($this->html);
        $change = $crawler->filter('#daychange1')->first();
        if(false === strpos($change->attr('class'), 'Loss') ) {
            $changeDirection = 'up';
        } else {
            $changeDirection = 'down';
        }

        $change = $this->plainNumber( $change->text() );
        $price = $crawler->filter('.ofLikeComponent .ofMedium strong')->first()->text();
        $price = $this->plainNumber( $price );

        $this->liveData->sell_price     = $price;
        $this->liveData->last_price     = $price;
        $this->liveData->last_change    = $change;
        $this->liveData->last_direction = $changeDirection;

        $this->liveData->m3 =  $this->lookupCell('3 Months',$crawler);
        $this->liveData->m6 =  $this->lookupCell('6 Months',$crawler);
        $this->liveData->m12 =  $this->lookupCell('1 Year',$crawler);
    }

    protected function lookupCell($label,$crawler)
    {
        $crawler = $crawler->filter('#col2 table tbody tr');

        foreach($crawler as $row) {
            if(strpos($row->nodeValue, $label) === false) {
                continue;
            }
            return $row->childNodes[1]->nodeValue;
        }

        return null;
    }
}