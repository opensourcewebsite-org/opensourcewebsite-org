<?php

namespace app\components;

use yii\helpers\Html;
use yii\grid\DataColumn;

class TitleColumn extends DataColumn
{

    public $title;
    public $encodeLabel = false;
    public $headerTitle = true;

    protected function renderHeaderCellContent()
    {
        $content = parent::renderHeaderCellContent();

        return $this->headerTitle ? Html::tag('span', $content, ['title' => $this->title]) : $content;
    }
}
