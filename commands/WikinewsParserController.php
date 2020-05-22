<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\console\Controller;
use yii\httpclient\Client;

class WikinewsParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->output('Running wikinews parser...');
        $this->parse();
    }

    protected function parse()
    {
        $needParse = WikinewsPage::findAll(['parsed_at' => null]);
        /** @var object $news */
        foreach ($needParse as $news) {
            $group_id = !empty($news->group_id) ? $news->group_id : $news->id;
            $identity = !$news->pageid ? urldecode($news->title) : $news->pageid;
            $data = $this->api($news->language->code, $identity);
            $news->title = str_replace('_', ' ', $news->title);
            if ($data) {
                $this->output("Parsing page: {$news->title}");
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
                $news->group_id = $group_id;
                $news->pageid = $data['pageid'];
                $news->parsed_at = time();
                $news->save();
                foreach ($data['langlinks'] as $langlink) {
                    $dataLink = $this->api($langlink['lang'], $langlink['*']);
                    $newsTranslate = WikinewsPage::findOne(['pageid' => $dataLink['pageid']]);
                    $this->output("Parsing by language link: {$dataLink['title']}");
                    $newsAnotherLang = !empty($newsTranslate) ? $newsTranslate : new WikinewsPage();
                    $newsAnotherLang->language_id = WikinewsLanguage::findOne(['code' => $langlink['lang']])->id;
                    $newsAnotherLang->title = $dataLink['title'];
                    $newsAnotherLang->group_id = $group_id;
                    $newsAnotherLang->pageid = $dataLink['pageid'];
                    $newsAnotherLang->parsed_at = time();
                    $newsAnotherLang->save();
                }
            } else {
                $this->output("Page is not exist: {$news->title}");
                $news->parsed_at = time();
                $news->save();
            }
        }
        if ($needParse) {
            $this->output('Parsing is done.');
        } else {
            $this->output('No page for parsing.');
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
