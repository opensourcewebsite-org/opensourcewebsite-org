<?php

namespace app\behaviors;

use app\exceptions\BehaviorException;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

/**
 * Class SetAttributeValueBehavior
 *
 * @package app\behaviors
 */
class SetAttributeValueBehavior extends AttributeBehavior
{
    /**
     * @var string
     */
    public $attribute = null;

    /** @inheritDoc */
    public function init()
    {
        parent::init();

        if (!$this->attribute) {
            throw new BehaviorException("attribute should be set");
        }

        if (empty($this->attributes)) {
            $this->attributes = [
                ActiveRecord::EVENT_BEFORE_INSERT => [$this->attribute],
            ];
        }
    }
}
