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

    private $updatesCount = 0;
    // Data source https://data.rada.gov.ua/open/data/plenary_vote_results-skl9
    // Data structure https://data.rada.gov.ua/ogd/zal/ppz/stru/chron-stru.xsd
    private $remoteSourceDirectory = 'https://data.rada.gov.ua/ogd/zal/ppz/skl9/json';
    private $eventType = 0; //
    const UPDATE_INTERVAL = 1 * 60 * 60; // seconds

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $cronJob = CronJob::find()
            ->where([
                CronJob::tableName() . '.name' => 'UaLawmakingParser'
            ])
            ->one();

        if (!isset($cronJob)) {
            return;
        }

        // set 3 previous days for period for the first launch
        if ($cronJob->created_at == $cronJob->updated_at) {
            $cronJob->updated_at = time() - 2 * 24 * 60 * 60;
        } elseif ($cronJob->updated_at > (time() - self::UPDATE_INTERVAL)) {
            return;
        }

        $client = new Client();
        // temporarily parse 1 day more, because the api updates with some delay
        $currentScrapeDate = strtotime(date('Y-m-d', $cronJob->updated_at - 1 * 24 * 60 * 60));
        $today = strtotime(date('Y-m-d'));

        while ($currentScrapeDate <= $today) {
            $remoteURL = $this->remoteSourceDirectory . '/' . date('dmY', $currentScrapeDate) . '.json';

            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($remoteURL)
                ->send();

            if ($response->headers['http-code'] == 200) {
                $this->console('Parsing: ' . $remoteURL);
                if ($response->headers['content-type'] == 'application/json') {
                    $uaLawmakingVotingExists = UaLawmakingVoting::find()
                        ->where([
                            'date' => date('Y-m-d', $currentScrapeDate),
                        ])
                        ->exists();

                    $uaLawmakingVotingExists2 = UaLawmakingVoting::find()
                        ->where([
                            'date' => date('Y-m-d', $currentScrapeDate),
                        ])
                        ->andWhere([
                            'or',
                            ['parsed_at' => null],
                            ['!=', 'parsed_at', strtotime($response->headers['last-modified'])],
                        ])
                        ->exists();

                    if (!$uaLawmakingVotingExists || $uaLawmakingVotingExists2) {
                        try {
                            $json = json_decode(iconv('windows-1251', 'utf-8', $response->content), true);

                            if (empty($json)) {
                                throw new Exception('Couldn\'t convert response to json');
                            }

                            foreach ($json['question'] as $question) {
                                foreach ($question['event_question'] as $event) {
                                    if ($event['type_event'] == $this->eventType) {
                                        foreach ($event['result_event'] as $result) {
                                            if ($result['id_event']) {
                                                $uaLawmakingVoting = UaLawmakingVoting::find()
                                                    ->where([
                                                        'event_id' => $result['id_event'],
                                                    ])
                                                    ->one();

                                                if (!$uaLawmakingVoting) {
                                                    $uaLawmakingVoting = new UaLawmakingVoting();
                                                    $uaLawmakingVoting->event_id = (int)$result['id_event'];
                                                }

                                                if ($uaLawmakingVoting->parsed_at != strtotime($response->headers['last-modified'])) {
                                                    $uaLawmakingVoting->name = $event['name_event'];
                                                    $uaLawmakingVoting->against = (int)$result['against'];
                                                    $uaLawmakingVoting->for = $result['for']?$result['for']:((int)$result['presence'] - $uaLawmakingVoting->against);
                                                    $uaLawmakingVoting->abstain = (int)$result['abstain'];
                                                    $uaLawmakingVoting->presence = (int)$result['presence'];
                                                    $uaLawmakingVoting->absent = (int)$result['absent'];
                                                    $uaLawmakingVoting->not_voting = (int)$result['not_voting'];
                                                    $uaLawmakingVoting->total = (int)$result['total'];
                                                    $uaLawmakingVoting->date = date('Y-m-d', strtotime($event['date_event']));
                                                    $uaLawmakingVoting->parsed_at = strtotime($response->headers['last-modified']);

                                                    if (!$uaLawmakingVoting->save()) {
                                                        $this->console('ERROR: Couldn\'t save vote event: ' . $uaLawmakingVoting->event_id);
                                                    }

                                                    $this->updatesCount++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // delete all events that are not in the response for this date
                            UaLawmakingVoting::deleteAll([
                                'and',
                                [
                                    'date' => date('Y-m-d', $currentScrapeDate)
                                ],
                                [
                                    'or',
                                    ['parsed_at' => null],
                                    ['!=', 'parsed_at', strtotime($response->headers['last-modified'])],
                                ],
                            ]);
                        } catch (Exception $e) {
                            $this->console('ERROR: processing result from ' . $remoteURL . ': ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->console('ERROR: response is not json: ' . $remoteURL);
                }
            } else {
                $this->console('Data source not found: ' . $remoteURL);
            }

            $currentScrapeDate += 1 * 24 * 60 * 60;
        }

        if ($this->updatesCount) {
            $this->output('Votings parsed: ' . $this->updatesCount);
        }
    }
}
