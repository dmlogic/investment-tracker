<?php
require __DIR__.'/vendor/autoload.php';

use App\FundData;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use App\Providers\HargreavesLandsdown\Hl;
use Psr\Http\Message\ServerRequestInterface;

$app = new Slim\App;
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new Engine(__DIR__.'/templates');
    return $view;
};
$container['funds'] = include(__DIR__.'/config/funds.php');
$container['fundData'] = new FundData($container['funds']);

$app->get('/', function ($request, $response) {
    $response = $response->write( $this->view->render('home',['groups' => $this->funds]) );
    return $response;
});

$app->get('/fund/{group}/{fund}', function ($request, $response,$args) {
    return $response->withJson( $this->fundData->get($args['group'],$args['fund']) );
});
$app->get('/debug', function() {
    $fund = [
                'name'            => 'Fidelity Index UK Fund P-Acc',
                'type'            => 'fund',
                'cost'            => 10780,
                'units_held'      => 12529.12,
                'provider'        => 'fid',
                'url'             => 'https://www.fidelity.co.uk/fund-supermarket/Fidelity-Index-UK-Fund-P-Acc-GB00BJS8SF95',
                'lookup_currency' => 'GBP',
            ];
    $pro = new App\Providers\Fidelity($fund);
    dd($pro->getData());
});

function dd($var) {
    dc($var);
    exit;
}

function dc($var) {
    \Symfony\Component\VarDumper\VarDumper::dump($var);
}