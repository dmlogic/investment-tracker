<?php

namespace Craft;

class Dragon_InvestmentcalcService
{
    private $entry;

    public function recalculate($entry)
    {
        $this->entry = $entry;
        $this->updateInvestmentData();
        $this->updateGroupBalances();
    }

    public function updateGroupBalances()
    {
        $group = $this->entry->investmentGroup->first();
        (new Dragon_GroupcalcService)->recalculate($group);
    }

    public function updateInvestmentData()
    {
        $unitsHeld = 0;
        $currentBalance = 0;
        foreach($this->entry->transactions as $transaction) {
            if($transaction->transactionType->value == 'buy') {
                $unitsHeld = bcadd($unitsHeld,$transaction->units,4);
                $currentBalance += (double) $transaction->amount;
            } elseif($transaction->transactionType->value == 'sell') {
                $unitsHeld = bcsub($unitsHeld,$transaction->units,4);
                $currentBalance -= (double) $transaction->amount;
            }
        }
        $entryData = [
            'balance' => $currentBalance,
            'unitsHeld' => $unitsHeld,
        ];

        if((float) $unitsHeld == 0) {
            $this->entry->enabled = false;
        }

        $this->entry->setContentFromPost($entryData);
        craft()->elements->saveElement($this->entry);
    }
}