<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "rating".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $type
 * @property int $created_at
 *
 * @property User $user
 */
class Rating extends \yii\db\ActiveRecord
{
    const CONFIRM_EMAIL = 0;
    const TEAM = 1;
    const DONATE = 2;
    const USE_TELEGRAM_BOT = 3;
    const UNRANKED = 'unranked';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rating';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'type', 'created_at'], 'integer'],
            [['amount', 'type'], 'required'],
            [['created_at'], 'default', 'value' => time()],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'amount' => Yii::t('app', 'Amount'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return integer The total rating in rating table
     */
    public static function getTotalRating()
    {
        $totalRating = static::find()->select('sum(amount)')->scalar();
        return $totalRating != null ? $totalRating : 0;
    }

    public static function getRank($userId)
    {
        $groupQueryResult = (new Query)
            ->select([
                '`user`.id',
                'balance' => 'CASE WHEN SUM(`rating`.`amount`) IS NULL THEN 0 ELSE SUM(`rating`.`amount`) END',
            ])
            ->from(User::tableName())
            ->leftJoin(Rating::tableName() . ' ON `user`.`id` = `rating`.`user_id`')
            ->groupBy('`user`.`id`')
            ->orderBy(['balance' => SORT_DESC, 'user.created_at' => SORT_ASC])
            ->all();

        $total = count($groupQueryResult);

        $rank = static::UNRANKED;
        foreach ($groupQueryResult as $index => $row) {
            if ($row['id'] == $userId) {
                $rank = $index + 1;
            }
        }

        return [$total, $rank];
    }
}
