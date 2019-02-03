<?php
namespace app\modules\comment;

use app\modules\comment\models\MoqupComment;
use Yii;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\bootstrap\Widget;
use yii\bootstrap\BootstrapAsset;
use app\modules\comment\assets\CommentsAsset;

/**
 * Component renders list of HTML comments.
 *
 * For example:
 *
 * ```php
 * echo Nav::widget([
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

    const PAGE_SIZE = 2;

    /**
     * Renders the widget.
     */
    public function run()
    {
        BootstrapAsset::register($this->getView());
        CommentsAsset::register($this->getView());

        $this->setItems();


        $pagination = new Pagination([
            'totalCount' => MoqupComment::find()->where(['parent_id' => null])->count(),
            'pageSize' => static::PAGE_SIZE,
        ]);

        $nextPages = '';
        $pageSize= 1;

        do {
            $pageSize++;

            $nextPages .= '<div id="next-page'.$pageSize.'"></div>';
        } while ($pageSize < $pagination->getPageCount());

        return
            Html::tag(
                'div',
                Html::tag(
                    'div',
                    $this->renderMainForm(),
                    ['class' => 'card-header']
                ) .
                Html::tag(
                    'div',
                    $this->renderItems() . $nextPages,
                    ['class' => 'card-body card-comments', 'id' => 'comments']
                ),
                ['id' => 'accordion', 'class' => 'card card-widget']
            );
    }

    /**
     * @return string
     */
    public function renderMainForm()
    {
        return $this->render('/../modules/comment/views/default/_reply_form', [
            'parent' => null,
            'modelClass'  => $this->model,
            'related' => $this->related,
            'material' => $this->material,
        ]);
    }

    /**
     * @return void
     */
    public function setItems()
    {
        $this->items = static::baseQuery($this->model, $this->material, $this->related);
    }

    /**
     * Renders widget items.
     */
    public function renderItems()
    {
        return $this->render('/../modules/comment/views/default/comment_wrapper', [
            'items' => $this->items,
            'model'    => $this->model,
            'related'  => $this->related,
            'material' => $this->material,
            'level'    => 1,
        ]);
//        foreach ($this->items as $item) {
//            $items[] = $this->view->render('/../modules/comment/views/default/_comment_template', [
//                'item'     => $item,
//            ]);
//        }
//
//        return implode("\n", $items);
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
            'totalCount' => $model::find()->count(),
            'pageSize' => static::PAGE_SIZE,
        ]);

        if ($parent) {
            return $model::find()->where([
                'parent_id' => $parent,
                $related    => $material,
            ])->with('user')->all();
        } else {
            $subQueryCount = $model::find()
                ->select('COUNT(`s`.`id`)')
                ->from($model::tableName() . ' s')
                ->where('s.parent_id=`m`.`id`');

            return $model::find()
                ->select(['*', 'count' => $subQueryCount])
                ->with('user')
                ->from($model::tableName() . ' m')
                ->where(['m.' . $related => $material, 'parent_id' => null])
                ->orderBy(['m.created_at' => SORT_DESC])
                ->limit($pagination->getLimit())
                ->offset($pagination->getOffset())
                ->all();
        }
    }
}
