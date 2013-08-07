<?php

namespace EE\ExtraMailerBundle\Test\Service;

use EE\ExtraMailerBundle\Service\ExtraMailer;

class ExtraMailerTest extends \PHPUnit_Framework_TestCase
{
    public function dummyTrans($str)
    {
        return $str;
    }

    private $swift;
    private $container;
    private $templates;
    private $twig;

    protected function setUp()
    {
        $this->swift = $this->getMock('Swift_Mailer', array('send'), array(new \Swift_NullTransport()));

        $this->container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->container->setParameter('ee_extra_mailer.from_email.address', 'test@example.com');
        $this->container->setParameter('ee_extra_mailer.from_email.sender_name', 'Test Example');

        $this->templates = array(
            'EEExtraMailerBundle::layout.txt.twig' => file_get_contents(
                __DIR__ . '/../Resources/views/layout.txt.twig'
            ),
            'EEExtraMailerBundle::layout.html.twig' => file_get_contents(
                __DIR__ . '/../Resources/views/layout.html.twig'
            ),
            'EEExtraMailerBundle::noreplyFooter.html.twig' => file_get_contents(
                __DIR__ . '/../Resources/views/noreplyFooter.html.twig'
            ),
            'EEExtraMailerBundle::noreplyFooter.txt.twig' => file_get_contents(
                __DIR__ . '/../Resources/views/noreplyFooter.txt.twig'
            ),
            'demo' => file_get_contents(
                __DIR__ . '/../Resources/views/Demo/sample.email.twig'
            ),
            'demoTXT' => '{% block subject %}Sample subject for {{name}} in email sent by EEExtraMailer{% endblock %}{% block body_text %}Hi {{ name }}, This is sample email sent by EEExtraMailer{% endblock %}{% block body_html %}<p>Hi {{ name }}, This is sample email sent by EEExtraMailer</p>{% endblock %}'
        );

        $this->twig = $this->getMock('Twig_Environment', null, array(new \Twig_Loader_Array($this->templates)));
        $this->twig->addFilter('trans*', new \Twig_Filter_Function(array(&$this, 'dummyTrans')));
    }

    public function testSendMessage()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);
        $this->swift->expects($this->once())->method('send')->will($this->returnValue(1));
        $result = $extraMailer->sendMessage(
            array('recipient@example.com' => 'Recipient Name'),
            'demo',
            array(),
            array('attachments' => array(__DIR__ . '/../Resources/views/Demo/sample.email.twig'))
        );

        $this->assertEquals(1, $result);
    }

    public function testSendMessageTxt()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);
        $this->swift->expects($this->once())->method('send')->will($this->returnValue(1));
        $result = $extraMailer->sendMessage(
            array('recipient@example.com' => 'Recipient Name'),
            'demoTXT',
            array(),
            array('attachments' => array(__DIR__ . '/../Resources/views/Demo/sample.email.twig'))
        );

        $this->assertEquals(1, $result);
    }


    public function testSendMessageToManyWithCommonContext()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);

        $recipients = array(
            'somename@example.com' => 'Somename',
            'someothername@example.com' => 'Someothername'
        );

        $this->swift
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue(count($recipients)));


        $result = $extraMailer->sendMessage(
            $recipients,
            'demo',
            array('name' => 'lol'),
            array('attachments' => array(__DIR__ . '/../Resources/views/Demo/sample.email.twig'))
        );

        $this->assertEquals(count($recipients), $result);
    }

    public function testPrepareMessagesToManyWithSeparateContexts()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);

        $recipients = array(
            'somename@example.com' => 'Somename',
            'someothername@example.com' => 'Someothername'
        );
        $contexts = array(
            array('name' => 'Example Name 1'),
            array('name' => 'Example Name 2')
        );

        $messagesArray = $extraMailer->composeMessages(
            $recipients,
            'demoTXT',
            $contexts
        );

        $expectedSubjectsArray = array(
            'Sample subject for Example Name 1 in email sent by EEExtraMailer',
            'Sample subject for Example Name 2 in email sent by EEExtraMailer'
        );

        for ($i = 0; $i< count($contexts); $i++) {
            $this->assertEquals(
                $expectedSubjectsArray[$i],
                $messagesArray[$i]->getSubject()
            );
        }

    }

    public function testGetRenderedSubject()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);

        $template = $this->twig->loadTemplate('demoTXT');


        $subject = $extraMailer->getRenderedSubject($template, array('name' => 'abcdef'));

        $expected = 'Sample subject for abcdef in email sent by EEExtraMailer';

        $this->assertEquals($expected, $subject);
    }


    public function testGetRenderedBodyText()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);

        $template = $this->twig->loadTemplate('demoTXT');


        $subject = $extraMailer->getRenderedBodyText($template, array('name' => 'abcdef'));

        $expected = 'Hi abcdef, This is sample email sent by EEExtraMailer';

        $this->assertEquals($expected, $subject);
    }

    public function testGetRenderedBodyHtml()
    {
        $extraMailer = new ExtraMailer($this->container, $this->swift, $this->twig);

        $template = $this->twig->loadTemplate('demoTXT');


        $subject = $extraMailer->getRenderedBodyHtml($template, array('name' => 'abcdef'));

        $expected = '<p>Hi abcdef, This is sample email sent by EEExtraMailer</p>';

        $this->assertEquals($expected, $subject);
    }
}
