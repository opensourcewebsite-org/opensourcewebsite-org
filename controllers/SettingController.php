<?php
namespace app\controllers;

use app\models\Setting;
use app\models\SettingValue;
use app\models\SettingValueVote;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;

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
     * Website settings
     * @return string
     */
    public function actionIndex()
    {
        $setting = Setting::find();
        $countQuery = clone $setting;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $setting->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    /**
     * Setting values added by users
     * @return string
     */
    public function actionValues($id)
    {
        //Get settings validation configuration from params
        $settingsConfig = json_encode(Yii::$app->params['settings']);

        $settingValues = SettingValue::find()->where(['setting_id' => $id, 'is_current' => 0])->all();
        $setting = Setting::find()->where(['id' => $id])->one();

        if(is_object($setting)){
            return $this->render('setting-values', [
                'settingValues' => $settingValues,
                'setting' => $setting,
                'settingsConfig' => $settingsConfig,
            ]);
        }

        return $this->redirect('site/error');
    }

    /**
     * Create new value for a setting
     * @return string
     */
    public function actionCreateValue()
    {
        if (Yii::$app->request->isAjax) {
            $postData = Yii::$app->request->post();
            $setting_id = $postData['setting_id'];
            $new_value = $postData['new_value'];
            $settingValue = new SettingValue(['setting_id' => $setting_id, 'value' => $new_value, 'updated_at' => time()]);
            if ($settingValue->save()) {
                //Switch user vote to added value
                $this->saveVote(['setting_id' => $setting_id, 'value_id' => $settingValue->id]);
                return $this->redirect(['setting/values', 'id' => $setting_id]);
            }
        }
    }

    /**
     * Vote for a setting value.
     * @return integer
     */
    public function actionVote()
    {
        if (Yii::$app->request->isAjax) {
            $postData = Yii::$app->request->post();
            return $this->saveVote($postData);
        }
        return 0;
    }

    private function saveVote($postData)
    {
        $setting = Setting::findOne($postData['setting_id']);

        $settingVote = SettingValueVote::find()->where(['setting_id' => $postData['setting_id'], 'user_id' => Yii::$app->user->id])->one();

        if (!empty($settingVote)) {
            $settingVote->delete();
        }

        $settingVote = new SettingValueVote(['setting_id' => $postData['setting_id'], 'user_id' => Yii::$app->user->identity->id, 'created_at' => time()]);

        //Save current value in setting_value table if user voted for it
        if ($postData['value_id'] == -1) {
            $settingValue = new SettingValue(['setting_id' => $postData['setting_id'], 'value' => $setting->value, 'is_current' => 1, 'updated_at' => time()]);

            if ($settingValue->save()) {
                $postData['value_id'] = $settingValue->id;
            }
        }

        //Save vote
        if (!empty($postData['value_id']) && $postData['value_id'] != -1) {
            $settingVote->setting_value_id = $postData['value_id'];

            if ($settingVote->save()) {

                //Delete the setting value for which no votes exist
                $settingValues = $setting->settingValues;
                foreach ($settingValues as $settingValue) {
                    if (empty($settingValue->settingValueVotes)) {
                        $settingValue->delete();
                    }
                }

                //Make the voted setting value as current setting value, if it reach a threshhold of setting value 'website_setting_min_vote_percent_to_apply_change'
                try {
                    $votePercent = floor($settingVote->settingValue->getUserVotesPercent(false));

                    $threshHold = Setting::find()->select('value')->where(['key' => 'website_setting_min_vote_percent_to_apply_change'])->scalar();

                    if ($threshHold <= $votePercent) {
                        $setting->value = $settingVote->settingValue->value;
                        $setting->updated_at = time();
                        $setting->save();

                        //change the current value state in setting_value table
                        $settingValue = $setting->getDefaultSettingValue();
                        if (!empty($settingValue)) {
                            $settingValue->is_current = 0;
                            $settingValue->save();
                        }

                        $settingVote->settingValue->is_current = 1;
                        $settingVote->settingValue->save();

                    }

                } catch (\Exception $e) {
                    return 0;
                }
                return 1;
            }
        }
        return 0;
    }
}
