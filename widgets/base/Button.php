<?php

namespace app\widgets\base;

use yii\base\Widget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use Yii;

class Button extends Widget
{

    /**
     * @var array|string
     * url
     */
    public $url;

    /**
     * @var string
     * additional class list
     */
    public $addClass;

    /**
     * @var string
     * text
     */
    protected $text;

    /**
     * @var array|string
     * additional style list
     */
    public $addStyle;

    public function init()
    {
        parent::init();

        if ($this->addClass == null) {
            $this->addClass = '';
        }

        if ($this->addStyle == null) {
            $this->addStyle = '';
        }

        $this->text = Yii::t('app', 'Button');

        if ($this->url == null) {
            throw new InvalidConfigException(Yii::t('app', "'url' property must be specified."));
        }
    }

    public function run()
    {
        return Html::a($this->text, $this->url, [
            'title'        => Yii::t('yii', 'Delete'),
            'aria-label'   => Yii::t('yii', 'Delete'),
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-pjax'    => '1',
            'data-method'  => 'post',
            'class'        => 'btn-action ' . $this->addClass,
            'style'        => $this->addStyle,
        ]);
    }
}
