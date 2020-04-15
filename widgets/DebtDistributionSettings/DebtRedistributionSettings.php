<?php

namespace app\widgets\DebtDistributionSettings;

use app\models\Currency;
use app\models\DebtRedistributionForm;
use PDO;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;

class DebtRedistributionSettings extends Widget
{
    /** @var null|\app\models\Contact */
    public $contact;
    /** @var null|DebtRedistributionForm */
    public $debtRed;
    /** @var array [id => code] */
    public $currencyList;

    /**
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        if (!$this->contact && !$this->debtRed) {
            throw new InvalidConfigException("'contact' property must be specified.");
        }

        if (!$this->debtRed) {
            $this->debtRed = DebtRedistributionForm::factory($this->contact);
        }

        if (empty($this->currencyList)) {
            $debtRedId = $this->debtRed->id ?? null;

            //find all Currencies, excluding those which are already used
            $this->currencyList = Currency::find()
                ->select(['currency.id', 'currency.code'])
                ->excludeExistedInDebtRedistribution($this->getFromUserId(), $this->getToUserId(), $debtRedId)
                ->orderBy('currency.code')
                ->createCommand()
                ->queryAll(PDO::FETCH_KEY_PAIR);
        }

        parent::init();
    }

    public function run()
    {
        echo $this->render('form', [
            'header' => $this->renderHeader(),
        ]);
    }

    private function renderHeader(): string
    {
        return Yii::t('app', 'Debt Redistribution for user "#{userId}"', ['userId' => $this->getToUserId()]);
    }

    private function getFromUserId()
    {
        return $this->contact ? $this->contact->user_id : $this->debtRed->from_user_id;
    }

    private function getToUserId()
    {
        return $this->contact ? $this->contact->link_user_id : $this->debtRed->to_user_id;
    }
}
