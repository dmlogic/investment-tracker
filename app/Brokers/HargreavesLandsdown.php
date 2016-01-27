<?php

namespace App\Brokers;

use Dmlogic\DataItem;
use App\Providers\Provider;
use Symfony\Component\DomCrawler\Crawler;

class HargreavesLandsdown extends Broker {

    /**
     * Where to get historical change data from
     */
    const DATA_URL = 'http://webfund6.financialexpress.net/clients/Hargreaves/Webservices/Charting.asmx';

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
    protected function lookupData()
    {
        $this->liveData = new DataItem;

        $this->liveData->type       = $this->investment['type'];
        $this->liveData->name       = $this->investment['name'];
        $this->liveData->cost_price = $this->investment['cost_price'];
        $this->liveData->currency   = $this->investment['currency'];
        $this->liveData->units_held = $this->investment['units_held'];

        $this->getCrawledData();
        $this->getXmlData();

    }

    /**
     * Crawl the HL website to pick out recent trade data
     *
     */
    protected function getCrawledData()
    {
        $options = [
            'headers' => [
                'Host'            => 'www.hl.co.uk',
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
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
     * @return array
     */
    protected function parseHTML($html)
    {
        $crawler = new Crawler($html);
        $change = $crawler->filter('.change-percent')->first();
        if(0 === strpos($change->attr('class'), 'positive') ) {
            $changeDirection = 'up';
        } elseif(0 === strpos($change->attr('class'), 'nochange') ) {
            $changeDirection = '';
        } else {
            $changeDirection = 'down';
        }

        $this->liveData->sell_price     = $this->plainNumber($crawler->filter('span.bid')->first()->text());
        $this->liveData->last_price     = $this->plainNumber($crawler->filter('span.ask')->first()->text());
        $this->liveData->last_change    = $this->plainNumber($change->text());
        $this->liveData->last_direction = $changeDirection;
    }

    /**
     * Generate a request to financialexpress and parse the result
     */
    protected function getXmlData()
    {
        if($this->investment['type'] !== 'fund') {
            return false;
        }

        $options = [
            'headers' => [
                'Content-Type'  => 'text/xml; charset=UTF-8',
                'Accept'        => '*/*',
                'Cache-Control' => 'max-age=0',
                'SOAPAction'    => 'http://localhost/ClientsV21/Hargreaves/Webservices/Charting.asmx/Performance',
                'DNT'           => '1',
                'User-Agent'    => static::USER_AGENT
            ],
            'body' => $this->getRequestXml()
        ];

        $response = $this->connector->post(static::DATA_URL,$options );
        $xml = $response->getBody()->__toString();
        $raw = $this->getResponseXml($xml);

        $pastData = (array) $raw->PerformanceResult->PerfItems->PerfItem;

        $this->liveData->set('m3', $pastData['P3']);
        $this->liveData->set('m6', $pastData['P6']);
        $this->liveData->set('m12', $pastData['P12']);
    }

    protected function getResponseXml($xml)
    {
        $find = ['<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>','</soap:Body></soap:Envelope>'];
        $replace = [''];
        $xml = str_replace($find,$replace,$xml);
        return new \SimpleXMLElement($xml);
    }

    /**
     * Create the SOAP payload for financialexpress
     *
     * @return string
     */
    protected function getRequestXml()
    {
        return sprintf('<?xml version="1.0" encoding="utf-16"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'.
               '<soap:Body><Performance xmlns="http://localhost/ClientsV21/Hargreaves/Webservices/Charting.asmx"><TypeCodes>%s</TypeCodes><PriceType>TR</PriceType><MethodType>1</MethodType><PageNo>1</PageNo><PageSize>0</PageSize><Filter></Filter><Sort></Sort></Performance></soap:Body>'.
               '</soap:Envelope>',$this->investment['chart_code']);
    }
}