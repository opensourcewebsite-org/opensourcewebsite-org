<?php

namespace app\models\traits;

use app\models\queries\traits\SelfSearchTrait;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;

trait SelectForUpdateTrait
{
    private static $foundForUpdate;

    private $foundForUpdateKey;

    /**
     * @param self[]|ActiveRecord[]|ActiveQuery $source
     *
     * @return self[]
     */
    public static function findAllForUpdate($source): array
    {
        /** @var self[] $models */
        $models = self::findBySql(self::sqlForUpdate($source))->all();
        foreach ($models as $model) {
            $model->setFoundForUpdate();
        }

        return $models;
    }

    /**
     * @param self|ActiveRecord|ActiveQuery $source
     *
     * @return self|null
     */
    public static function findOneForUpdate($source): ?self
    {
        if ($source instanceof ActiveRecord) {
            $source = [$source];
        }
        /** @var self $model */
        $model = self::findBySql(self::sqlForUpdate($source))->one();

        if ($model) {
            $model->setFoundForUpdate();
        }

        return $model;
    }

    private static function clearFoundForUpdate(): void
    {
        self::$foundForUpdate = [];
    }

    /**
     * @param self[]|ActiveQuery $source
     *
     * @return string
     */
    private static function sqlForUpdate($source): string
    {
        self::requireTransaction();

        if ($source instanceof ActiveQuery) {
            $query = $source;
        } else {
            /** @var ActiveQuery|SelfSearchTrait $query */
            $query = self::find();

            $traits = class_uses($query);
            if (!isset($traits[SelfSearchTrait::class])) {
                throw new InvalidCallException($query::className() . ' require to use trait ' . SelfSearchTrait::class);
            }

            $query->models($source);
        }

        return $query->createCommand()->getRawSql() . ' FOR UPDATE';
    }

    private static function requireTransaction(): void
    {
        if (!self::getDb()->getTransaction()) {
            throw new InvalidCallException('The method must be called in DB transaction');
        }
    }

    private function setFoundForUpdate(): void
    {
        if (self::$foundForUpdate === null) {
            self::$foundForUpdate = [];

            $handler = static function () { static::clearFoundForUpdate(); };
            self::getDb()->on(Connection::EVENT_COMMIT_TRANSACTION, $handler);
            self::getDb()->on(Connection::EVENT_ROLLBACK_TRANSACTION, $handler);
        }

        $key = uniqid('', true);
        self::$foundForUpdate[$key] = true;
        $this->foundForUpdateKey = $key;
    }

    private function isFoundForUpdate(): bool
    {
        return isset(self::$foundForUpdate[$this->foundForUpdateKey]);
    }
}
