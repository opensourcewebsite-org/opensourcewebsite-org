<?php

namespace app\controllers;

use app\components\Converter;
use app\models\Css;
use app\models\Moqup;
use app\models\MoqupSearch;
use app\models\Setting;
use app\models\User;
use app\models\UserMoqupFollow;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

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
                    'design-delete' => ['post'],
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
     * Do tasks before the action is executed
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->layout = 'adminlte-guest';
        } else {
            $this->layout = 'adminlte-user';
        }

        return parent::beforeAction($action);
    }

    /**
     * Shows a list of the registered moqups
     */
    public function actionDesignList($viewYours = false, $viewFollowing = false)
    {
        $searchModel = new MoqupSearch();
        $params = Yii::$app->request->queryParams;

        if ($viewYours) {
            $params['viewYours'] = true;
        }

        if ($viewFollowing) {
            $params['viewFollowing'] = true;
        }

        $dataProvider = $searchModel->search($params);

        $countYours = Moqup::find()->where(['user_id' => Yii::$app->user->identity->id])->count();
        $countFollowing = Moqup::find()
            ->alias('m')
            ->leftJoin(UserMoqupFollow::tableName() . ' umf', 'umf.moqup_id = m.id')
            ->where(['umf.user_id' => Yii::$app->user->identity->id])
            ->count();
        $countAll = Moqup::find()->count();

        $maxMoqupValue = Setting::getValue('moqup_quantity_value_per_one_rating');

        return $this->render('design-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'viewFollowing' => $viewFollowing,
            'viewYours' => $viewYours,
            'countYours' => $countYours,
            'countFollowing' => $countFollowing,
            'countAll' => $countAll,
            'maxMoqupValue' => $maxMoqupValue,
        ]);
    }

    public function actionDesignView($id)
    {
        $moqup = Moqup::findOne($id);

        if ($moqup == null) {
            return $this->redirect(Yii::$app->request->referrer ?: ['moqup/design-list']);
        }

        $css = $moqup->css;

        return $this->render('design-view', ['moqup' => $moqup, 'css' => $css]);
    }

    /**
     * Create or edit a moqup
     * @param integer $id The id of the moqup to be updated
     */
    public function actionDesignEdit($id = null, $fork = null)
    {
        if ($id == null && $fork == null) {
            if (Yii::$app->user->identity->reachMaxMoqupsNumber || Yii::$app->user->identity->reachMaxMoqupsSize) {
                return $this->redirect(Yii::$app->request->referrer ?: ['moqup/design-list']);
            }

            $moqup = new Moqup(['user_id' => Yii::$app->user->identity->id]);
            $css = new Css();
        } elseif ($id == null && $fork != null) {
            $origin = Moqup::findOne($fork);

            if ($origin == null || Yii::$app->user->identity->reachMaxMoqupsNumber || Yii::$app->user->identity->reachMaxMoqupsSize) {
                return $this->redirect(Yii::$app->request->referrer ?: ['moqup/design-list']);
            }

            $moqup = new Moqup([
                'user_id' => Yii::$app->user->identity->id,
                'title' => $origin->title,
                'html' => $origin->html,
                'forked_of' => $origin->id,
            ]);

            $css = new Css([
                'css' => ($origin->css != null) ? $origin->css->css : null,
            ]);
        } else {
            $moqup = Moqup::findOne($id);

            if ($moqup == null) {
                throw new \yii\web\NotFoundHttpException();
            }

            if ($moqup->css != null) {
                $css = $moqup->css;
            } else {
                $css = new Css();
            }
        }

        if ($moqup->load(Yii::$app->request->post()) && $css->load(Yii::$app->request->post())) {
            $success = false;
            $transaction = Yii::$app->db->beginTransaction();

            $maxSize = Yii::$app->user->identity->maxMoqupsSize;
            $currentSize = Yii::$app->user->identity->totalMoqupsSize;
            $moqupLength = Converter::byteToMega(strlen($moqup->html));
            $cssLength = Converter::byteToMega(strlen($css->css));

            if ($maxSize < ($currentSize + $moqupLength + $cssLength)) {
                $moqup->addError('title', 'You reach your maximum moqups total size.');
            }

            if (!$moqup->hasErrors() && $moqup->save()) {
                $success = true;

                if (!$moqup->isNewRecord || $css->css != '') {
                    $css->moqup_id = $moqup->id;

                    if ($css->save()) {
                        $success = true;
                    } else {
                        $success = false;
                    }
                }

                if ($success) {
                    $transaction->commit();
                    return $this->redirect(['moqup/design-list', 'viewYours' => true]);
                } else {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('design-edit', [
            'moqup' => $moqup,
            'css' => $css,
        ]);
    }

    /**
     * Deletes a moqup
     * @param integer $id The id of the moqup being deleted
     */
    public function actionDesignDelete($id)
    {
        $moqup = Moqup::findOne($id);

        $css = Css::find()
            ->where(['moqup_id' => $id])
            ->one();

        if ($moqup != null) {
            if ($css != null) {
                $css->delete();
            }

            $moqup->delete();
        }

        $this->redirect(['moqup/design-list']);
    }

    /**
     * Renders a page to preview the moqups
     */
    public function actionDesignPreview()
    {
        $this->layout = 'adminlte-moqup-preview';
        return $this->render('design-preview');
    }
}
