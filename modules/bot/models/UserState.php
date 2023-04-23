<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\behaviors\JsonBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class UserState
 *
 * @package app\modules\bot\models
 */
class UserState extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%bot_user_state}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['value'], 'string'],
            [['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => JsonBehavior::class,
                'attributes' => [
                      'obj' => 'value',
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => 'User ID',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getName()
    {
        return $this->getItem('name');
    }

    public function setName(?string $value)
    {
        $this->setItem('name', $value);
    }

    public function getBackRoute()
    {
        return $this->getItem('backRoute');
    }

    public function setBackRoute(?string $value)
    {
        $this->setItem('backRoute', $value);
    }

    public function clearBackRoute()
    {
        $this->clearItem('backRoute');
    }

    /**
     * @param string $name
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getItem(string $name, $defaultValue = null)
    {
        $this->name = $name;
        $this->obj = null;
        $this->setIsNewRecord(true);
        $this->refresh();

        if (is_subclass_of($name, ActiveRecord::class)) {
            try {

                $model = \Yii::createObject([
                    'class' => $name,
                ]);

                $model->setAttributes($this->obj, false);

                return $model;
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage());
                return null;
            }
        }

        return $this->obj ?? $defaultValue;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function setItem($name, $value = null)
    {
        $this->name = $name;
        $attributes = $value;

        if ($name instanceof ActiveRecord) {
            $this->name = get_class($name);
        }

        $this->setIsNewRecord(true);
        $this->refresh();

        // This condition for different way to save model
        // first valiant is setItem(<instance_of_ActiveRecord>, null)
        // second variant is setItem(<classname_of_ActiveRecord_instance>, <object_of_subclass_of_ActiveRecord>)
        // in this cases we need iterate it attributes
        if ($name instanceof ActiveRecord || (is_subclass_of($name, ActiveRecord::class) && get_class($value) == $name)) {
            $attributes = [];

            foreach ($name as $key => $value) {
                $attributes[$key] = $value;
            }
        }

        $this->obj = $attributes;
        $this->save();
    }

    /**
     * @param array $values
     * {@inheritdoc}
     */
    public function setItems($values)
    {
        foreach ($values as $key => $value) {
            if (is_int($key) && $value instanceof ActiveRecord) {
                $this->setItem($value);
                continue;
            }
            $this->setItem($key, $value);
        }
    }

    /**
     * @param string $name
     */
    public function clearItem(string $name)
    {
        $this->name = $name;
        $this->setIsNewRecord(true);
        $this->refresh();
        $this->delete();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isItemExists(string $name)
    {
        $this->name = $name;
        $this->setIsNewRecord(true);
        $this->refresh();
        return isset($this->obj);
    }

    public function reset(string $name = null)
    {
        $this->deleteAll(['user_id' => $this->user_id]);
    }

    public static function fromUser(User $user)
    {
        $state = new UserState();
        $state->user_id = $user->id;
        return $state;
    }
}
