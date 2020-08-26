<?php

namespace app\modules\bot\components\helpers;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class EntityDecoder
{
    private $entities;
    private $text;
    private $style;
    private $withoutCmd;
    private $offsetCorrection;

    /**
     * @param Message $message     Message object to reconstruct Entities from.
     * @param string  $style       Either 'html' or 'markdown'.
     * @param bool    $withoutCmd If the bot command should be included or not.
     */
    public function __construct(Message $message, string $style = 'html', bool $withoutCmd = false)
    {
        $this->entities    = $message->getEntities();
        $this->text        = $message->getText($withoutCmd);
        $this->style       = $style;
        $this->withoutCmd = $withoutCmd;
    }

    public function decode(): string
    {
        if (empty($this->entities)) {
            return $this->text;
        }

        $this->fixBotCommandEntity();

        // Reverse entities and start replacing bits from the back, to preserve offset positions.
        foreach (array_reverse($this->entities) as $entity) {
            $this->text = $this->decodeEntity($entity, $this->text);
        }

        return $this->text;
    }

    protected function fixBotCommandEntity(): void
    {
        // First entity would be the bot command, remove if necessary.
        $first_entity = reset($this->entities);
        if ($this->withoutCmd && $first_entity->getType() === 'bot_command') {
            $this->offsetCorrection = ($first_entity->getLength() + 1);
            array_shift($this->entities);
        }
    }

    /**
     * @param MessageEntity $entity
     *
     * @return array
     */
    protected function getOffsetAndLength(MessageEntity $entity): array
    {
        static $text_byte_counts;

        if (!$text_byte_counts) {
            // https://www.php.net/manual/en/function.str-split.php#115703
            $strSplitUnicode = preg_split('/(.)/us', $this->text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            // Generate an array of UTF-16 encoded string lengths, which is necessary
            // to correct the offset and length values of special characters, like Emojis.
            $textByteCounts = array_map(function ($char) {
                return strlen(mb_convert_encoding($char, 'UTF-16', 'UTF-8')) / 2;
            }, $strSplitUnicode);
        }

        $offset = $entity->getOffset() - $this->offsetCorrection;
        $length = $entity->getLength();

        $offset += $offset - array_sum(array_slice($textByteCounts, 0, $offset));
        $length += $length - array_sum(array_slice($textByteCounts, $offset, $length));

        return [$offset, $length];
    }

    /**
     * @param string $style
     * @param string $type
     *
     * @return string
     */
    protected function getFiller(string $style, string $type): string
    {
        $fillers = [
            'html'     => [
                'text_mention' => '<a href="tg://user?id=%2$s">%1$s</a>',
                'text_link'    => '<a href="%2$s">%1$s</a>',
                'bold'         => '<b>%s</b>',
                'italic'       => '<i>%s</i>',
                'code'         => '<code>%s</code>',
                'pre'          => '<pre>%s</pre>',
            ],
            'markdown' => [
                'text_mention' => '[%1$s](tg://user?id=%2$s)',
                'text_link'    => '[%1$s](%2$s)',
                'bold'         => '**%s**',
                'italic'       => '_%s_',
                'code'         => '`%s`',
                'strikethrough' => '~~%s~~',
                'pre'          => '```%s```',
            ],
        ];

        return $fillers[$style][$type] ?? '';
    }

    /**
     * Decode an entity into the passed string.
     *
     * @param MessageEntity $entity
     * @param string        $text
     *
     * @return string
     */
    private function decodeEntity(MessageEntity $entity, string $text): string
    {
        [$offset, $length] = $this->getOffsetAndLength($entity);

        $textBit = $this->getTextBit($entity, $offset, $length);

        // Replace text bit.
        return mb_substr($text, 0, $offset) . $textBit . mb_substr($text, $offset + $length);
    }

    /**
     * @param MessageEntity $entity
     * @param int           $offset
     * @param int           $length
     *
     * @return false|string
     */
    private function getTextBit(MessageEntity $entity, $offset, $length)
    {
        $type     = $entity->getType();
        $filler   = $this->getFiller($this->style, $type);
        $textBit = mb_substr($this->text, $offset, $length);

        switch ($type) {
            case 'text_mention':
                $textBit = sprintf($filler, $textBit, $entity->getUser()->getId());
                break;
            case 'text_link':
                $textBit = sprintf($filler, $textBit, $entity->getUrl());
                break;
            case 'bold':
            case 'italic':
            case 'code':
            case 'strikethrough':
            case 'pre':
                $textBit = sprintf($filler, $textBit);
                break;
            default:
                break;
        }

        return $textBit;
    }
}