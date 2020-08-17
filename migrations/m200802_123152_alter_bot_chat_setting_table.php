<?php

use yii\db\Migration;
use yii\db\Query;
use app\modules\bot\models\ChatMember;

/**
 * Class m200802_123152_alter_bot_chat_setting_table
 */
class m200802_123152_alter_bot_chat_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%bot_chat_setting}}', 'value', $this->text());
        $this->addColumn('{{%bot_chat_setting}}', 'updated_by', $this->integer()->unsigned()->notNull());

        $rows = (new Query())
            ->select('bot_chat_setting.id, bot_chat_member.user_id')
            ->from('{{%bot_chat_setting}}')
            ->innerJoin('{{%bot_chat}}', '{{%bot_chat_setting}}.chat_id = {{%bot_chat}}.id')
            ->innerJoin('{{%bot_chat_member}}', '{{%bot_chat_member}}.chat_id = {{%bot_chat}}.id')
            ->where([
                'status' => ChatMember::STATUS_CREATOR,
            ])
            ->all();

        foreach ($rows as $row) {
            $this->update(
                '{{%bot_chat_setting}}',
                ['updated_by' => $row['user_id']],
                ['id' => $row['id']],
            );
        }

        $this->createIndex(
            'idx-bot_chat_setting-updated_by',
            '{{%bot_chat_setting}}',
            'updated_by'
        );

        $this->addForeignKey(
            'fk-bot_chat_setting-updated_by',
            '{{%bot_chat_setting}}',
            'updated_by',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_setting-updated_by', '{{%bot_chat_setting}}');
        $this->dropIndex('idx-bot_chat_setting-updated_by', '{{%bot_chat_setting}}');

        $this->alterColumn('{{%bot_chat_setting}}', 'value', $this->string());
        $this->dropColumn('{{%bot_chat_setting}}', 'updated_by');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200802_123152_alter_bot_chat_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
