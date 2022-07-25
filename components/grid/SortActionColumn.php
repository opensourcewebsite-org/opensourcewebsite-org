<?php


namespace app\components\grid;

use Yii;
use yii\grid\ActionColumn;
use yii\helpers\Html;

class SortActionColumn extends ActionColumn
{
    public $template = '<span class="sort-actions">{sort-up} {sort-down}</span>';
    public $options = [
        'class' => 'action-column',
    ];
    public $sortUpUrl;
    public $sortDownUrl;

    public function init()
    {
        parent::init();
        $sortUpUrl = $this->sortUpUrl;
        $sortDownUrl = $this->sortDownUrl;
        $this->buttons = [
            'sort-up' => function ($url, $model, $key) use ($sortUpUrl) {
                return Html::a('<span class="fas fa-arrow-up"></span>', [$sortUpUrl, 'id' => $key], [
                    'data-original-title' => Yii::t('app', 'Move up'),
                    'data-tooltip' => 'tooltip',
                ]);
            },
            'sort-down' => function ($url, $model, $key) use ($sortDownUrl) {
                return Html::a('<span class="fas fa-arrow-down"></span>', [$sortDownUrl, 'id' => $key], [
                    'data-original-title' => Yii::t('app', 'Move down'),
                    'data-tooltip' => 'tooltip',
                ]);
            },
        ];
    }
}
