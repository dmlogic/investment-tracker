<?php

namespace App\Providers;

use Dmlogic\DataItem;
use App\Providers\Provider;
use Symfony\Component\DomCrawler\Crawler;

class HargreavesLandsdown extends Provider {

    /**
     * Where to get historical change data from
     */
    const DATA_URL = 'http://webfund6.financialexpress.net/clients/Hargreaves/Webservices/Charting.asmx';

    /**
     * Give them something plausible
     */
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0';

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
        $pastData = $this->getXmlData();

        $output = new DataItem($lastTrade);
        $output->type       = $this->investment['type'];
        $output->name       = $this->investment['name'];
        $output->cost       = $this->investment['cost'];
        $output->currency   = $this->investment['lookup_currency'];
        $output->units_held = $this->investment['units_held'];

        if($pastData) {
            $output->set('m3', $pastData['P3']);
            $output->set('m6', $pastData['P6']);
            $output->set('m12', $pastData['P12']);
        }

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
        $change = $crawler->filter('.change-percent')->first();
        if(0 === strpos($change->attr('class'), 'positive') ) {
            $changeDirection = 'up';
        } elseif(0 === strpos($change->attr('class'), 'nochange') ) {
            $changeDirection = '';
        } else {
            $changeDirection = 'up';
        }
        return [
            'sellPrice'     => trim($crawler->filter('span.bid')->first()->text(),'$£p'),
            'lastPrice'     => trim($crawler->filter('span.ask')->first()->text(),'$£p'),
            'lastChange'    => trim($change->text(),"\r\n ()%"),
            'lastDirection' => $changeDirection
        ];
    }

    /**
     * Generate a request to financialexpress and parse the result
     *
     * @return array
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
        return (array) $raw->PerformanceResult->PerfItems->PerfItem;
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
               '</soap:Envelope>',$this->investment['xml_code']);
    }
}