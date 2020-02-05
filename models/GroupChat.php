<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "group_chats".
 *
 * @property int $_id
 * @property int $owner_id
 * @property int $tg_id
 * @property string $title
 * @property int $mode
 * @property boolean $enabled
 */
class GroupChat extends ActiveRecord
{
    const MODE_GO = 0;
    const MODE_STOP = 1;
    const TG_ID_KEY = 'tg_id';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'owner_id' => 'OnwerID',
            'tg_id' => 'TGID',
            'title' => 'Title',
            'mode' => 'Mode',
            'enabled' => 'Enabled',
        ];
    }

    public function getId() {
        return $this->_id;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }

    public function setOwnerid($ownerId) {
        $this->owner_id = $ownerId;
    }

    public function getTgId() {
        return $this->tg_id;
    }

    public function setTgId($tgId) {
        $this->tg_id = $tgId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getMode() {
        return $this->mode;
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}
