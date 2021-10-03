<?php

namespace app\controllers;

use app\models\Setting;
use app\models\SettingValue;
use app\models\SettingValueVote;
use app\models\search\SettingSearch;
use app\models\search\SettingValueSearch;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SettingController extends Controller
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
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SettingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int|string $key
     *
     * @return string
     */
    public function actionView($key = null)
    {
        if (!$key) {
            return $this->redirect(['index']);
        }

        $setting = Setting::find()
            ->where([
                'id' => $key,
            ])
            ->orWhere([
                'key' => $key,
            ])
            ->one();

        if (!$setting) {
            $setting = new Setting();

            $setting->setAttributes([
                    'key' => $key,
                ]);

            if (!$setting->validate()) {
                throw new NotFoundHttpException();
            }
        }

        $searchModel = new SettingValueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'setting' => $setting,
        ]);
    }

    /**
     * Add new value for a setting
     * @param int|string $setting_key
     *
     * @return string|void
     */
    public function actionAddValue($setting_key)
    {
        if (Yii::$app->request->isPost) {
            $setting_key = Yii::$app->request->post('setting_key');
            $value = Yii::$app->request->post('SettingValue')['value'];

            $setting = Setting::find()
                ->where([
                    'key' => $setting_key,
                ])
                ->one();

            if ($setting) {
                $settingValue = SettingValue::find()
                    ->where([
                        'setting_id' => $setting->id,
                        'value' => $value,
                    ])
                    ->one();
            }

            if (empty($settingValue)) {
                if (empty($setting)) {
                    $setting = new Setting();

                    $setting->setAttributes([
                        'key' => $setting_key,
                        'updated_at' => time(),
                    ]);

                    if (!$setting->validate()) {
                        throw new NotFoundHttpException();
                    }

                    $setting->save();
                }

                $settingValue = new SettingValue();

                $settingValue->setAttributes([
                    'setting_id' => $setting->id,
                    'value' => $value,
                ]);

                if (!$settingValue->save()) {
                    Yii::$app->response->format = Response::FORMAT_JSON;

                    return ActiveForm::validate($settingValue);
                }
            }

            if ($settingValue->id) {
                // Switch user vote to added value
                $settingValue->setVoteByUserId($this->user->id);

                return $this->redirect([
                    'setting/view',
                    'key' => $setting_key,
                ]);
            }
        }

        $settingValue = new SettingValue();

        $renderParams = [
            'setting_key' => $setting_key,
            'settingValue' => $settingValue,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/add-value', $renderParams);
        } else {
            return $this->render('modals/add-value', $renderParams);
        }
    }

    /**
     * Vote for a setting value
     *
     * @return string
     */
    public function actionVote()
    {
        if (Yii::$app->request->isPost) {
            if ($setting_value_id = Yii::$app->request->post('setting_value_id')) {
                $settingValue = SettingValue::findOne($setting_value_id);

                if ($settingValue) {
                    //Switch user vote to selected value
                    $settingValue->setVoteByUserId($this->user->id);

                    return $this->redirect([
                        'setting/view',
                        'key' => $settingValue->setting->getKey(),
                    ]);
                }
            }
        }

        return $this->redirect(['index']);
    }
}
