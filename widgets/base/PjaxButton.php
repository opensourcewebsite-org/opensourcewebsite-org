<?php

namespace app\widgets\base;

use yii\base\Widget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use Yii;

class PjaxButton extends Widget
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

    /**
     * @var bool
     * enable confirm window
     */
    public $confirm;

    public function init()
    {
        parent::init();

        $this->defaultOptions = [
            'title'         => Yii::t('yii', 'Button'),
            'aria-label'    => Yii::t('yii', 'Button'),
            'data-pjax'     => '1',
            'data-method'   => 'post',
            'class'         => 'btn-action',
            'confirmMessage' => 'Are you sure you want to delete this item?',
        ];

        if ($this->options == null) {
            $this->options = [];
        }
    }

    public function run()
    {
        if ($this->confirm == true) {
            $this->options['data-confirm'] = Yii::t('yii', $this->defaultOptions['confirmMessage']);
        }

        return Html::a(Yii::t('app', $this->text), $this->url, array_merge($this->defaultOptions, $this->options));
    }
}
