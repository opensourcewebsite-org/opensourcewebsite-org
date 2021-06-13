<?php

namespace app\modules\bot\components\helpers;

use function Functional\flatten;
use function Functional\group;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class MessageWithEntitiesConverter
{
    /**
     * Convert message with {@see \TelegramBot\Api\Types\MessageEntity entities} to HTML format
     *
     * Also escapes HTML special characters in message text.
     *
     * Doesn't support underline, code blocks and mentions.
     *
     * @param Message $message telegram message, containing some text with markup
     * @return string HTML representation of $message
     */
    public static function toHtml(Message $message): string
    {
        $text = $message->getText();
        $entities = $message->getEntities();

        if (empty($text)) {
            return '';
        }
        if (empty($entities)) {
            return htmlspecialchars($text);
        }
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $entities = self::correctEntities($characters, $entities);

        $start_tags = group($entities, fn ($e) => $e->getOffset());
        $end_tags = group($entities, fn ($e) => $e->getOffset() + $e->getLength());

        $html = [];
        foreach ($characters as $i => $c) {
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
        return preg_replace('%\[(.+)]\(<a href=\"[^\"]*\">(.+)</a>\)%u', '<a href="$2">$1</a>', $html_text);
    }

    /**
     * Convert HTML {@see https://core.telegram.org/api/entities with restrictions} to {@see https://github.com/telegramdesktop/tdesktop/issues/330#issuecomment-326881955 Telegram Markdown}
     *
     * Doesn't support `<pre>`, `<u>` tags.
     *
     * @param string $text Telegram-compliant HTML code
     * @return string Markdown representation of $text
     */
    public static function fromHtml(string $text): string
    {
        return preg_replace([
            '/<b>/u',
            '/<i>/u',
            '/<s>/u',
            '/<code>/u',
            '%</b>%u',
            '%</i>%u',
            '%</s>%u',
            '%</code>%u',
            '%<a +href="(.*)">(.*)</a>%u',
        ], [
            '**',
            '__',
            '~~',
            '`',
            '**',
            '__',
            '~~',
            '`',
            '[$2]($1)',
        ], $text);
    }

    private static function startTagToText(MessageEntity $tag, string $text): string
    {
        switch ($tag->getType()) {
            case MessageEntity::TYPE_BOLD:
                return '<b>';
            case MessageEntity::TYPE_ITALIC:
                return '<i>';
            case MessageEntity::TYPE_STRIKETHROUGH:
                return '<s>';
            case MessageEntity::TYPE_CODE:
                return '<code>';
            case MessageEntity::TYPE_TEXT_LINK:
                $url = $tag->getUrl();
                return "<a href=\"{$url}\">";
            case MessageEntity::TYPE_URL:
                $url = mb_substr($text, $tag->getOffset(), $tag->getLength(), 'UTF-8');
                return "<a href=\"{$url}\">";
            default:
                return '';
        }
    }

    private static function endTagToText(MessageEntity $tag): string
    {
        switch ($tag->getType()) {
            case MessageEntity::TYPE_BOLD:
                return '</b>';
            case MessageEntity::TYPE_ITALIC:
                return '</i>';
            case MessageEntity::TYPE_STRIKETHROUGH:
                return '</s>';
            case MessageEntity::TYPE_CODE:
                return '</code>';
            case MessageEntity::TYPE_TEXT_LINK:
            case MessageEntity::TYPE_URL:
                return '</a>';
            default:
                return '';
        }
    }

    private static function utf16CodePointsLength(string $char): int
    {
        $chunks = str_split(bin2hex(mb_convert_encoding($char, 'UTF-16')), 4);
        return count($chunks);
    }

    /**
     * Offset and length correction for entities
     *
     * Needed to handle UTF-16 characters, for example, emoji.
     *
     * @param string[] $characters
     * @param MessageEntity[] $entities
     * @return MessageEntity[] $entities
     */
    private static function correctEntities(array $characters, array $entities) {
        $tagGroups = group($entities, fn ($e) => $e->getOffset());

        // Offset correction for entities
        {
            $offsetCorrection = 0;
            $codeLengths = [];
            foreach ($characters as $i => $c) {
                if (array_key_exists($i + $offsetCorrection, $tagGroups)) {
                    foreach ($tagGroups[$i + $offsetCorrection] as &$tag) {
                        $tag->setOffset($tag->getOffset() - $offsetCorrection);
                    }
                }
                $len = self::utf16CodePointsLength($c);
                $codeLengths[] = $len;
                $offsetCorrection += $len - 1;
            }
        }

        // Length correction for entities
        foreach($tagGroups as &$tagGroup) {
            foreach ($tagGroup as &$tag) {
                $remainingLength = $tag->getLength();
                $lengthCorrection = 0;
                foreach (array_slice($codeLengths, $tag->getOffset()) as $len) {
                    if ($remainingLength <= 0) {
                        break;
                    }
                    $lengthCorrection += $len - 1;
                    $remainingLength -= $len;
                }
                $tag->setLength($tag->getLength() - $lengthCorrection);
            }
        }

        return flatten($tagGroups);
    }
}
