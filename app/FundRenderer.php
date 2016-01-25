<?php

namespace App;

use App\Providers\Factory;

class FundRenderer {

    private $funds;
    private $output;
    private $view;

    public function __construct($funds,$view) {
        $this->funds = $funds;
        $this->view = $view;
        // $this->view = new Engine(__DIR__.'/../templates');
    }

    public function render()
    {
        foreach($this->funds as $group) {
            $viewData['name'] = $group['group_name'];
            $viewData['funds'] = [];
            foreach($group['funds'] as $fund) {
                $viewData['funds'][] = $this->addFund($fund);
            }
            $this->output .= $this->view->render('group',$viewData);
        }

        return $this->view->render('page',['groups' => $this->output]);
    }

    public function addFund($fund)
    {
        $provider = Factory::make($fund);
        $data = $provider->getData();
        if($data->lastDirection == 'down') {
            $data->change = '<span class="down">-'.$data->lastChange.'%</span>';
        } else {
            $data->change = '<span class="up">+'.$data->lastChange.'%</span>';
        }
        $value = $data->units_held * $data->sellPrice;
        if($data->type === 'fund') {
            $value = $value /100;
        }
        $data->value = $value;
        return $data;
    }

}