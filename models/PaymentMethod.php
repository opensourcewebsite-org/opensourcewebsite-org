<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use app\helpers\UrlTrimmer;
use Yii;

/**
 * This is the model class for table "payment_method".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property string|null $url
 */
class PaymentMethod extends ActiveRecord
{
    public const TYPE_EMONEY = 0;
    public const TYPE_BANK = 1;
    public const TYPE_STABLECOIN = 2;

    public static $types = [
        self::TYPE_EMONEY => 'E-Money',
        self::TYPE_BANK => 'Bank',
        self::TYPE_STABLECOIN => 'Stablecoin',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%payment_method}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'integer'],
            [['name', 'url'], 'string', 'max' => 255],
            [
                [
                    'url',
                ],
                'filter',
                'skipOnEmpty' => true,
                'filter' => [
                    new UrlTrimmer(),
                    'trim',
                ],
            ],
            [
                [
                    'url',
                ],
                'url',
                'defaultScheme' => Yii::$app->params['defaultScheme'] ?? 'https',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'type' => Yii::t('app', 'Type'),
            'url' => Yii::t('app', 'Website'),
        ];
    }

    public function getCurrencies()
    {
        return $this->hasMany(Currency::class, ['id' => 'currency_id'])
                ->viaTable('payment_method_currency', ['payment_method_id' => 'id']);
    }

    public function getTypeName(): string
    {
        return self::$types[$this->type];
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->name;
    }
}
