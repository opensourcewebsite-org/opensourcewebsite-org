<?php

namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use Yii;

class DeleteButton extends Widget
{

    /**
     * @var array|string
     * url
     */
    public $url;

    /**
     * @var string
     * class list
     */
    public $class;

    /**
     * @var string
     * text
     */
    private $text;

    /**
     * @var type of the button. That can be Trash or Delete
     */
    public $type;

    public function init()
    {
        parent::init();

        if($this->class == null) {
            $this->class = '';
        }

        switch(strtolower($this->type)) {
            case 'delete':
                $this->class .= 'btn btn-danger float-right';
                $this->text = 'delete';
                break;

            case 'trash':
                $this->text = '<i class="fa fa-trash"></i>';
                break;

            default :
                throw new InvalidConfigException("'type' property must be only 'trash' or 'delete'.");
                break;
        }

        if($this->url == null) {
            throw new InvalidConfigException("'url' property must be specified.");
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
            'class'        => 'btn-action ' . $this->class,
        ]);
    }
}
