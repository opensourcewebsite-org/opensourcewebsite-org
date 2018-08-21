<?php
namespace tests\models;

use Codeception\Test\Unit;

class ContactFormTest extends Unit
{

    /**
     * @var \UnitTester
     */
    public $tester;
    private $model;

    public function testIsEmailSend()
    {
        $this->model = $this->getMockBuilder('app\models\ContactForm')
            ->setMethods(['validate'])
            ->getMock();

        $this->model->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->model->attributes = [
            'name' => 'demo',
            'email' => 'demo@example.com',
            'subject' => 'unit testing contact form',
            'body' => 'brief description about unit testing',
        ];

        expect_that($this->model->sendEmail('admin@example.com'));

        $this->tester->seeEmailIsSent();

        $email = $this->tester->grabLastSentEmail();
        expect($email->getTo())->hasKey('admin@example.com');
        expect($email->getFrom())->hasKey('noreply@opensourcewebsite.org');
        expect($email->getSubject())->equals('unit testing contact form');
        expect($email->toString())->contains('brief description about unit testing');
    }
}
