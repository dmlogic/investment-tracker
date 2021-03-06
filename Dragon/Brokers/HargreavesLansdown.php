<?php

namespace Dragon\Brokers;

use StdClass;
use Symfony\Component\DomCrawler\Crawler;

class HargreavesLansdown extends Broker {

    /**
     * Where to get historical chart data from
     */
    const DATA_URL = 'http://webfund6.financialexpress.net/clients/Hargreaves/Webservices/Charting.asmx';

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

        if(!$this->investment->apiId) {
            return;
        }

        if(filter_var($this->investment->apiId,FILTER_VALIDATE_URL,['flags' => [FILTER_FLAG_SCHEME_REQUIRED, FILTER_FLAG_HOST_REQUIRED, FILTER_FLAG_PATH_REQUIRED, FILTER_FLAG_QUERY_REQUIRED]])) {
            $this->getHistoryFromChartJson();
        } else {
            $this->getHistoryFromScrape();
        }

    }

    protected function getHistoryFromChartJson()
    {
        $response = $this->connector->get($this->investment->apiId);
        $source = utf8_encode($response->getBody()->__toString());
        $data = json_decode($source);
        $snapshots = array_reverse($data->quotes);
        $total = count($snapshots);

        if(count($snapshots) < 13) {
            return;
        }
        $this->liveData->m3 = $this->percentageValue($this->liveData->sell_price, $snapshots[12]->close);
        if(count($snapshots) < 26) {
            return;
        }
        $this->liveData->m6 = $this->percentageValue($this->liveData->sell_price, $snapshots[25]->close);
        if(count($snapshots) < 52) {
            return;
        }
        $m12Data = array_pop($snapshots);
        $this->liveData->m12 = $this->percentageValue($this->liveData->sell_price, $m12Data->close);
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
                'Accept'          => static::ACCEPT,
                'Accept-Language' => 'en-US,en;q=0.5',
                'DNT'             =>  '1',
                'Connection'      => 'keep-alive',
            ]
        ];

        $response = $this->connector->get($this->investment->apiUrl,$options);
        $html     = $response->getBody()->__toString();
        $this->parseHTML($html);
    }

    /**
     * Pick the bits we want out of the HL markup
     * Not pretty, but neither is markup
     *
     * @param  string $html
     */
    protected function parseHTML($html)
    {
        $crawler = new Crawler($html);

        $change = $crawler->filter('.price > .row > .columns span.change')->last();
        if(0 === strpos($change->attr('class'), 'positive') ) {
            $changeDirection = 'up';
        } elseif(0 === strpos($change->attr('class'), 'down') ) {
            $changeDirection = 'down';
        } else {
            $changeDirection = '';
        }

        $this->liveData->sell_price     = $this->plainNumber($crawler->filter('span.bid')->first()->text());
        $this->liveData->last_price     = $this->plainNumber($crawler->filter('span.ask')->first()->text());
        $this->liveData->last_change    = $this->plainNumber($change->text());
        $this->liveData->last_direction = $changeDirection;
    }

    /**
     * Generate a request to financialexpress and parse the result
     */
    protected function getHistoryFromScrape()
    {

        if($this->investment->investmentType->value !== 'fund') {
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
        $this->liveData->m3 =  $pastData['P3'];
        $this->liveData->m6 =  $pastData['P6'];
        $this->liveData->m12 =  $pastData['P12'];
    }

    /**
     * Clean some SOAP out of the returned XML and then parse it
     *
     * @param  string $xml
     * @return SimpleXMLElement
     */
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
               '</soap:Envelope>',$this->investment->apiId);
    }

    protected function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}