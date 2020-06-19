<?php

namespace app\tests\fixtures;

use app\models\Debt;
use app\tests\_extra\DebtBalanceFixture;
use yii\db\ActiveRecord;
use yii\test\ActiveFixture;

class DebtFixture extends ActiveFixture
{
    public $modelClass = Debt::class;

    public function beforeLoad()
    {
        (new DebtBalanceFixture)->unload();
        parent::beforeLoad();
    }

    public function load()
    {
        $this->data = [];
        foreach ($this->getData() as $alias => $row) {
            /** @var ActiveRecord $model */
            $model = new $this->modelClass;
            $model->setAttributes($row, false);
            $model->save(false);

            $this->data[$alias] = $model->attributes;
        }
    }
}
