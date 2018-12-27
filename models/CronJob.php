<?php
namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "cron_job".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property array $_cronJobsFiles
 * @property array $_cronJobsDb
 */
class CronJob extends ActiveRecord
{
    const EXCLUDE = 'Cron';

    private $_cronJobsFiles = [];
    private $_cronJobsDb = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->checkFolder();
        $this->checkDatabase();
    }

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
            [['name'], 'string', 'max' => 127],
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
     * Checking folder and collecting jobs data from files
     *
     * @return void
     */
    protected function checkFolder()
    {
        $folder = FileHelper::findFiles('commands', [
            'recursive' => false,
            'only'      => ['*.php']
        ]);

        if (count($folder) > 0) {
            foreach ($folder as $file) {
                $start = mb_strpos($file, '/') + 1;
                $this->_cronJobsFiles[] = mb_substr($file, $start, -14);
            }
        }
    }

    /**
     * collecting data from db
     *
     * @return void
     */
    protected function checkDatabase()
    {
        $this->_cronJobsDb = $this->find()->select('name')->column();
    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function add()
    {
        foreach ($this->_cronJobsFiles as $name) {
            if (static::EXCLUDE == $name) {
                continue;
            }

            if (!$this->findOne(['name' => $name])) {
                $model = clone $this;
                $model->setAttributes([
                    'name'   => $name,
                    'status' => 1
                ]);

                if ($model->validate() && !$model->save()) {
                    throw new ServerErrorHttpException(implode(', ', $model->getErrors()));
                }
            }
        }

        return true;
    }

    /**
     * @return int number of rows deleted
     */
    public function clear()
    {
        $toDrop = array_diff($this->_cronJobsDb, $this->_cronJobsFiles);

        return $this->deleteAll(['IN', 'name', $toDrop]);
    }
}
