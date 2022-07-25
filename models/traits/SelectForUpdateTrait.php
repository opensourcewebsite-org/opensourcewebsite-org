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
     * @param self[]|ActiveQuery $source
     *
     * @return self[]
     */
    public static function findAllForUpdate($source): array
    {
        $indexBy = ($source instanceof ActiveQuery) ? $source->indexBy : null;

        /** @var self[] $models */
        $models = static::findBySql(static::sqlForUpdate($source))
            ->indexBy($indexBy)
            ->all();

        foreach ($models as $model) {
            $model->setFoundForUpdate();
        }

        return $models;
    }

    /**
     * @param self|ActiveQuery $source
     *
     * @return self|null
     */
    public static function findOneForUpdate($source): ?self
    {
        if ($source instanceof ActiveRecord) {
            $source = [$source];
        }
        /** @var self $model */
        $model = static::findBySql(static::sqlForUpdate($source))->one();

        if ($model) {
            $model->setFoundForUpdate();
        }

        return $model;
    }

    private static function clearFoundForUpdate(): void
    {
        static::$foundForUpdate = [];
    }

    /**
     * @param static[]|ActiveQuery $source
     *
     * @return string
     */
    private static function sqlForUpdate($source): string
    {
        static::requireTransaction();

        if ($source instanceof ActiveQuery) {
            $query = $source;
        } else {
            /** @var ActiveQuery|SelfSearchTrait $query */
            $query = static::find();

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
        if (!static::getDb()->getTransaction()) {
            throw new InvalidCallException('The method must be called in DB transaction');
        }
    }

    private function setFoundForUpdate(): void
    {
        if (static::$foundForUpdate === null) {
            static::$foundForUpdate = [];

            $handler = static function () {
                static::clearFoundForUpdate();
            };

            static::getDb()->on(Connection::EVENT_COMMIT_TRANSACTION, $handler);
            static::getDb()->on(Connection::EVENT_ROLLBACK_TRANSACTION, $handler);
        }

        $key = uniqid('', true);
        static::$foundForUpdate[$key] = true;
        $this->foundForUpdateKey = $key;
    }

    public function isFoundForUpdate(): bool
    {
        return isset(static::$foundForUpdate[$this->foundForUpdateKey]);
    }
}
