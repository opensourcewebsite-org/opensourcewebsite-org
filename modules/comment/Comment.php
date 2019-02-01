<?php
namespace app\modules\comment;

use Yii;
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

    /**
     * Renders the widget.
     */
    public function run()
    {
        BootstrapAsset::register($this->getView());
        CommentsAsset::register($this->getView());

        $this->setItems();

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
                    $this->renderItems(),
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
        $this->items = static::baseQuery($this->model, $this->material);
    }

    /**
     * Renders widget items.
     */
    public function renderItems()
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $this->view->render('/../modules/comment/views/default/_comment_template', [
                'item'     => $item,
                'model'    => $this->model,
                'related'  => $this->related,
                'material' => $this->material,
                'level'    => 1,
            ]);
        }

        return implode("\n", $items);
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
                ->where(['m.moqup_id' => $material, 'parent_id' => null])
                ->orderBy(['m.created_at' => SORT_DESC])
                ->all();
        }
    }
}
