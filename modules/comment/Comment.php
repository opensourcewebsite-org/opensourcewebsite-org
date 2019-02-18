<?php
namespace app\modules\comment;

use Yii;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\bootstrap\Widget;
use yii\bootstrap\BootstrapAsset;
use app\modules\comment\assets\CommentsAsset;

/**
 * Component renders comments module.
 *
 * For example:
 *
 * ```php
 * echo Comment::widget([
 *       'model' => MoqupComment::class,
 *       'material' => 2,
 *       'related' => 'moqup_id',
 * ]);
 * ```
 *
 */
class Comment extends Widget
{
    public $items = [];
    public $model;
    public $material;
    public $related;

    const PAGE_SIZE = 20;

    /**
     * Renders the widget.
     */
    public function run()
    {
        BootstrapAsset::register($this->getView());
        CommentsAsset::register($this->getView());

        $this->setItems();
        $this->setClientScripts();

        $nextPages = '';
        $pageSize = 1;

        do {
            $pageSize++;

            $nextPages .= '<div id="next-page' . $pageSize . '"></div>';
        } while ($pageSize < $this->items['pagination']->getPageCount());

        return
            Html::tag(
                'div',
                $this->renderMainForm() .
                Html::tag(
                    'div',
                    Html::tag(
                        'div',
                        '',
                        ['id' => 'main-response']
                    ) .
                    $this->renderItems() . $nextPages,
                    ['class' => 'card-body card-comments', 'id' => 'comments']
                ),
                ['id' => 'accordion', 'class' => 'card card-widget']
            );
    }

    /**
     * @return void
     */
    public function setClientScripts()
    {
        $className = mb_strtolower(\yii\helpers\StringHelper::basename(get_class(new $this->model)));

        $this->view->registerJs('$(document).on(\'pjax:complete\', function(event) {
            $(\'form.formReplies #' . $className . '-message\').val(\'\');
            
            $(\'.modal\').modal(\'hide\');
        });
        $(\'#main-response\').on(\'pjax:end\', function(event) {
            $(\'#replyForm\').remove();
            $(\'#accordion .card-header\').remove();
        });
        ');
    }

    /**
     * @return string
     */
    public function renderMainForm()
    {
        $model = $this->model;
        if ($model::findOne([
            'user_id'      => Yii::$app->user->id,
            'parent_id'    => null,
            $this->related => $this->material,
        ])
        ) {
            return '';
        }

        return
            Html::tag(
                'div',
                $this->render('/../modules/comment/views/default/_reply_form', [
                    'parent'     => null,
                    'modelClass' => $this->model,
                    'related'    => $this->related,
                    'material'   => $this->material,
                    'mainForm'   => true,
                ]),
                ['class' => 'card-header']
            );
    }

    /**
     * @return void
     */
    public function setItems()
    {

        $query = static::baseQuery($this->model, $this->material, $this->related);

        $this->items = $query;
    }

    /**
     * Renders widget items.
     */
    public function renderItems()
    {
        return $this->render('/../modules/comment/views/default/comment_wrapper', [
            'items'      => $this->items['query'],
            'model'      => $this->model,
            'related'    => $this->related,
            'material'   => $this->material,
            'pagination' => $this->items['pagination'],
            'level'      => 1,
        ]);
    }


    /**
     * @param string $model
     * @param int $material
     * @param string|null $related
     * @param null|int $parent
     *
     * @return mixed
     */
    public static function baseQuery($model, $material, $related = null, $parent = null)
    {
        $pagination = new Pagination([
            'totalCount' => $model::find()
                ->where(['parent_id' => $parent])
                ->andWhere([$related => $material])
                ->count(),
            'pageSize'   => static::PAGE_SIZE,
        ]);

        if ($parent) {
            $query = $model::find()
                ->where([
                    'parent_id' => $parent,
                    $related    => $material,
                ])
                ->with('user')
                ->limit($pagination->getLimit())
                ->offset($pagination->getOffset())
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            return [
                'pagination' => $pagination,
                'query'      => $query,
            ];
        } else {
            $subQueryCount = $model::find()
                ->select('COUNT(`s`.`id`)')
                ->from($model::tableName() . ' s')
                ->where('s.parent_id=`m`.`id`');

            $query = $model::find()
                ->select(['*', 'count' => $subQueryCount])
                ->with('user')
                ->from($model::tableName() . ' m')
                ->where(['m.' . $related => $material])
                ->andWhere(['parent_id' => null])
                ->orderBy(['m.created_at' => SORT_DESC])
                ->limit($pagination->getLimit())
                ->offset($pagination->getOffset())
                ->all();

            return [
                'pagination' => $pagination,
                'query'      => $query,
            ];
        }
    }
}
