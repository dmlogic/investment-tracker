<?php
/**
https://www.cellar-watch.com/chart/fusionchartxmldata.do?
    _=1475830397068
    &ajaxReq=1
    &series=0%7C1007475%7C1996
    &timescale=60
    &colors=0064c8
    &rebase=false
    &type=5yr
    &startTime=1317977597065
    &endTime=1475830397065

 */
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
        // $response = $this->connector->get(static::API_ENDPOINT.'?'.$query,$options);
        // $this->parseResponse($response->getBody()->__toString());


        $json = '[{"color":"#null","name":"Calon Segur, 1996","data":[{"value":695.0,"date":1455235200000},{"value":695.0,"date":1455840000000},{"value":740.0,"date":1456444800000},{"value":725.0,"date":1457049600000},{"value":720.0,"date":1457654400000},{"value":685.0,"date":1458259200000},{"value":720.0,"date":1458864000000},{"value":720.0,"date":1459465200000},{"value":720.0,"date":1460070000000},{"value":720.0,"date":1460674800000},{"value":720.0,"date":1461279600000},{"value":720.0,"date":1461884400000},{"value":720.0,"date":1462489200000},{"value":720.0,"date":1463094000000},{"value":702.0,"date":1463698800000},{"value":700.0,"date":1464303600000},{"value":700.0,"date":1464908400000},{"value":720.0,"date":1465513200000},{"value":720.0,"date":1466118000000},{"value":720.0,"date":1466722800000},{"value":720.0,"date":1467327600000},{"value":720.0,"date":1467932400000},{"value":750.0,"date":1468537200000},{"value":750.0,"date":1469142000000},{"value":750.0,"date":1469746800000},{"value":750.0,"date":1470351600000},{"value":700.0,"date":1470956400000},{"value":750.0,"date":1471561200000},{"value":750.0,"date":1472166000000},{"value":750.0,"date":1472770800000},{"value":750.0,"date":1473375600000},{"value":750.0,"date":1473980400000},{"value":750.0,"date":1474585200000},{"value":750.0,"date":1475190000000},{"value":750.0,"date":1475794800000},{"value":750.0,"date":1476399600000},{"value":750.0,"date":1477004400000},{"value":750.0,"date":1477609200000},{"value":780.0,"date":1478217600000},{"value":780.0,"date":1478822400000},{"value":800.0,"date":1479427200000},{"value":800.0,"date":1480032000000},{"value":800.0,"date":1480636800000},{"value":800.0,"date":1481241600000},{"value":776.0,"date":1481846400000},{"value":788.0,"date":1482451200000},{"value":792.0,"date":1483056000000},{"value":795.0,"date":1483660800000},{"value":795.0,"date":1484265600000},{"value":795.0,"date":1484870400000},{"value":785.0,"date":1485475200000},{"value":785.0,"date":1486080000000},{"value":786.0,"date":1486684800000}]}]';
        $this->parseResponse($json);
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
        $this->liveData->last_change  = $today->value - $previous->value;
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