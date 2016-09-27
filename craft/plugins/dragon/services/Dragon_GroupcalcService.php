<?php

namespace Craft;

class Dragon_GroupcalcService
{
    private $group;

    public function recalculate($group)
    {
        $this->group = $group;

        $balance = 0;

        foreach($this->getInvestments() as $fund) {
            $balance += (float) $fund->balance;
        }

        $this->group->setContentFromPost(['balance' => $balance]);
        craft()->elements->saveElement($this->group);
    }

    protected function getInvestments()
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = 'investments';
        $criteria->relatedTo = $this->group->id;
        $criteria->status = null;

        return $criteria->find();
    }
}