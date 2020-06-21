<?php

namespace app\behaviors;

use yii\base\Event;

/**
 * Class TimestampBehavior
 *
 * ```php
 * use yii\db\Expression;
 *
 * public function behaviors()
 * {
 *     return [
 *         'TimestampBehavior' => [
 *             'class' => TimestampBehavior::class,
 *             'createdAtAttribute' => 'created',
 *             'updatedAtAttribute' => 'updated',
 *             'value' => new Expression('NOW()'),
 *         ],
 *     ];
 * }
 * ```
 *
 * @package app\behaviors
 */
class TimestampBehavior extends \yii\behaviors\TimestampBehavior
{
    /**
     * @var string
     */
    public $createdAtAttribute = 'created_at';

    /**
     * @var string
     */
    public $updatedAtAttribute = 'renewed_at';

    /**
     * @param Event $event
     *
     * @return string
     */
    protected function getValue($event)
    {
        return $this->value ?: time();
    }
}
