<?php

namespace app\widgets\base;

use yii\base\Widget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use Yii;

class LinkButton extends Widget
{

    /**
     * @var array|string
     * url
     */
    public $url;

    /**
     * @var array
     * options
     */
    public $options;

    /**
     * @var array
     * default options
     */
    protected $defaultOptions;

    /**
     * @var string
     * text
     */
    public $text;

    public function init()
    {
        parent::init();

        $this->defaultOptions = [
            'title' => Yii::t('yii', 'Button'),
            'class' => 'btn btn-action',
        ];

        if($this->options == null) {
            $this->options = [];
        }
    }

    public function run()
    {
        return Html::a(Yii::t('app', $this->text), $this->url, array_merge($this->defaultOptions, $this->options));
    }
}
