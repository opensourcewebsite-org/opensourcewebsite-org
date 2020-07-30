<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\httpclient\Client;

class WikinewsParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->parse();
    }

    protected function parse()
    {
        $updatesCount = 0;

        $pages = WikinewsPage::findAll([
            'parsed_at' => null,
        ]);

        /** @var object $page */
        foreach ($pages as $page) {
            $group_id = !empty($page->group_id) ? $page->group_id : $page->id;
            $identity = !$page->pageid ? urldecode($page->title) : $page->pageid;
            $data = $this->api($page->language->code, $identity);
            $page->title = str_replace('_', ' ', $page->title);
            if ($data) {
                foreach ($data['langlinks'] as $check) {
                    $exist = WikinewsPage::findOne(['title' => $check['*']]);
                    if (empty($exist)) {
                        $exist = $this->api($check['lang'], $check['*']);
                        $exist = WikinewsPage::findOne(['pageid' => $exist['pageid']]);
                    }
                    if ($exist) {
                        $group_id = $exist->group_id;
                        break;
                    }
                }
                $page->group_id = $group_id;
                $page->pageid = $data['pageid'];
                $page->parsed_at = time();
                $page->save();
                foreach ($data['langlinks'] as $langlink) {
                    $dataLink = $this->api($langlink['lang'], $langlink['*']);
                    $pageTranslate = WikinewsPage::findOne(['pageid' => $dataLink['pageid']]);
                    $this->output("Parsing by language link: {$dataLink['title']}");
                    $pageAnotherLang = !empty($pageTranslate) ? $pageTranslate : new WikinewsPage();
                    $pageAnotherLang->language_id = WikinewsLanguage::findOne(['code' => $langlink['lang']])->id;
                    $pageAnotherLang->title = $dataLink['title'];
                    $pageAnotherLang->group_id = $group_id;
                    $pageAnotherLang->pageid = $dataLink['pageid'];
                    $pageAnotherLang->parsed_at = time();
                    $pageAnotherLang->save();
                }

                $updatesCount++;
            } else {
                echo 'ERROR: page is not exist: ' . $page->title;
                $page->parsed_at = time();
                $page->save();
            }
        }

        if ($updatesCount) {
            $this->output('Pages parsed: ' . $updatesCount);
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
