<?php

namespace app\controllers;

use app\models\WikiNews;
use DOMDocument;
use yii\console\Controller;
use yii\httpclient\Client;

class WikinewsParserController extends Controller
{
    const NEWS_PARSE_LIMIT = 10;
    const PARSE_PAGES = [
        'ca' => 'Categoria:Notícies publicades',
        'de' => 'Kategorie:Veröffentlicht',
        'el' => 'Κατηγορία:Δημοσιευμένα',
        'en' => 'Category:Published',
        'eo' => 'Kategorio:Publikigitaj artikoloj',
        'es' => 'Categoría:Artículos_publicados',
        'fa' => 'رده:منتشرشده',
        'fi' => 'Luokka:Julkaistut artikkelit',
        'fr' => 'Catégorie:Article publié',
        'he' => 'קטגוריה:פורסמו',
        'it' => 'Categoria:Pubblicati',
        'ja' => 'カテゴリ:公開中',
        'ko' => '분류:발행됨',
        'nl' => 'Categorie:Gepubliceerd',
        'pt' => 'Categoria:Publicado',
        'ro' => 'Categorie:Publicate',
        'ru' => 'Категория:Опубликовано',
        'zh' => 'Category:已发布',
    ];

    public function actionIndex()
    {
        foreach (self::PARSE_PAGES as $language => $page){
            $baseUrl = 'https://'.$language.'.wikinews.org';
            $client = new Client([
                'baseUrl' => $baseUrl.'/w',
            ]);
            $response = $client->get('api.php', [
                'action'    => 'parse',
                'format'    => 'json',
                'page'      => $page,
                'prop'   => 'text',
            ])->send();
            if($text = $response->data['parse']['text']['*']) {
                $dom = new domDocument();
                $dom->loadHTML('<?xml encoding="utf-8" ?>'.$text);
                $liAll = $dom->getElementsByTagName('li');
                /** @var object $li */
                foreach ($liAll as $key => $li) {
                    $title = null;
                    $link = null;
                    /** @var object $a */
                    $a = $li->getElementsByTagName('a')[0];
                    $title = $a->getAttribute('title');
                    $link = $baseUrl.$a->getAttribute('href');
                    if ($title && $link) {
                        $exist = WikiNews::find()->where(['link' => $link])->one();
                        if (empty($exist)) {
                            /** @var object $wikiNews */
                            $wikiNews = new WikiNews();
                            $wikiNews->title = $title;
                            $wikiNews->link = $link;
                            $wikiNews->save();
                        }
                    }
                    if ($key+1 >= self::NEWS_PARSE_LIMIT) {
                        break;
                    }
                }
            }
        }
    }
}
