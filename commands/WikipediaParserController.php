<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\components\WikipediaParser;
use app\models\UserWikiToken;
use app\models\WikiLanguage;
use app\models\WikiPage;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

/**
 * Class WikipediaParserController
 *
 * @package app\commands
 */
class WikipediaParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public const UPDATE_INTERVAL = 24 * 60 * 60; // seconds

    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    public function actionIndex()
    {
        $this->parseWatchlists();
        $this->parsePages();
    }

    protected function parsePages()
    {
        $updatesCount = 0;

        $baseUrl = 'https://wikidata.org/w';
        $client = new Client([
            'baseUrl' => $baseUrl,
        ]);
        $languageCodes = WikiLanguage::find()
            ->select('id')
            ->indexBy('code')
            ->column();
        $baseQuery = WikiPage::find()->select('id');

        $pages = WikiPage::find()
            ->with('language')
            ->joinWith('users')
            ->where([
                'or',
                ['wiki_page.updated_at' => null],
                ['<', 'wiki_page.updated_at', time() - self::UPDATE_INTERVAL],
            ])
            ->andWhere(['is not', 'user.id', null])
            ->all();

        foreach ($pages as $page) {
            try {
                $response = $client->get('api.php', [
                    'action'    => 'wbgetentities',
                    'format'    => 'json',
                    'props'     => 'sitelinks',
                    'utf8'      => 1,
                    'normalize' => 1,
                    'sites'     => $page->language->code . 'wiki',
                    'titles'    => $page->title,
                ])->send();
            } catch (ErrorException $e) {
                echo 'ERROR: parsing page ' . $page->title . ': ' . $e->getMessage() . "\n";
            }

            if (isset($response)) {
                $data = $response->data;
                if (isset($data['success']) && ($data['success'] == 1) && count($data['entities'])) {
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
                        WikiPage::updateAll(
                            [
                            'group_id' => $groupId,
                            'updated_at' => $time,
                        ],
                            [
                            'id' => $pageIds,
                        ]
                        );
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
                    }
                }
            }
            $page->updateAttributes([
                'group_id' => $this->getGroupId(),
                'updated_at' => time(),
            ]);

            $updatesCount++;
        }

        if ($updatesCount) {
            $this->output('Pages parsed: ' . $updatesCount);
        }
    }

    protected function getGroupId()
    {
        return WikiPage::find()->max('group_id') + 1;
    }

    protected function parseWatchlists()
    {
        $updatesCount = 0;

        $tokens = UserWikiToken::find()
            ->andWhere([
                'or',
                ['updated_at' => null],
                ['<', 'updated_at', time() - self::UPDATE_INTERVAL],
            ])
            ->andWhere(['!=', 'status', UserWikiToken::STATUS_HAS_ERROR])
            ->all();

        foreach ($tokens as $token) {
            $updatesCount++;
            $this->updatePages($token);
        }

        if ($updatesCount) {
            $this->output('Tokens updated: ' . $updatesCount);
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

            Yii::$app->db->createCommand()->update(
                '{{%user_wiki_token}}',
                ['updated_at' => time()],
                ['id' => $token->id]
            )->execute();
        } catch (ServerErrorHttpException $e) {
            echo 'ERROR: updating token #' . $token->id . ': ServerErrorHttpException: ' . $e->getMessage() . "\n";

            Yii::$app->db->createCommand()
                ->update(
                    '{{%user_wiki_token}}',
                    [
                        'updated_at' => time(),
                        'status'     => UserWikiToken::STATUS_HAS_ERROR,
                    ],
                    [
                        'user_id' => $token->user_id,
                        'language_id' => $token->language_id
                    ]
                )
                ->execute();
        } catch (\Exception $e) {
            echo 'ERROR: updating token #' . $token->id . ': Exception: ' . $e->getMessage() . "\n";
        }
    }
}
