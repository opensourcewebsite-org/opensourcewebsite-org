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
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property array $_cronJobs
 * @property array $_cronJobsDb
 */
class CronJobConsole extends CronJob
{
    const EXCLUDE = 'Cron';

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
        $toDrop = array_diff($this->_cronJobsDb, $this->_cronJobs);

        return $this->deleteAll(['IN', 'name', $toDrop]);
    }
}
