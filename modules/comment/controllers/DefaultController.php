<?php

namespace app\modules\comment\controllers;

use app\modules\comment\Comment;
use yii\web\Controller;
use yii\helpers\Html;
use Yii;
use yii\helpers\BaseHtml;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\ServerErrorHttpException;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->request->isAjax) {
            throw new ServerErrorHttpException('Wrong type request');
        }

        return parent::beforeAction($action);
    }

    /**
     * @param int $parent_id
     * @param int $material
     * @param string $related
     * @param string $model
     *
     * @return string
     */
    public function actionIndex($parent_id, $material, $related, $model)
    {

        $modelClass = $model;

        //TODO check access
        $items = $model::find()->where([
            'parent_id' => (int)$parent_id,
            $related    => $material,
        ])->with('user')->all();

        $count = count($items);

        $html = Html::tag(
            'span',
            "replies ({$count})",
            [
                'class' => 'text-muted show-reply',
                'href'  => Url::to([
                    '/comment/default/index',
                    'parent_id' => $parent_id,
                    'material'  => $material,
                    'related'   => $related,
                    'model'     => $model,
                ]),
            ]
        );

        $html .= '<br /><br />';
        foreach ($items as $item) {
            $html .= $this->renderAjax('_comment_template', [
                'item'     => $item,
                'related'  => $related,
                'material' => $material,
                'model'    => $modelClass,
                'level'    => 2,
            ]);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function actionPager()
    {
        $model = Yii::$app->request->get('model');
        $related = Yii::$app->request->get('related');
        $material = Yii::$app->request->get('material');

        $query = Comment::baseQuery($model, $material, $related);
        $items = $query['query'];

        //TODO THINK OVER LEVEL PARAM
        return $this->renderAjax('comment_wrapper', [
            'items'      => $items,
            'model'      => $model,
            'related'    => $related,
            'material'   => $material,
            'pagination' => $query['pagination'],
            'level'      => 1,
        ]);
    }

    /**
     * @return string
     */
    public function actionHandler()
    {
        $modelName = Yii::$app->request->post('model', null);

        $material = Yii::$app->request->post('material');
        $related = Yii::$app->request->post('related');
        $mainForm = Yii::$app->request->post('mainForm', false);

        $model = new $modelName;
        if ($model->load(Yii::$app->request->post())) {
            $message = $model->message;

            $model->$related = $material;

            if ($model->validate()) {
                //TODO check Access
                if ($q = $model::findOne(['id' => $model->id])) {
                    $model = $q;
                    $model->setAttributes([
                        'message' => $message,
                    ]);
                    $model->$related = $material;
                }

                if ($model->save()) {
                    if ($model->parent_id) {
                        $parent = $model->parent_id;
                    } else {
                        $parent = null;
                    }

                    //TODO check access
                    $query = Comment::baseQuery($model, $material, $related, $parent);
                    $items = $query['query'];

                    if (!$parent && $mainForm) {
                        $nextPages = '';
                        $pageSize = 1;

                        do {
                            $pageSize++;

                            $nextPages .= '<div id="next-page' . $pageSize . '"></div>';
                        } while ($pageSize < $query['pagination']->getPageCount());

                        $options = [
                            'model'    => $modelName,
                            'related'  => $related,
                            'material' => $material,
                            'level'    => 1,
                        ];

                        return $this->renderAjax(
                            '_comment_template',
                            array_merge($options, ['item' => array_shift($items)])
                        );
                    }

                    if ($parent) {
                        $count = count($items);

                        $html = Html::tag(
                            'span',
                            "replies ({$count})",
                            [
                                'class' => 'text-muted show-reply',
                            ]
                        );

                        $html .= '<br /><br />';
                    }

                    $Mitems = [];
                    foreach ($items as $step => $item) {
                        $options = [
                            'item'     => $item,
                            'model'    => $modelName,
                            'related'  => $related,
                            'material' => $material,
                            'level'    => 1,
                        ];

                        if ($parent) {
                            $options['level'] = 2;
                            $Mitems[] = $this->renderAjax('_comment_template', $options);
                        } else {
                            $Mitems[] = $this->renderPartial('_comment_template', $options);
                        }
                    }

                    if ($parent) {
                        return $html . implode("\n", $Mitems);
                    } else {
                        return implode("\n", $Mitems);
                    }
                }
            }
            Yii::$app->session->setFlash('danger', BaseHtml::errorSummary($model));
        }

        return ' ';
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        //TODO check access
        $modelName = Yii::$app->request->post('model', null);

        $model = $modelName::findOne(['id' => $id]);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    return $model->message;
                }
            }
        }
        Yii::$app->session->setFlash('danger', BaseHtml::errorSummary($model));
    }

    /**
     * @param string $model
     * @param int $id
     * @param int $material
     * @param string $related
     *
     * @return bool|string
     * @throws \yii\db\Exception
     */
    public function actionDelete($model, $id, $material, $related, $level)
    {
        //TODO check access
        if ($main = $model::findOne(['id' => (int)$id])) {
            $transaction = Yii::$app->db->beginTransaction('SERIALIZABLE');

            $inside = $model::find()->select('id')->where(['parent_id' => $main->id])->column();

            if (!empty($inside)) {
                if (!$model::deleteAll(['id' => $inside])) {
                    $transaction->rollBack();

                    return false;
                }
            }

            if (!$main->delete()) {
                $transaction->rollBack();
            }

            $transaction->commit();

            $parent = null;
            if ($main->parent_id) {
                $parent = $main->parent_id;
            }

            $query = Comment::baseQuery($model, $material, $related, $parent);
            $itemsQuery = $query['query'];

            $items = [];

            $count = count($itemsQuery);

            $html = Html::tag(
                'span',
                "replies ({$count})",
                [
                    'class' => 'text-muted show-reply',
                ]
            );

            $html .= '<br /><br />';

            foreach ($itemsQuery as $item) {
                $items[] = $this->renderPartial('_comment_template', [
                    'item'     => $item,
                    'model'    => $model,
                    'related'  => $related,
                    'material' => $material,
                    'level'    => 2,
                ]);
            }

            if ($level == 1) {
                return ' ';
            } else {
                return $html . implode("\n", $items);
            }
        }

        return ' ';
    }
}
