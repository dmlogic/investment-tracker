<?php

namespace App;

use App\Providers\Factory;

class FundData {

    private $funds;
    private $output;
    private $view;

    public function __construct($funds) {
        $this->funds = $funds;
    }

    public function get($groupId,$fundId)
    {
        $fund = $this->loadFund($groupId,$fundId);
        return $this->parseFund($fund);
    }

    private function loadFund($groupId,$fundId)
    {
        if(!array_key_exists($groupId, $this->funds)) {
            throw new Exception("Invalid group");
        }
        if(!array_key_exists($fundId, $this->funds[$groupId]['funds'])) {
        }

        return $this->funds[$groupId]['funds'][$fundId];
    }


    public function parseFund($fund)
    {
        $provider = Factory::make($fund);
        $data = $provider->getData();
        $value = $this->getValue($data);
        if($data->type === 'fund') {
            $value = $value /100;
        }
        if($data->lastDirection == 'down') {
            $data->lastChange = '-'.$data->lastChange;
        }
        $data->value = $value;
        $data->profit = $data->value - $data->cost;
        return $data->getAll();
    }

    private function getValue($data)
    {
        $value = $data->units_held * $data->sellPrice;
        if($data->currency == 'GBP') {
            return $value;
        }

        $convertor = new CurrencyConverter($data->currency,'GBP');
        return $convertor->convert($value);
    }

}