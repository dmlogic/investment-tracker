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
        $this->displayData();
    }

    protected function displayData()
    {
        // header('Content-Type: application/json');
        echo json_encode($this->broker->getData());
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
            default:
                throw new HttpException(500, 'Invalid broker');
        }
        $this->broker = new $class($this->fund);
    }

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