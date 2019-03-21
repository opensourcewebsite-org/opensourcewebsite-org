<?php

namespace app\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use app\models\WikinewsPage;
use app\models\WikinewsLanguage;
use yii\filters\AccessControl;
use yii\web\Controller;

class WikinewsPagesController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
	
    /**
     * Show all wikinews 
     * @return string
     */
	 
    public function actionIndex()
    {    
		$wikiNews 		= WikinewsPage::getWikiNews();
		$wikiNewsCount  = WikinewsPage::getWikiNewsCount();
		$pagination 	= new Pagination(['totalCount' => $wikiNewsCount]);
		$dataProvider = new ArrayDataProvider([
            'allModels' => $wikiNews,
			'pagination' => [
				'pageSize' => 5,
			],
 
        ]);
        return $this->render('index', [
			'dataProvider'  => $dataProvider,
            'pages' 		=> $pagination,			
		]);
    }
	
    /**
     * Add new wikinews 
     * @return string
     */
	 	
    public function actionAddLink()
    {
		$title = Yii::$app->request->post("title");
		$notValidUrl = "$title is not a valid URL";
		if (filter_var($title, FILTER_VALIDATE_URL)) {
			list ($https,$link) = preg_split('/:\/\//', $title );
			if (!preg_match("/:/i", $link)){
				$allLangs = WikinewsLanguage::getAllLangs();
				$linkParts = preg_split('/\//', $link);
				list($lang,$wikinews,$ext) = preg_split('/\./', $linkParts[0]);
				if ($wikinews == 'wikinews' && $ext == 'org' && array_key_exists($lang,$allLangs) && $linkParts[1] == 'wiki'){
					$user_id = Yii::$app->user->identity->id;
					$created_at = time();
					$model = new WikinewsPage;
					$model->language_id = $allLangs[$lang];
					$model->title 		= $title;
					$model->created_by 	= $user_id;
					$model->created_at 	= $created_at;
					if ($id = WikinewsPage::find()->select('id')->where([ 'language_id' => $allLangs[$lang]])->andWhere(['title' => $title])->andWhere(['created_by' => $user_id])->scalar()){
						WikinewsPage::updateAll(['parsed_at' => NULL], ['=', 'id', $id]);
						return "$title is valid URL but already exist in DB";
					} else {
						if($model->save(false)){
							return "$title is valid URL and being added to DB";
						}else {
							return "$title is valid URL but NOT being added to DB";
						}
					}
				} else {
					return $notValidUrl;
				}
			} else {
				return $notValidUrl;
			}
		} else {
			return $notValidUrl;
		}		
    }	
  
}
