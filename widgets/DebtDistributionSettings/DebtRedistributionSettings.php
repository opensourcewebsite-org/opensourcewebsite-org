<?php

namespace app\widgets\DebtDistributionSettings;

use app\interfaces\UserRelation\ByOwnerInterface;
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
        if (!$this->getModelSource()) {
            throw new InvalidConfigException("Either 'contact' or 'debtRed' property must be specified.");
        }

        if (!$this->debtRed) {
            $this->debtRed = DebtRedistributionForm::factory($this->contact);
        }

        if (empty($this->currencyList)) {
            $debtRedId = $this->debtRed->id ?? null;
            $modelSource = $this->getModelSource();

            //find all Currencies, excluding those which are already used
            $this->currencyList = Currency::find()
                ->select(['currency.id', 'currency.code'])
                ->excludeExistedInDebtRedistribution($modelSource, $debtRedId)
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
        return Yii::t('app', 'Debt Redistribution for user "#{userId}"', [
            'userId' => $this->getModelSource()->linkedUID(),
        ]);
    }

    private function getModelSource(): ByOwnerInterface
    {
        return $this->contact ?: $this->debtRed;
    }
}
