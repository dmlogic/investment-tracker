<?php
require __DIR__.'/vendor/autoload.php';

use App\FundRenderer;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use App\Providers\HargreavesLandsdown\Hl;
use Psr\Http\Message\ServerRequestInterface;

$app = new \Slim\App;
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {

    $view =  new Engine(__DIR__.'/templates');
    $funds = include(__DIR__.'/config/funds.php');
    $parser = new FundRenderer($funds,$view);
    echo $parser->render();

});

$app->get('/debug', function (ServerRequestInterface $request, ResponseInterface $response) {
    $fund = new App\Providers\HargreavesLandsdown([
                'name'       => 'AXA Framlington Biotech Class Z - Accumulation (GBP)',
                'type'       => 'fund',
                'cost'       => 2000,
                'units_held' => 587.889,
                'provider'   => 'hl',
                'xml_code'   => 'F11VD',
                'url'        => 'http://www.hl.co.uk/funds/fund-discounts,-prices--and--factsheets/search-results/a/axa-framlington-biotech-class-z-accumulation/charts'
            ]);
});

function dd($var) {
    dc($var);
    exit;
}

function dc($var) {
    \Symfony\Component\VarDumper\VarDumper::dump($var);
}