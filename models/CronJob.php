<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "cron_job".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class CronJob extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cron_job';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['status', 'created_at', 'updated_at', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string'],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'name'       => Yii::t('app', 'Name'),
            'status'     => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param CronJob[] $factory
     * @param int|null $id
     *
     * @return string
     */
    public static function renderMenu(array $factory, $id = null)
    {
        $active = $id;
        $html = Html::a(
            'All',
            ['index'],
            [
                'class' => 'btn btn-outline-primary mr-2' . ((is_null($active)) ? ' active' : ''),
                'title' => 'All',
            ]
        );

        foreach ($factory as $job) {
            $html .= Html::a(
                $job->name,
                ['view', 'id' => $job->id],
                [
                    'class' => 'btn btn-outline-primary mr-2' . (($active == $job->id) ? ' active' : ''),
                    'title' => $job->name,
                ]
            );
        }

        return $html;
    }
}
