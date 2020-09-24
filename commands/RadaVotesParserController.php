<?php

namespace app\commands;

use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\models\RadaVote;
use yii\base\Exception;
use yii\httpclient\Client;

class RadaVotesParserController extends Controller implements CronChainedInterface
{
    private $_sourceURL = 'https://data.rada.gov.ua/ogd/zal/ppz/skl9/chron-json.zip';
    private $_eventsStartFrom = '2020-09-01';
    private $_delimiter = "\n";

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $maxDateEvent = $this->getLatestDateFromDB();
        if (!$maxDateEvent) $maxDateEvent = $this->_eventsStartFrom;
        $maxDateEvent = strtotime($maxDateEvent);
        $client = new Client([
            'baseUrl' => $this->_sourceURL,
        ]);
        $response = $client->createRequest()->send();
        if ($response->headers['http-code'] != 200) {
            exit('Api source not found: '. $this->_sourceURL);
        }
        $tempDir = \Yii::$app->runtimePath. '/tmp';
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
        $zipTempPath = tempnam($tempDir, 'temp');
        if (file_exists($zipTempPath)) unlink($zipTempPath);
        file_put_contents($zipTempPath, $response->content);
        $zip = new \ZipArchive;
        if ($zip->open($zipTempPath) === true) {
            for($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $eventsDate = substr($fileName, 4, 4). '-'. substr($fileName, 2, 2). '-'. substr($fileName, 0, 2);
                if (strtotime($eventsDate) >= $maxDateEvent) {
                    echo 'Scraping file: '. $fileName. $this->_delimiter;
                    $zip->extractTo($tempDir, $fileName);
                    $dataFPath = $tempDir . '/' . $fileName;
                    if (file_exists($dataFPath)) {
                        try {
                            $this->processEventsFile($dataFPath);
                        }catch (Exception $e) {
                            echo 'ERROR: processing result from '. $fileName. ': '. $e->getMessage() . $this->_delimiter;
                        }
                        unlink($dataFPath);
                    }else {
                        echo "Couldn't fetch file: ". $dataFPath. $this->_delimiter;
                    }
                }
            }
            $zip->close();
        } else {
            exit("Couldn't extract zip");
        }
        unlink($zipTempPath);
    }

    function getLatestDateFromDb() {
        return RadaVote::find()->max('date_event');
    }

    function processEventsFile($fPath) {
        $json = json_decode(iconv('windows-1251', 'utf-8', file_get_contents($fPath)), true);
        if (empty($json)) {
            throw new Exception("Couldn't convert to json");
        }
        foreach ($json['question'] as $question) {
            foreach ($question['event_question'] as $event) {
                foreach ($event['result_event'] as $result) {
                    if ($result['id_event']) {
                        if (!$this->isEventExists($result['id_event'])) {
                            $radaVote = new RadaVote();
                            $radaVote->id_event = (int)$result['id_event'];
                            $radaVote->name = $event['name_event'];
                            $radaVote->against = (int)$result['against'];
                            $radaVote->for = $result['for']?$result['for']:((int)$result['presence'] - $radaVote->against);
                            $radaVote->abstain = (int)$result['abstain'];
                            $radaVote->absent = (int)$result['absent'];
                            $radaVote->not_voting = (int)$result['not_voting'];
                            $radaVote->date_event = date('Y-m-d', strtotime($event['date_event']));
                            if (!$radaVote->save()) {
                                echo "Couldn't save vote event: ". $radaVote->id_event. $this->_delimiter;
                            }
                        }
                    }
                }
            }
        }
    }

    function isEventExists($eventId) {
        $result = RadaVote::find()->where([RadaVote::tableName() . '.id_event' => $eventId])->one();
        return $result?true:false;
    }
}
