<?php

namespace app\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use app\models\User;
use app\models\Moqup;
use app\models\Css;
use yii\db\Query;

class MoqupController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'design-list', 'design-view', 'design-edit', 'account'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionDesignList($viewMode = NULL)
    {
        if (!empty($viewMode)) {
            $viewMode = 1;
        } else {
            $viewMode = 0;
        }
        $query = new Query;
//        $query = new \yii\db\Query;
        $query->select(['moqup.*', 'user.username as username'])
                ->from('moqup')
                ->where(['!=', 'user_id', Yii::$app->user->id])
                ->leftJoin('user', 'moqup.user_id = user.id')
                ->all();

        $command = $query->createCommand();
        $moqups = $command->queryAll();

        $your_moqups_qry = new Query;
        $your_moqups_qry->select(['moqup.*', 'user.username as username'])
                ->from('moqup')
                ->where(['user_id' => Yii::$app->user->id])
                ->leftJoin('user', 'moqup.user_id = user.id')
                ->all();

        $your_moqups_cmd = $your_moqups_qry->createCommand();
        $your_moqups = $your_moqups_cmd->queryAll();
        
        return $this->render('design-list', ['viewMode' => $viewMode, 'moqups' => $moqups, 'your_moqups' => $your_moqups]);
    }

    public function actionDesignView($id)
    {
        $moqup = Moqup::find()
                ->where(['id' => $id])
                ->one();
        $css = Css::find()
                ->where(['moqup_id' => $id])
                ->one();
        return $this->render('design-view', ['moqup' => $moqup, 'css' => $css]);
    }

    public function actionDesignAdd()
    {
        if (Yii::$app->request->isPost) {
            $formatter = \Yii::$app->formatter;
            $now = $formatter->asDateTime('now');
            $now = strtotime($now);

            $moqup = new Moqup;
            $moqup->user_id = Yii::$app->user->id;
            $moqup->title = Yii::$app->request->post('title');
            $moqup->html = Yii::$app->request->post('html');
            $moqup->created_at = $now;
            $moqup->updated_at = $now;
            $moqup->save();

            $css = new Css;
            $css->moqup_id = $moqup->id;
            $css->css = Yii::$app->request->post('css');
            $css->created_at = $now;
            $css->updated_at = $now;
            $css->save();
//            Yii::$app->session->setFlash('success', 'Your new moqup has been saved.');
            return $this->redirect(['moqup/design-list']);
        }
        return $this->render('design-add');
    }

    public function actionDesignEdit($id)
    {
        $moqup = Moqup::find()
                ->where(['id' => $id])
                ->one();
        $css = Css::find()
                ->where(['moqup_id' => $id])
                ->one();
        if (Yii::$app->request->isPost) {
            $formatter = \Yii::$app->formatter;
            $now = $formatter->asDateTime('now');
            $now = strtotime($now);

            $moqup->user_id = Yii::$app->user->id;
            $moqup->title = Yii::$app->request->post('title');
            $moqup->html = Yii::$app->request->post('html');
            $moqup->updated_at = $now;
            $moqup->save();

            $css->css = Yii::$app->request->post('css');
            $css->updated_at = $now;
            $css->save();
//            Yii::$app->session->setFlash('success', 'Moqup has been updated.');
            return $this->redirect(['moqup/design-list']);
        }
        return $this->render('design-edit', ['moqup' => $moqup, 'css' => $css]);
    }
    
    public function actionDesignDelete()
    {
        if (Yii::$app->request->isPost) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
                $moqup_id = trim(Yii::$app->request->post('id'));
                if(\app\models\Moqup::find()->where(['id' => $moqup_id])->one()->delete()) {
                    return [
                        "status" => "success"
                    ];
                }
                else {
                    return [
                        "status" => "failure"
                    ];
                }
            }
        }
    }

    /**
     * Do tasks before the action is executed
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            $this->layout = 'adminlte-guest';
        } else {
            $this->layout = 'adminlte-main';
        }

        return true;
    }

}
