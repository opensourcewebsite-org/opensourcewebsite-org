<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200330_095629_add_currency_id_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'currency_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-user_currency_id-currency_id',
            '{{%user}}',
            'currency_id',
            'currency',
            'id'
        );

        $usedCurrencies = (new Query())
            ->select([ '{{%currency}}.code, {{%currency}}.id' ])
            ->from('{{%currency}}')
            ->innerJoin('{{%bot_user}}', '{{%bot_user}}.currency_code = {{%currency}}.code')
            ->groupBy('{{%currency}}.code')
            ->all();

        foreach ($usedCurrencies as $usedCurrency) {
            $this->db->createCommand(
                "UPDATE {{%user}} JOIN {{%bot_user}} ON {{%bot_user}}.user_id = {{%user}}.id SET {{%user}}.currency_id = '$usedCurrency[id]' WHERE {{%bot_user}}.currency_code = '$usedCurrency[code]'"
                )
                ->execute();
        }

        $this->dropColumn('{{%bot_user}}', 'currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_user}}', 'currency_code',$this->string()->notNull()->defaultValue('USD'));

        $usedCurrencies = (new Query())
            ->select([ '{{%currency}}.code, {{%currency}}.id' ])
            ->from('{{%currency}}')
            ->innerJoin('{{%user}}', '{{%user}}.currency_id = {{%currency}}.id')
            ->groupBy('{{%currency}}.code')
            ->all();

        foreach ($usedCurrencies as $usedCurrency) {
            $this->db->createCommand(
                "UPDATE {{%bot_user}} JOIN {{%user}} ON {{%bot_user}}.user_id = {{%user}}.id SET {{%bot_user}}.currency_code = '$usedCurrency[code]' WHERE {{%user}}.currency_id = '$usedCurrency[id]'"
            )
                ->execute();
        }

        $this->dropForeignKey(
            'fk-user_currency_id-currency_id',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'currency_id');
    }
}
