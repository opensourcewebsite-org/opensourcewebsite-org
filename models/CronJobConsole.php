<?php
namespace app\models;

use Yii;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "cron_job".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class CronJobConsole extends CronJob
{
    public const EXCLUDE = 'Cron';
    public const STATUS_ON = 1;

    private $_cronJobs = [];
    private $_cronJobsDb = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->checkDatabase();
    }

    public function setCronJobs($jobs)
    {
        $this->_cronJobs = $jobs;
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
        foreach ($this->_cronJobs as $name) {
            if (static::EXCLUDE == $name) {
                continue;
            }

            if (!$this->findOne(['name' => $name])) {
                $model = clone $this;
                $model->setAttributes([
                    'name'   => $name,
                    'status' => self::STATUS_ON,
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
        $toDrop = array_diff($this->_cronJobsDb, $this->_cronJobs);

        return $this->deleteAll(['IN', 'name', $toDrop]);
    }
}
