<?php

namespace app\modules\comment\controllers;

use app\modules\comment\Comment;
use yii\web\Controller;
use yii\helpers\Html;
use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;

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
        $nextPage = Yii::$app->request->get('page', 1) + 1;

        $query = Comment::baseQuery($model, $material, $related, $parent_id);

        $items = $query['query'];

        $items = array_reverse($items);

        $count = $query['pagination']->totalCount;

        $nextPages = '';
        $pageSize = $query['pagination']->pageCount + 1;

        do {
            $pageSize--;

            $nextPages .= '<div id="inside-next-page_' . $parent_id . '_' . $pageSize . '"></div>';
        } while ($pageSize > 1);

        $html = Html::tag(
            'span',
            "View replies ({$count})",
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

        $moreComments = '';


        if ($query['pagination']->pageCount > 1) {
            $moreComments = $this->renderAjax('_show_more', [
                'related'   => $related,
                'material'  => $material,
                'model'     => $modelClass,
                'inside'    => $nextPage,
                'parent_id' => $parent_id,
            ]);
        }

        $html .= '<br /><br />' . $nextPages . $moreComments;
        foreach ($items as $item) {
            $html .= $this->renderAjax('_comment_template', [
                'item'     => $item,
                'related'  => $related,
                'material' => $material,
                'model'    => $modelClass,
                'level'    => 2,
                'mainForm' => false,
            ]);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function actionInsidePager()
    {
        $model = Yii::$app->request->get('model');
        $related = Yii::$app->request->get('related');
        $material = Yii::$app->request->get('material');
        $parent_id = Yii::$app->request->get('parent_id');

        $query = Comment::baseQuery($model, $material, $related, $parent_id);
        $items = $query['query'];

        $items = array_reverse($items);

        $html = '';

        $nextPage = Yii::$app->request->get('page', 1) + 1;

        $moreComments = '';
        if ($query['pagination']->pageCount != 0 && Yii::$app->request->get('page', 1) < $query['pagination']->pageCount) {
            $moreComments = $this->renderAjax('_show_more', [
                'related'   => $related,
                'material'  => $material,
                'model'     => $model,
                'inside'    => $nextPage,
                'parent_id' => $parent_id,
            ]);
        }

        $html .= $moreComments;

        foreach ($items as $item) {
            $html .= $this->renderAjax('_comment_template', [
                'item'     => $item,
                'related'  => $related,
                'material' => $material,
                'model'    => $model,
                'level'    => 2,
                'mainForm' => false,
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

        /**
         * @var $pagination \yii\data\Pagination
         */
        $pagination = $query['pagination'];

        # Small bug fixed: if user deleted his message
        # pagination works incorrect (FIXED)
        if ($pagination->totalCount == $pagination->pageSize) {
            array_shift($items);
        }

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
                            'mainForm' => $mainForm,
                        ];

                        return $this->renderAjax(
                            '_comment_template',
                            array_merge($options, ['item' => array_shift($items)])
                        );
                    }

                    if ($parent) {
                        $count = $query['pagination']->totalCount;

                        $html = Html::tag(
                            'span',
                            "View replies ({$count})",
                            [
                                'class' => 'text-muted show-reply',
                            ]
                        );

                        $moreComments = '';
                        if ($query['pagination']->pageCount > 1) {
                            $moreComments = $this->renderAjax('_show_more', [
                                'related'   => $related,
                                'material'  => $material,
                                'model'     => $modelName,
                                'inside'    => 2,
                                'parent_id' => $parent,
                            ]);
                        }

                        $pageSize = $query['pagination']->pageCount + 1;

                        $nextPages = '';
                        do {
                            $pageSize--;

                            $nextPages .= '<div id="inside-next-page_' . $parent . '_' . $pageSize . '"></div>';
                        } while ($pageSize > 1);

                        $html .= '<br /><br />' . $nextPages . $moreComments;

                        $items = array_reverse($items);
                    }


                    $Mitems = [];
                    foreach ($items as $step => $item) {
                        $options = [
                            'item'     => $item,
                            'model'    => $modelName,
                            'related'  => $related,
                            'material' => $material,
                            'level'    => 1,
                            'mainForm' => $mainForm,
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

            //TODO optimization
            # NEED FOR OPTIMIZATION
            # FIXES BUG
            if ($model->parent_id) {
                $parent = $model->parent_id;
            } else {
                $parent = null;
            }


            $query = Comment::baseQuery($model, $material, $related, $parent);
            $items = $query['query'];

            $html = '';
            if ($parent) {
                $count = $query['pagination']->totalCount;

                $items = array_reverse($items);

                $moreComments = '';
                if ($query['pagination']->pageCount > 1) {
                    $moreComments = $this->renderAjax('_show_more', [
                        'related'   => $related,
                        'material'  => $material,
                        'model'     => $modelName,
                        'inside'    => 2,
                        'parent_id' => $parent,
                    ]);
                }

                $pageSize = $query['pagination']->pageCount + 1;

                $nextPages = '';
                do {
                    $pageSize--;

                    $nextPages .= '<div id="inside-next-page_' . $parent . '_' . $pageSize . '"></div>';
                } while ($pageSize > 1);

                $html = Html::tag(
                    'span',
                    "View replies ({$count})",
                    [
                        'class' => 'text-muted show-reply',
                    ]
                );

                $html .= '<br /><br />' . $nextPages . $moreComments;
            }

            $Mitems = [];
            foreach ($items as $step => $item) {
                $options = [
                    'item'     => $item,
                    'model'    => $modelName,
                    'related'  => $related,
                    'material' => $material,
                    'level'    => 1,
                    'mainForm' => $mainForm,
                ];

                if ($parent) {
                    $options['level'] = 2;
                    $Mitems[] = $this->renderAjax('_comment_template', $options);
                }
            }

            return $html . implode("\n", $Mitems);
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
        $modelName = Yii::$app->request->post('model', null);

        $model = $modelName::findOne(['id' => $id]);
        $message = $model->message;
        $this->checkAccess($model->id, $modelName);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    return $model->message;
                }
            }

            return $message;
        }
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
        $this->checkAccess($id, $model);

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

            $count = $query['pagination']->totalCount;

            if ($count > 0) {
                $html = Html::tag(
                    'span',
                    "View replies ({$count})",
                    [
                        'class' => 'text-muted show-reply',
                    ]
                );

                $moreComments = '';
                $nextPages = '';
                if ($parent) {
                    if ($query['pagination']->pageCount > 1) {
                        $moreComments = $this->renderAjax('_show_more', [
                            'related'   => $related,
                            'material'  => $material,
                            'model'     => $model,
                            'inside'    => 2,
                            'parent_id' => $parent,
                        ]);
                    }

                    $pageSize = $query['pagination']->pageCount + 1;

                    do {
                        $pageSize--;

                        $nextPages .= '<div id="inside-next-page_' . $parent . '_' . $pageSize . '"></div>';
                    } while ($pageSize > 1);
                }

                $html .= '<br /><br />' . $nextPages . $moreComments;
            } else {
                $html = ' ';
            }

            foreach ($itemsQuery as $item) {
                $items[] = $this->renderPartial('_comment_template', [
                    'item'     => $item,
                    'model'    => $model,
                    'related'  => $related,
                    'material' => $material,
                    'level'    => 2,
                    'mainForm' => false,
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

    /**
     * @param int $id
     * @param string $model
     *
     * @return void
     * @throws NotFoundHttpException
     */
    protected function checkAccess($id, $model)
    {
        $access = $model::findOne([
            'user_id' => Yii::$app->user->id,
            'id'      => $id,
        ]);

        if (!$access) {
            throw new NotFoundHttpException;
        }
    }
}
