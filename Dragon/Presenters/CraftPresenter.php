<?php

namespace Dragon\Presenters;

use Craft\EntryModel;

class CraftPresenter {

    private $investment;
    private $totalCost = null;

    public function __construct(EntryModel $investment) {
        $this->investment = $investment;
    }

    public function totalCost()
    {

        if(null !== $this->totalCost) {
            return $this->totalCost;
        }
        $transactions = $this->investment->transactions->find();
        $value = 0;
        foreach($transactions as $transaction) {
            $amount = (float) $transaction->amount;
            switch($transaction->transactionType->__toString()) {
                case 'buy':
                    $this->totalCost += $amount;
                    break;
                case 'sell':
                    $this->totalCost -= $amount;
            }
        }

        return $this->totalCost;
    }

    public function __get($key)
    {
        return $this->investment->__get($key);
    }

}