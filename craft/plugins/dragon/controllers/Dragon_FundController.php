<?php namespace Craft;

/**
* dragon/fund/data
*/
class Dragon_FundController extends BaseController
{
    protected $allowAnonymous = true;

    private $fund;
    private $broker;

    public function actionData()
    {
        $this->loadFund();
        $this->loadBroker();
        $data = $this->broker->getData();
        $this->updateFund($data);
        $this->displayData($data);
    }

    protected function updateFund($data)
    {
        $date = new \DateTime;
        $this->fund->setContentFromPost(
            [
                'lastValue'     => round($data['value']),
                'lastValueDate' => [
                    'date' => $date->format('d/m/Y'),
                    'time' => $date->format('H:i'),
                ]
            ]
        );
        craft()->entries->saveEntry($this->fund);
    }

    protected function displayData($data)
    {
        // header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function loadBroker()
    {
        switch($this->fund->apiProvider->value) {
            case 'hargreavesLansdown':
                $class = '\Dragon\Brokers\HargreavesLansdown';
                break;
            case 'yahooFinance':
                $class = '\Dragon\Brokers\YahooFinance';;
                break;
            case 'fidelity':
                $class = '\Dragon\Brokers\Fidelity';;
                break;
            case 'morningStar':
                $class = '\Dragon\Brokers\MorningStar';
                break;
            case 'cellarWatch':
                $class = '\Dragon\Brokers\CellarWatch';
                break;
            case 'corneyBarrow':
                $class = '\Dragon\Brokers\CorneyBarrow';
                break;
            default:
                throw new HttpException(500, 'Invalid broker');
        }
        $this->broker = new $class($this->fund);
    }

    // https://dragon.darrenm.net/?action=dragon/fund/data&group_id=5&fund_id=11
    protected function loadFund()
    {
        $groupId = (int)  craft()->request->getQuery('group_id');
        $fundId = (int)  craft()->request->getQuery('fund_id');

        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->id = $fundId;
        $criteria->section = 'investments';
        $criteria->relatedTo = $groupId;

        if(!$this->fund = $criteria->first()) {
            throw new HttpException(404, 'Investment not found');
        }
    }
}