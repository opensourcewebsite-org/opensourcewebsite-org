<?php

namespace app\modules\bot\components;

use app\modules\bot\components\api\BotApi;
use app\modules\bot\components\helpers\Document;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\commands\SendDocumentCommand;
use Yii;
use yii\log\Target;

class TelegramLogTarget extends Target
{
    public $botApi;
    public $botToken;
    public $chatId;

    public function init()
    {
        parent::init();

        $this->botToken = Yii::$app->params['bot']['token'] ?? null;
        $this->chatId = Yii::$app->params['bot']['osw_logs_group_id'] ?? null;

        if (empty($this->botToken) || empty($this->chatId)) {
            $this->enabled = false;
        } else {
            $this->botApi = new BotApi($this->botToken);
        }
    }

    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages));
        $this->sendDocument($text);
    }

    protected function sendDocument($text)
    {
        if (!$this->enabled) {
            return;
        }

        # TODO

        // $filePath = Yii::getAlias('@runtime/logs/telegram-log.txt');
        // file_put_contents($filePath, $text);

        // $document = new Document($filePath);
        // $caption = new MessageText("Error log");

        // $this->botApi->SendDocument($this->chatId, $document, $caption);
    }
}
