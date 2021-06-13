<?php

namespace tests;

use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageEntity;

class MessageWithEntitiesConverterTest extends \Codeception\Test\Unit
{

    protected \UnitTester $tester;

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

        $message = self::message('text bold italic code [link title](example.com) strike', [
            self::entity(MessageEntity::TYPE_BOLD, 5, 4),
            self::entity(MessageEntity::TYPE_ITALIC, 10, 6),
            self::entity(MessageEntity::TYPE_CODE, 17, 4),
            self::entity(MessageEntity::TYPE_URL, 35, 11),
            self::entity(MessageEntity::TYPE_STRIKETHROUGH, 48, 6)
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
            self::entity(MessageEntity::TYPE_URL, 1, 11),
            self::entity(MessageEntity::TYPE_URL, 14, 11),
        ]);
        $expected = '<a href="example.com">example.com</a>';
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
