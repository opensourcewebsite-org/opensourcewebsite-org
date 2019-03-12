<?php

namespace app\commands;

use app\components\CustomConsole;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\console\Controller;
use yii\httpclient\Client;

class WikinewsParserController extends Controller
{
    public $log = false;

    public function actionIndex()
    {
        CustomConsole::output(
            'Running wikinews parser...',
            [
                'logs' => $this->log,
                'jobName' => CustomConsole::convertName(self::class),
            ]
        );
        $this->parse();
    }

    protected function parse()
    {
        $needParse = WikinewsPage::findAll(['parsed_at' => null]);
        /** @var object $news */
        foreach ($needParse as $news) {
            if (!$news->group_id) {
                $group_id = WikinewsPage::find()
                    ->where(['is not', 'group_id', null])
                    ->orderBy(['id' => SORT_DESC])
                    ->select('group_id')
                    ->one();
                $group_id = $group_id['group_id'];
                $group_id = $group_id ? $group_id + 1 : 1;
            } else {
                $group_id = $news->group_id;
            }
            $identity = !$news->pageid ? $news->title : $news->pageid;
            $data = $this->api($news->language->code, $identity);
            if ($data) {
                CustomConsole::output(
                    "Parsing page: {$news->title}",
                    [
                        'logs' => $this->log,
                        'jobName' => CustomConsole::convertName(self::class),
                    ]
                );
                foreach ($data['langlinks'] as $check) {
                    $exist = WikinewsPage::findOne(['title' => $check['*']]);
                    if ($exist) {
                        $group_id = $exist->group_id;
                        break;
                    }
                }
                foreach ($data['langlinks'] as $key => $langlink) {
                    $newsTranslate = WikinewsPage::find()
                        ->where(['group_id' => $group_id])
                        ->andWhere(['<>', 'pageid', $news->pageid])
                        ->all();
                    $identity = $newsTranslate[$key]->pageid ? $newsTranslate[$key]->pageid : $langlink['*'];
                    $dataLink = $this->api($langlink['lang'], $identity);
                    CustomConsole::output(
                        "Parsing by language link: {$dataLink['title']}",
                        [
                            'logs' => $this->log,
                            'jobName' => CustomConsole::convertName(self::class),
                        ]
                    );
                    $newsAnotherLang = $newsTranslate[$key] ? $newsTranslate[$key] : new WikinewsPage();
                    $newsAnotherLang->language_id = WikinewsLanguage::findOne(['code' => $langlink['lang']])->id;
                    $newsAnotherLang->title = $dataLink['title'];
                    $newsAnotherLang->group_id = $group_id;
                    $newsAnotherLang->pageid = $dataLink['pageid'];
                    $newsAnotherLang->parsed_at = time();
                    $newsAnotherLang->save();
                }
                $news->group_id = $group_id;
                $news->pageid = $data['pageid'];
                $news->parsed_at = time();
                $news->save();
            } else {
                CustomConsole::output(
                    "Page is not exist: {$news->title}",
                    [
                        'logs' => $this->log,
                        'jobName' => CustomConsole::convertName(self::class),
                    ]
                );
                $news->parsed_at = time();
                $news->save();
            }
        }
        if ($needParse) {
            CustomConsole::output(
                "Parsing is done.",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class),
                ]
            );
        } else {
            CustomConsole::output(
                "No page for parsing.",
                [
                    'logs' => $this->log,
                    'jobName' => CustomConsole::convertName(self::class),
                ]
            );
        }
    }

    protected function api($language, $identity)
    {
        $searchMethod = is_string($identity) ? 'page' : 'pageid';
        $baseUrl = 'https://' . $language . '.wikinews.org';
        $client = new Client([
            'baseUrl' => $baseUrl . '/w',
        ]);
        $response = $client->get('api.php', [
            'action' => 'parse',
            'format' => 'json',
            'prop' => 'langlinks',
            $searchMethod => $identity,
        ])->send();

        return $response->data['parse'];
    }
}