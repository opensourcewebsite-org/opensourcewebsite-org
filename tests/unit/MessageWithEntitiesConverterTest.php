<?php

namespace tests;

use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use Codeception\Test\Unit;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;
use UnitTester;

class MessageWithEntitiesConverterTest extends Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    static private function message($text, $entities) {
        $result = new Message();
        $result->setText($text);
        $result->setEntities($entities);
        return $result;
    }

    static private function entity($type, $offset, $length, $url = null) {
        $result = new MessageEntity();
        $result->setType($type);
        $result->setOffset($offset);
        $result->setLength($length);
        if (isset($url)) {
            $result->setUrl($url);
        }
        return $result;
    }

    // tests
    public function testToHtml()
    {
        $message = self::message('some text', []);
        expect(MessageWithEntitiesConverter::toHtml($message))->equals('some text');

        $text = 'text bold italic code [link title](example.com) strike';
        $message = self::message($text, [
            self::entity(MessageEntity::TYPE_BOLD, strpos($text, 'bold'), strlen('bold')),
            self::entity(MessageEntity::TYPE_ITALIC, strpos($text, 'italic'), strlen('italic')),
            self::entity(MessageEntity::TYPE_CODE, strpos($text, 'code'), strlen('code')),
            self::entity(MessageEntity::TYPE_URL, strpos($text, 'example.com'), strlen('example.com')),
            self::entity(MessageEntity::TYPE_STRIKETHROUGH, strpos($text, 'strike'), strlen('strike'))
        ]);
        $expected = 'text <b>bold</b> <i>italic</i> <code>code</code> <a href="example.com">link title</a> <s>strike</s>';
        expect(MessageWithEntitiesConverter::toHtml($message))->equals($expected);

        $message = self::message('🍪 text 🍩💚🧡🎂🍧 text text 💚🧡', [
            self::entity(MessageEntity::TYPE_BOLD, 8, 10),
            self::entity(MessageEntity::TYPE_ITALIC, 24, 4)
        ]);
        $expected = '🍪 text <b>🍩💚🧡🎂🍧</b> text <i>text</i> 💚🧡';
        expect(MessageWithEntitiesConverter::toHtml($message))->equals($expected);


        $message = self::message('[example.com](example.com)', [
            self::entity(MessageEntity::TYPE_URL, 1, strlen('example.com')),
            self::entity(MessageEntity::TYPE_URL, 14, strlen('example.com')),
        ]);
        $expected = '<a href="example.com">example.com</a>';
        expect(MessageWithEntitiesConverter::toHtml($message))->equals($expected);

        $message = self::message(
            'some text [text](https://example.com/) or [similar](https://example.com/projects? omet = ipsum # lorem) text [some](https://example.com/) text',
            [
                self::entity(MessageEntity::TYPE_URL, 17, strlen('https://example.com/')),
                self::entity(MessageEntity::TYPE_URL, 52, strlen('https://example.com/projects')),
                self::entity(MessageEntity::TYPE_URL, 116, strlen('https://example.com/')),
            ]
        );
        $expected = 'some text <a href="https://example.com/">text</a> or [similar](<a href="https://example.com/projects">https://example.com/projects</a>? omet = ipsum # lorem) text <a href="https://example.com/">some</a> text';
        expect(MessageWithEntitiesConverter::toHtml($message))->equals($expected);
    }

    public function testFromHtml()
    {
        expect(MessageWithEntitiesConverter::fromHtml('some text'))->equals('some text');

        $html = 'text <b>bold</b> <i>italic</i> <code>code</code> <a href="example.com">link title</a> <s>strike</s>';
        $expected = 'text **bold** __italic__ `code` [link title](example.com) ~~strike~~';
        expect(MessageWithEntitiesConverter::fromHtml($html))->equals($expected);

        $html = '🍪 text <b>🍩💚🧡🎂🍧</b> text <i>text</i> 💚🧡';
        $expected = '🍪 text **🍩💚🧡🎂🍧** text __text__ 💚🧡';
        expect(MessageWithEntitiesConverter::fromHtml($html))->equals($expected);

        $html = '<a href="example.com">example.com</a>';
        $expected = 'example.com';
        expect(MessageWithEntitiesConverter::fromHtml($html))->equals($expected);
    }
}
