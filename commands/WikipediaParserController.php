<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\components\WikipediaParser;
use app\components\CustomConsole;
use app\interfaces\ICronChained;
use app\models\UserWikiToken;
use app\models\WikiLanguage;
use app\models\WikiPage;
use Yii;
use yii\base\ErrorException;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

/**
 *
 * @property integer $groupId
 * @property bool $log
 */
class WikipediaParserController extends Controller implements ICronChained
{
    use ControllerLogTrait;

    const UPDATE_INTERVAL = 24 * 3600;
    const PAGE_PARSE_RETRY_INTERVAL = 5;
    const PAGE_PARSE_RETRY_COUNT = 3;

    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    public function actionIndex()
    {
        CustomConsole::output(
            'Running watchlists parser...',
            [
                'logs' => $this->log,
                'jobName' => CustomConsole::convertName(self::class)
            ]
        );
        $this->processPages();
        CustomConsole::output(
            'Running languages parser...',
            [
                'logs' => $this->log,
                'jobName' => CustomConsole::convertName(self::class)
            ]
        );
        $this->parse();
    }

    protected function parse()
    {
        $baseUrl = 'https://wikidata.org/w';
        $client = new Client([
            'baseUrl' => $baseUrl,
        ]);
        $languageCodes = WikiLanguage::find()
            ->select('id')
            ->indexBy('code')
            ->column();
        $baseQuery = WikiPage::find()->select('id');
        if ($page = WikiPage::find()
            ->with('language')
            ->joinWith('users')
            ->where([
                'or',
                ['wiki_page.updated_at' => null],
                ['<', 'wiki_page.updated_at', time() - self::UPDATE_INTERVAL],
            ])
            ->andWhere(['is not', 'user.id', null])
            ->one()
        ) {
            CustomConsole::output(
                "Parsing page: {$page->title}",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );
            for ($retry = 0; $retry <= self::PAGE_PARSE_RETRY_COUNT; $retry++) {
                try {
                    $response = $client->get('api.php', [
                        'action'    => 'wbgetentities',
                        'format'    => 'json',
                        'props'     => 'sitelinks',
                        'utf8'      => 1,
                        'normalize' => 1,
                        'sites'     => "{$page->language->code}wiki",
                        'titles'    => $page->title,
                    ])->send();
                    break;
                } catch (ErrorException $e) {
                    CustomConsole::output(
                        'Error parsing page ' . $page->title . ' - ' . $e->getMessage(),
                        [
                            'logs' => $this->log,
                            'jobName' => CustomConsole::convertName(self::class)
                        ]
                    );
                    if ($retry == self::PAGE_PARSE_RETRY_COUNT) {
                        $page->updateAttributes(['group_id' => $this->getGroupId(), 'updated_at' => time()]);
                    } else {
                        sleep(self::PAGE_PARSE_RETRY_INTERVAL);
                    }
                }
            }
            if (isset($response)) {
                $data = $response->data;
                if ($data['success'] == 1 && count($data['entities'])) {
                    $result = array_shift($data['entities']);
                    if (isset($result['sitelinks'])) {
                        $query = clone $baseQuery;
                        $query->where(['id' => $page->id]);
                        $sitelinkData = [];
                        foreach ($result['sitelinks'] as $sitelink) {
                            if (substr($sitelink['site'], -4, 4) == 'wiki') {
                                $pos = strpos($sitelink['site'], 'wiki');
                                $language = substr($sitelink['site'], 0, $pos);
                                $languageId = ArrayHelper::getValue($languageCodes, $language);
                                if ($languageId && $languageId != $page->language_id) {
                                    $sitelinkData[$languageId] = $sitelink['title'];
                                    $query->orWhere([
                                        'and', ['language_id' => $languageId], ['title' => $sitelink['title']],
                                    ]);
                                }
                            }
                        }
                        $groupQuery = clone $query;
                        $groupQuery->andWhere(['is not', 'group_id', null]);
                        $groupId = $groupQuery->select('group_id')->scalar();
                        if (!$groupId) {
                            $groupId = $this->getGroupId();
                        }
                        $pageIds = $query->column();
                        $time = time();
                        WikiPage::updateAll(['group_id' => $groupId, 'updated_at' => $time], ['id' => $pageIds]);
                        $existingLanguageIds = WikiPage::find()
                            ->select('language_id')
                            ->where(['id' => $pageIds])
                            ->column();
                        $missingLanguageIds = array_diff(array_keys($sitelinkData), $existingLanguageIds);
                        $rows = [];
                        foreach ($missingLanguageIds as $missingLanguageId) {
                            $rows[] = [
                                $missingLanguageId,
                                0,
                                $sitelinkData[$missingLanguageId],
                                $groupId,
                                $time,
                            ];
                        }
                        Yii::$app->db->createCommand()->batchInsert(
                            '{{%wiki_page}}',
                            ['language_id', 'ns', 'title', 'group_id', 'updated_at'],
                            $rows
                        )->execute();
                    } else {
                        $page->updateAttributes(['group_id' => $this->getGroupId(), 'updated_at' => time()]);
                    }
                } else {
                    $page->updateAttributes(['group_id' => $this->getGroupId(), 'updated_at' => time()]);
                }
            }
        }
        if (WikiPage::find()
            ->joinWith('users')
            ->where([
                'or',
                ['wiki_page.updated_at' => null],
                ['<', 'wiki_page.updated_at', time() - self::UPDATE_INTERVAL],
            ])
            ->andWhere(['is not', 'user.id', null])
            ->exists()
        ) {
            $this->parse();
        } else {
            return true;
        }
    }

    protected function getGroupId()
    {
        return WikiPage::find()->max('group_id') + 1;
    }

    protected function processPages()
    {
        $tokens = UserWikiToken::find()
            ->andWhere([
                'or',
                ['updated_at' => null],
                ['<', 'updated_at', time() - self::UPDATE_INTERVAL],
            ])
            ->andWhere(['!=', 'status', UserWikiToken::STATUS_HAS_ERROR])
            ->all();
        $counter = count($tokens);
        CustomConsole::output(
            "Found $counter tokens to update",
            [
                'logs' => $this->log,
                'jobName' => CustomConsole::convertName(self::class)
            ]
        );

        foreach ($tokens as $token) {
            $this->updatePages($token);
        }
    }

    protected function updatePages(UserWikiToken $token)
    {
        $parser = new WikipediaParser([
            'user_id'     => $token->user_id,
            'language_id' => $token->language_id,
        ]);
        try {
            $parser->run();
            CustomConsole::output(
                "Updated token #{$token->id}",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );

            Yii::$app->db->createCommand()->update(
                '{{%user_wiki_token}}',
                ['updated_at' => time()],
                ['id' => $token->id]
            )->execute();
        } catch (ServerErrorHttpException $e) {
            CustomConsole::output(
                "Error updating token #{$token->id} ServerErrorHttpException: ",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );

            CustomConsole::output(
                $e->getMessage(),
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );

            Yii::$app->db->createCommand()->update(
                '{{%user_wiki_token}}',
                [
                    'updated_at' => time(),
                    'status'     => UserWikiToken::STATUS_HAS_ERROR,
                ],
                ['user_id' => $token->user_id, 'language_id' => $token->language_id]
            )->execute();
        } catch (\Exception $e) {
            CustomConsole::output(
                "Error updating token #{$token->id} Exception: ",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );

            CustomConsole::output(
                $e->getMessage(),
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class)
                ]
            );
        }
    }
}
