<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles adding columns to table `{{%bot_user}}`.
 */
class m200330_105848_add_language_id_column_to_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('PRIMARY', '{{%language}}');

        $this->addColumn('{{%language}}', 'id', $this->primaryKey()->unsigned());

        $this->addColumn('{{%bot_user}}', 'language_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'ft-bot_user_language_id-language_id',
            '{{%bot_user}}',
            'language_id',
            '{{%language}}',
            'id'
        );

        $usedLanguages = (new Query())
            ->select('{{%language}}.code, {{%language}}.id')
            ->from('{{%language}}')
            ->innerJoin('{{%bot_user}}', '{{%bot_user}}.language_code = {{%language}}.code')
            ->groupBy('{{%language}}.id')
            ->all();

        foreach ($usedLanguages as $usedLanguage) {
            $this->update(
                '{{%bot_user}}',
                [ 'language_id' => $usedLanguage['id'] ],
                [ 'language_code' => $usedLanguage['code'] ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $usedLanguages = (new Query())
            ->select('{{%language}}.code, {{%language}}.id')
            ->from('{{%language}}')
            ->innerJoin('{{%bot_user}}', '{{%bot_user}}.language_id = {{%language}}.id')
            ->groupBy('{{%language}}.id')
            ->all();

        foreach ($usedLanguages as $usedLanguage) {
            $this->update(
                '{{%bot_user}}',
                [ 'language_code' => $usedLanguage['code'] ],
                [ 'language_id' => $usedLanguage['id'] ]
            );
        }

        $this->dropForeignKey(
            'ft-bot_user_language_id-language_id',
            '{{%bot_user}}'
        );

        $this->dropColumn('{{%bot_user}}', 'language_id');

        $this->dropColumn('{{%language}}', 'id');

        $this->addPrimaryKey('PRIMARY', '{{%language}}', 'code');
    }
}
