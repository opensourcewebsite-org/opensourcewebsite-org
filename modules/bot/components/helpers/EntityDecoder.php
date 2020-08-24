<?php
/**
 * This class decode style entities from Telegram bot messages (bold, italic, etc.) in text with inline entities that duplicate (when possible) the
 * exact style the message has originally when was sended to the bot.
 * All this work is necessary because Telegram returns offset and length of the entities in UTF-16 code units that they've been hard to decode correctly in PHP
 *
 * Inspired By: https://github.com/php-telegram-bot/core/issues/544#issuecomment-564950430
 * Emoji detection (with some customizations) from: https://github.com/aaronpk/emoji-detector-php
 *
 * Example usage:
 * $entity_decoder = new EntityDecoder('HTML', 'API_KEY_STRING');
 * $decoded_text = $entity_decoder->decode($message);
 *
 * @author LucaDevelop
 * @access public
 * @see https://github.com/LucaDevelop/telegram-entities-decoder
 */

namespace app\modules\bot\components\helpers;

use app\modules\bot\components\api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class EntityDecoder
{
    private $entities;
    private $text;
    private $style;
    private $without_cmd;
    private $offset_correction;

    /**
     * @param Message $message     Message object to reconstruct Entities from.
     * @param string  $style       Either 'html' or 'markdown'.
     * @param bool    $without_cmd If the bot command should be included or not.
     */
    public function __construct(Message $message, string $style = 'html', bool $without_cmd = false)
    {
        $this->entities    = $message->getEntities();
        $this->text        = $message->getText($without_cmd);
        $this->style       = $style;
        $this->without_cmd = $without_cmd;
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
        if ($this->without_cmd && $first_entity->getType() === 'bot_command') {
            $this->offset_correction = ($first_entity->getLength() + 1);
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
            $str_split_unicode = preg_split('/(.)/us', $this->text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            // Generate an array of UTF-16 encoded string lengths, which is necessary
            // to correct the offset and length values of special characters, like Emojis.
            $text_byte_counts = array_map(function ($char) {
                return strlen(mb_convert_encoding($char, 'UTF-16', 'UTF-8')) / 2;
            }, $str_split_unicode);
        }

        $offset = $entity->getOffset() - $this->offset_correction;
        $length = $entity->getLength();

        $offset += $offset - array_sum(array_slice($text_byte_counts, 0, $offset));
        $length += $length - array_sum(array_slice($text_byte_counts, $offset, $length));

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

        $text_bit = $this->getTextBit($entity, $offset, $length);

        // Replace text bit.
        return mb_substr($text, 0, $offset) . $text_bit . mb_substr($text, $offset + $length);
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
        $text_bit = mb_substr($this->text, $offset, $length);

        switch ($type) {
            case 'text_mention':
                $text_bit = sprintf($filler, $text_bit, $entity->getUser()->getId());
                break;
            case 'text_link':
                $text_bit = sprintf($filler, $text_bit, $entity->getUrl());
                break;
            case 'bold':
            case 'italic':
            case 'code':
            case 'strikethrough':
            case 'pre':
                $text_bit = sprintf($filler, $text_bit);
                break;
            default:
                break;
        }

        return $text_bit;
    }
}
