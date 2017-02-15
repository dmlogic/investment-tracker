<?php namespace Craft;

/**
* dragon/import/process
*/
class Dragon_ImportController extends BaseController
{
    protected $allowAnonymous = true;

    private $idMap = [];

    public function actionGroupassign()
    {
        $sipp = [11,12,13,14,15,16,17];
        $isa = [18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,45,46,47,48];

        foreach ($sipp as $entryId) {
            $entry = craft()->entries->getEntryById($entryId);
            $entry->setContentFromPost(array(
                'investmentGroup' => [5],
            ));
            $success = craft()->entries->saveEntry($entry);
        }
        foreach ($isa as $entryId) {
            $entry = craft()->entries->getEntryById($entryId);
            $entry->setContentFromPost(array(
                'investmentGroup' => [6],
            ));
            $success = craft()->entries->saveEntry($entry);
        }
    }

    public function actionProcess()
    {
        dd("no");
        // $block = new MatrixBlockModel();
        // $block->fieldId = 14;
        // $block->ownerId = 11;
        // $block->typeId = 1;

        // $block->setContentFromPost(array(
        //     'date' => '2014-04-15',
        //     'type' => 'buy',
        //     'units' => '1',
        //     'amount' => '346.41',
        // ));

        // $success = craft()->matrix->saveBlock($block);
        $this->readInIds();
        $this->processTransactions();
        exit("done");
        // dd($success);
    }

    protected function process($data)
    {
        $newId = $this->idMap[$data[0]];

        $block = new MatrixBlockModel;
        $block->fieldId = 14;
        $block->ownerId = $newId;
        $block->typeId = 1;

        $insertData = [
            'date' => $data[1],
            'type' => $data[2],
            'units' => $data[3],
            'amount' => $data[4],
        ];

        echo PHP_EOL.'Add data for '.$newId;
        print_r($insertData);

        $block->setContentFromPost($insertData);
        craft()->matrix->saveBlock($block);
    }

    protected function processTransactions()
    {
        if (($handle = fopen(__DIR__.'/../csv/craft3_transactions.csv', "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $this->process($data);
            }
            fclose($handle);
        }
    }

    protected function readInIds()
    {
        if (($handle = fopen(__DIR__.'/../csv/funds.csv', "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $this->idMap[$data[1]] = $data[0];
            }
            fclose($handle);
        }
    }
}