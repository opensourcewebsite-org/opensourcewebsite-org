<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\UaLawmakingVoting;
use app\models\CronJob;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Class UaLawmakingParserController
 *
 * @package app\commands
 */
class UaLawmakingParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    private $updatesCount;
    // Data source https://data.rada.gov.ua/open/data/plenary_vote_results-skl9
    // Data structure https://data.rada.gov.ua/ogd/zal/ppz/stru/chron-stru.xsd
    private $remoteSourceDirectory = 'https://data.rada.gov.ua/ogd/zal/ppz/skl9/json';
    private $eventType = 0; //
    private $delimiter = "\n";
    const UPDATE_INTERVAL = 60 * 60; // seconds

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $this->updatesCount = 0;

        $cronJob = CronJob::find()
            ->where([
                CronJob::tableName() . '.name' => 'UaLawmakingParser'
            ])
            ->one();

        if (!isset($cronJob) || ($cronJob->updated_at > (time() - self::UPDATE_INTERVAL))) {
            return;
        }

        $client = new Client();

        if (isset($cronJob)) {
            $startScrapeDate = $cronJob->updated_at;
        } else {
            $startScrapeDate = $this->getLatestDateFromDB();
            if (!$startScrapeDate) {
                $startScrapeDate = date('Y-m-d');
            }
            $startScrapeDate = strtotime($startScrapeDate);
        }
        $currentScrapeDate = strtotime(date('Y-m-d', $startScrapeDate));
        $today = strtotime(date('Y-m-d'));

        while ($currentScrapeDate <= $today) {
            $remoteURL = $this->remoteSourceDirectory . '/' . date('dmY', $currentScrapeDate) . '.json';
            echo "Remote url: $remoteURL\n";
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($remoteURL)
                ->send();
            if ($response->headers['http-code'] != 200) {
                echo 'Api source not found: ' . $remoteURL . $this->delimiter;
            } elseif ($response->headers['content-type'] != 'application/json') {
                echo 'Response is not json: ' . $remoteURL . $this->delimiter;
            } elseif ($startScrapeDate < strtotime($response->headers['last-modified'])) {
                try {
                    $this->processEvents($response->content);
                } catch (Exception $e) {
                    echo 'ERROR: processing result from ' . $remoteURL . ': ' . $e->getMessage() . $this->delimiter;
                }
            }
            $currentScrapeDate += 24 * 3600;
        }

        if ($this->updatesCount) {
            $this->output('Votings parsed: ' . $this->updatesCount);
        }
    }

    private function getLatestDateFromDb()
    {
        return UaLawmakingVoting::find()->max('date');
    }

    private function processEvents($content)
    {
        $json = json_decode(iconv('windows-1251', 'utf-8', $content), true);
        if (empty($json)) {
            throw new Exception("Couldn't convert to json");
        }
        foreach ($json['question'] as $question) {
            foreach ($question['event_question'] as $event) {
                if ($event['type_event'] == $this->eventType) {
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

                                $this->updatesCount++;
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
                UaLawmakingVoting::tableName() . '.event_id' => $eventId,
            ])
            ->exists();

        return $result ? true : false;
    }
}
