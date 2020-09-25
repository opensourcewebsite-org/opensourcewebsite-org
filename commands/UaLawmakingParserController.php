<?php

namespace app\commands;

use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\models\UaLawmakingVoting;
use app\models\CronJob;
use yii\base\Exception;
use yii\httpclient\Client;

class UaLawmakingParserController extends Controller implements CronChainedInterface
{
    private $sourceURL = 'https://data.rada.gov.ua/ogd/zal/ppz/skl9/chron-json.zip';
    private $delimiter = "\n";
    const UPDATE_INTERVAL = 60 * 60; // seconds

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $cronJobs = CronJob::find()
            ->where([
                CronJob::tableName() . '.name' => 'UaLawmakingParser'
            ])->orderBy('updated_at DESC')->one();

        $flag = !$cronJobs || $cronJobs->updated_at < time() - self::UPDATE_INTERVAL;
        if (!$flag) {
            return;
        }

        $maxDateEvent = $this->getLatestDateFromDB();
        if (!$maxDateEvent) {
            $maxDateEvent = date('Y-m-d');
        }
        $maxDateEvent = strtotime($maxDateEvent);
        $client = new Client([
            'baseUrl' => $this->sourceURL,
        ]);
        $response = $client->createRequest()->send();
        if ($response->headers['http-code'] != 200) {
            echo 'Api source not found: ' . $this->sourceURL;
            return;
        }
        $tempDir = \Yii::$app->runtimePath;
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $zipTempPath = tempnam($tempDir, 'temp');
        if (file_exists($zipTempPath)) {
            unlink($zipTempPath);
        }
        file_put_contents($zipTempPath, $response->content);
        $zip = new \ZipArchive;
        if ($zip->open($zipTempPath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $eventsDate = substr($fileName, 4, 4) . '-' . substr($fileName, 2, 2) . '-' . substr($fileName, 0, 2);
                if (strtotime($eventsDate) >= $maxDateEvent) {
                    echo 'Scraping file: ' . $fileName . $this->delimiter;
                    $zip->extractTo($tempDir, $fileName);
                    $dataFPath = $tempDir . '/' . $fileName;
                    if (file_exists($dataFPath)) {
                        try {
                            $this->processEventsFile($dataFPath);
                        } catch (Exception $e) {
                            echo 'ERROR: processing result from ' . $fileName . ': ' . $e->getMessage() . $this->delimiter;
                        }
                        unlink($dataFPath);
                    } else {
                        echo "Couldn't fetch file: " . $dataFPath . $this->delimiter;
                    }
                }
            }
            $zip->close();
        } else {
            echo "Couldn't extract zip";
            unlink($zipTempPath);
            return;
        }
        unlink($zipTempPath);
    }

    private function getLatestDateFromDb()
    {
        return UaLawmakingVoting::find()->max('date');
    }

    private function processEventsFile($fPath)
    {
        $json = json_decode(iconv('windows-1251', 'utf-8', file_get_contents($fPath)), true);
        if (empty($json)) {
            throw new Exception("Couldn't convert to json");
        }
        foreach ($json['question'] as $question) {
            foreach ($question['event_question'] as $event) {
                foreach ($event['result_event'] as $result) {
                    if ($result['id_event']) {
                        if (!$this->isEventExists($result['id_event'])) {
                            $uaLawmakingVoting = new UaLawmakingVoting();
                            $uaLawmakingVoting->event_id = (int)$result['id_event'];
                            $uaLawmakingVoting->name = $event['name_event'];
                            $uaLawmakingVoting->against = (int)$result['against'];
                            $uaLawmakingVoting->for = $result['for']?$result['for']:((int)$result['presence'] - $uaLawmakingVoting->against);
                            $uaLawmakingVoting->abstain = (int)$result['abstain'];
                            $uaLawmakingVoting->presence = (int)$result['presence'];
                            $uaLawmakingVoting->absent = (int)$result['absent'];
                            $uaLawmakingVoting->not_voting = (int)$result['not_voting'];
                            $uaLawmakingVoting->total = (int)$result['total'];
                            $uaLawmakingVoting->date = date('Y-m-d', strtotime($event['date_event']));
                            if (!$uaLawmakingVoting->save()) {
                                echo "Couldn't save vote event: " . $uaLawmakingVoting->event_id . $this->delimiter;
                            }
                        }
                    }
                }
            }
        }
    }

    private function isEventExists($eventId)
    {
        $result = UaLawmakingVoting::find()
            ->where([
                UaLawmakingVoting::tableName() . '.event_id' => $eventId
            ])->one();
        return $result?true:false;
    }
}
