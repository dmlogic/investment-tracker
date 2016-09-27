<?php
namespace Craft;

class DragonPlugin extends BasePlugin
{
    public function init()
    {
        craft()->on('entries.saveEntry', function(Event $event) {

            if($event->params['entry']->section->name !== 'Investments' || $event->params['entry']->status !== 'live') {
                return;
            }
            (new Dragon_InvestmentcalcService)->recalculate($event->params['entry']);

        });
    }

    public function getName()
    {
         return 'Dragon';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'Darren';
    }

    public function getDeveloperUrl()
    {
        return 'http://dmlogic.com';
    }
}