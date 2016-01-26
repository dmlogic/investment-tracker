<?php

namespace App\Providers;

class Factory {

    public static function make(array $fund)
    {
        switch($fund['provider']) {
            case 'hl':
                return new HargreavesLandsdown($fund);
                break;
            case 'fid':
                return new Fidelity($fund);
                break;
            default:
                throw new \Exception('Invalid provider');
        }
    }
}