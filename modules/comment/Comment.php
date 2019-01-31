<?php
namespace app\modules\comment;

use app\modules\comment\models\MoqupComment;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;
use yii\helpers\Html;
use yii\bootstrap\Widget;
use yii\bootstrap\BootstrapAsset;
use yii\helpers\Url;
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

        if ($this->postHandler()) {
            Yii::$app->session->setFlash('success', 'Saved!');
            $this->view->context->refresh();
            Yii::$app->end();
        }

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
                    ['class' => 'card-body card-comments']
                ),
                ['id' => 'accordion', 'class' => 'card card-widget']
            );
    }

    /**
     * @return bool
     */
    public function postHandler()
    {
        $model = new $this->model;
        $related = $this->related;

        if ($model->load(Yii::$app->request->post())) {
            $model->$related = $this->material;
            if ($model->validate() && $model->save()) {
                return true;
            }
            Yii::$app->session->setFlash('danger', BaseHtml::errorSummary($model));
        }

        return false;
    }

    /**
     * @return string
     */
    public function renderMainForm()
    {
        $model = new $this->model;

        return $this->render('/../modules/comment/views/default/_reply_form', [
            'parent' => null,
            'model'  => $model,
        ]);
    }

    /**
     * @return void
     */
    public function setItems()
    {
        $model = $this->model;

        $subQueryCount = $model::find()
            ->select('COUNT(`s`.`id`)')
            ->from($model::tableName() . ' s')
            ->where('s.parent_id=`m`.`id`');

        $this->items = $model::find()
            ->select(['*', 'count' => $subQueryCount])
            ->with('user')
            ->from($model::tableName() . ' m')
            ->where(['m.moqup_id' => $this->material, 'parent_id' => null])
            ->all();
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
}
