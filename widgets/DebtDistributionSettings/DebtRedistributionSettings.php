<?php

namespace app\widgets\DebtDistributionSettings;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

class DebtRedistributionSettings extends Widget
{
    /** @var \app\models\Contact */
    public $contact;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->contact) {
            throw new InvalidConfigException("'contact' property must be specified.");
        }

        parent::init();
    }

    public function run()
    {
        echo $this->render('index', [
            'header' => $this->renderHeader(),
            'footer' => $this->renderFooter(),
        ]);
    }

    private function renderHeader(): string
    {
        return Yii::t('app', 'Debt Redistribution for user "#{userId}"', ['userId' => $this->contact->link_user_id]);
    }

    private function renderFooter(): string
    {
        return Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success'])
            . '<a class="btn btn-secondary" href="#" data-dismiss="modal">'
            .       Yii::t('app', 'Cancel')
            . '</a>';
    }
}
