<?php

namespace app\modules\bot\components\helpers;

use function Functional\group;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class MessageWithEntitiesConverter
{
    public static function toHtml(Message $message): string
    {
        $text = $message->getText();
        $entities = $message->getEntities();

        if (empty($text)) {
            return "";
        }

        $start_tags = [];
        $end_tags = [];
        if ( ! empty($entities)) {
            $start_tags = group($entities, fn ($e) => $e->getOffset());
            $end_tags = group($entities, fn ($e) => $e->getOffset() + $e->getLength());
        }
        $html = [];
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $i => $c) {
            if (array_key_exists($i, $start_tags)) {
                foreach ($start_tags[$i] as $tag) {
                    $html[] = self::startTagToText($tag, $text);
                }
            }
            $html[] = htmlspecialchars($c);
            if (array_key_exists($i + 1, $end_tags)) {
                foreach (array_reverse($end_tags[$i + 1]) as $tag) {
                    $html[] = self::endTagToText($tag);
                }
            }
        }
        $html_text = join('', $html);
        return preg_replace("%\[(.+)]\(<a href=\"[^\"]*\">(.+)</a>\)%u", '<a href="$2">$1</a>', $html_text);
    }

    private static function startTagToText(MessageEntity $tag, string $text): string
    {
        switch ($tag->getType()) {
            case MessageEntity::TYPE_BOLD:
                return "<b>";
            case MessageEntity::TYPE_ITALIC:
                return "<i>";
            case MessageEntity::TYPE_STRIKETHROUGH:
                return "<s>";
            case MessageEntity::TYPE_CODE:
                return "<code>";
            case MessageEntity::TYPE_TEXT_LINK:
                $url = $tag->getUrl();
                return "<a href=\"{$url}\">";
            case MessageEntity::TYPE_URL:
                $url = mb_substr($text, $tag->getOffset(), $tag->getLength(), 'UTF-8');
                return "<a href=\"{$url}\">";
            default:
                return "";
        }
    }

    private static function endTagToText(MessageEntity $tag): string
    {
        switch ($tag->getType()) {
            case MessageEntity::TYPE_BOLD:
                return "</b>";
            case MessageEntity::TYPE_ITALIC:
                return "</i>";
            case MessageEntity::TYPE_STRIKETHROUGH:
                return "</s>";
            case MessageEntity::TYPE_CODE:
                return "</code>";
            case MessageEntity::TYPE_TEXT_LINK:
            case MessageEntity::TYPE_URL:
                return "</a>";
            default:
                return "";
        }
    }
}
