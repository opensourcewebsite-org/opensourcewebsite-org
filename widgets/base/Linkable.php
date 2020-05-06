<?php

namespace app\widgets\base;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Linkable extends Widget
{
    /**
     * @var array|string|null
     * url
     */
    public $url;

    /**
     * @var array
     * options in view
     */
    public $options;

    /**
     * @var array
     * default options in class
     */
    protected $defaultOptions;

    /**
     * @var string
     * text
     */
    public $text;

    public function init() {
        parent::init();

        if ($this->options == null) {
            $this->options = [];
        }

        if ($this->defaultOptions == null) {
            $this->defaultOptions = [];
        }
    }

    public function run() {
        return Html::a(Yii::t('app', $this->text), $this->url, array_merge($this->defaultOptions, $this->options));
    }
}
