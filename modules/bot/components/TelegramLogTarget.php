<?php

namespace app\modules\bot\components;

use app\modules\bot\models\Bot;
use Yii;
use yii\log\Target;

class TelegramLogTarget extends Target
{
    public $botApi;
    public $botToken;
    public $chatId;
    public $cacheKey = 'last-telegram-log';
    public $cacheDuration = 24 * 60 * 60; // seconds

    public function init()
    {
        parent::init();

        $this->botToken = Yii::$app->params['bot']['token'] ?? null;
        $this->chatId = Yii::$app->params['bot']['osw_logs_group_id'] ?? null;

        if (empty($this->botToken) || empty($this->chatId)) {
            $this->enabled = false;
        }
    }

    /**
     * Sends log messages to specified telegram group.
     * @throws \Exception
     */
    public function export()
    {
        if (!$this->enabled) {
            return;
        }

        $bot = new Bot();
        $botApi = $bot->botApi;

        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages));
        $fileName = 'telegram-log-' . date('Y-m-d-H-i-s') . '.txt';

        $caption = explode("\n", $text)[0] ?: '';

        preg_match('/in (.+)$/', $caption, $matches);
        $errorPath = $matches[1] ?? '';

        // Check if this error has been sent recently
        if ($this->isErrorPathDuplicate($errorPath)) {
            return;
        }

        try {
            $botApi->sendDocument($this->chatId, new \CURLStringFile($text, $fileName), null, $caption);
            $this->cacheErrorPath($errorPath);
        } catch (\Exception $e) {
            Yii::warning($e);
        }
    }

    protected function isErrorPathDuplicate($errorPath)
    {
        $cache = Yii::$app->cache;
        $lastErrorPath = $cache->get($this->cacheKey);

        return $lastErrorPath == $errorPath;
    }

    protected function cacheErrorPath($errorPath)
    {
        $cache = Yii::$app->cache;
        $cache->set($this->cacheKey, $errorPath, $this->cacheDuration);
    }
}
