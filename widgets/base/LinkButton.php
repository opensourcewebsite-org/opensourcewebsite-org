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

    /**
     * @var bool
     * enable confirm window
     */
    protected $confirm;

    /**
     * @var array
     * params for Html::a
     */
    protected $params;

    public function init()
    {
        parent::init();

        if ($this->addClass == null) {
            $this->addClass = '';
        }

        $this->text = Yii::t('app', 'Button');

    }

    public function run()
    {
        $this->params = [
            'title'        => Yii::t('yii', 'Delete'),
            'aria-label'   => Yii::t('yii', 'Delete'),
            'data-pjax'    => '1',
            'data-method'  => 'post',
            'class'        => 'btn-action ' . $this->addClass,
            'style'        => $this->addStyle,
        ];

        if($this->confirm == true) {
            $this->params['data-confirm'] = Yii::t('yii', 'Are you sure you want to delete this item?');
        }

        return Html::a($this->text, $this->url, $this->params);
    }
}
