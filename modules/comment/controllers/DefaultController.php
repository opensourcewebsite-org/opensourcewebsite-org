<?php

namespace app\modules\comment\controllers;

use app\modules\comment\Comment;
use yii\web\Controller;
use yii\helpers\Html;
use Yii;
use yii\helpers\BaseHtml;
use yii\helpers\Url;
use yii\filters\AccessControl;

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
            'a',
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
    public function actionHandler()
    {
        $modelName = Yii::$app->request->post('model', null);

        $material = Yii::$app->request->post('material');
        $related = Yii::$app->request->post('related');

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

                    $items = Comment::baseQuery($model, $material, $related, $parent);

                    $Mitems = [];
                    foreach ($items as $step => $item) {
                        $options = [
                            'item'     => $item,
                            'model'    => $modelName,
                            'related'  => $related,
                            'material' => $material,
                            'level'    => 1,
                        ];

                        if (!$parent && $step == 0) {
                            $Mitems[] = $this->renderAjax('_comment_template', $options);
                        } else {
                            if ($parent) {
                                $options['level'] = 2;
                                $Mitems[] = $this->renderAjax('_comment_template', $options);
                            } else {
                                $Mitems[] = $this->renderPartial('_comment_template', $options);
                            }
                        }
                    }

                    return implode("\n", $Mitems);
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
    public function actionDelete($model, $id, $material, $related)
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

            $itemsQuery = Comment::baseQuery($model, $material);

            $items = [];
            foreach ($itemsQuery as $item) {
                $items[] = $this->renderPartial('_comment_template', [
                    'item'     => $item,
                    'model'    => $model,
                    'related'  => $related,
                    'material' => $material,
                    'level'    => 1,
                ]);
            }

            return implode("\n", $items);
        }

        return ' ';
    }
}
