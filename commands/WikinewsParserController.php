<?php
namespace app\controllers;

use app\models\WikiNews;
use yii\console\Controller;
use yii\httpclient\Client;

class WikinewsParserController extends Controller
{
    public function actionIndex()
    {
        $needParse = WikiNews::findAll(['pageid' => null]);
        /** @var object $news */
        foreach ($needParse as $news){
            $group_id  = WikiNews::find()
                ->where(['is not', 'group_id', null])
                ->orderBy(['id' => SORT_DESC])
                ->select('group_id')
                ->one();
            $group_id = $group_id['group_id'];
            $group_id = $group_id ? $group_id+1 : 1;

            $data = $this->api($news->lang, $news->title);
            foreach ($data['langlinks'] as $check){
                $exist = WikiNews::findOne(['title' => $check['*']]);
                if($exist){
                    $group_id = $exist->group_id;
                    break;
                }
            }
            /** @var object $langlink */
            foreach($data['langlinks'] as $langlink){
                $dataLink = $this->api($langlink['lang'], $langlink['*']);

                $exist = WikiNews::findOne(['title' => $langlink['*']]);
                if(!$exist){
                    /** @var object $newsAnotherLang */
                    $newsAnotherLang = new WikiNews();
                    $newsAnotherLang->lang = $langlink['lang'];
                    $newsAnotherLang->title = $dataLink['title'];
                    $newsAnotherLang->group_id = $group_id;
                    $newsAnotherLang->pageid = $dataLink['pageid'];
                    $newsAnotherLang->save();
                }
            }
            $news->group_id = $group_id;
            $news->pageid = $data['pageid'];
            $news->save();
        }
    }

    protected function api($lang, $title)
    {
        $baseUrl = 'https://'.$lang.'.wikinews.org';
        $client = new Client([
            'baseUrl' => $baseUrl.'/w',
        ]);
        $response = $client->get('api.php', [
            'action'    => 'parse',
            'format'    => 'json',
            'page'      => $title,
        ])->send();

        return $response->data['parse'];
    }
}
