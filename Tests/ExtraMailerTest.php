<?php

namespace EE\ExtraMailerBundle\Test\Service;

use EE\ExtraMailerBundle\Service\ExtraMailer;

class ExtraMailerTest extends \PHPUnit_Framework_TestCase
{
    function dummy_trans($str)
    {
        return $str;
    }

    private $swift;
    private $container;
    private $templates;
    private $twig;

    protected function setUp()
    {
        $this->swift = $this->getMock('Swift_Mailer', array( 'send' ), array( new \Swift_NullTransport() ));

        $this->container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->container->setParameter('ee_extra_mailer.from_email.address', 'test@example.com');
        $this->container->setParameter('ee_extra_mailer.from_email.sender_name', 'Test Example');

        $this->templates = array(
            'EEExtraMailerBundle::layout.txt.twig'         => file_get_contents(
                __DIR__ . '/../Resources/views/layout.txt.twig'
            ),
            'EEExtraMailerBundle::layout.html.twig'        => file_get_contents(
                __DIR__ . '/../Resources/views/layout.html.twig'
            ),
            'EEExtraMailerBundle::noreplyFooter.html.twig' => file_get_contents(
                __DIR__ . '/../Resources/views/noreplyFooter.html.twig'
            ),
            'EEExtraMailerBundle::noreplyFooter.txt.twig'  => file_get_contents(
                __DIR__ . '/../Resources/views/noreplyFooter.txt.twig'
            ),
            'demo'                                         => file_get_contents(
                __DIR__ . '/../Resources/views/Demo/sample.email.twig'
            ),
            'demoTXT'                                      => '{% block subject %}Sample subject, email send by EEExtraMailer{% endblock %}{% block body_text %}This is sample email send by EEExtraMailer{% endblock %}'
        );

        $this->twig = $this->getMock('Twig_Environment', null, array( new \Twig_Loader_Array( $this->templates ) ));
        $this->twig->addFilter('trans*', new \Twig_Filter_Function( array( &$this, 'dummy_trans' ) ));
    }

    public function testsendMessage()
    {
        $extraMailer = new ExtraMailer( $this->container, $this->swift, $this->twig );
        $this->swift->expects($this->once())->method('send')->will($this->returnValue(0));
        $result = $extraMailer->sendMessage(
            array( 'recipient@example.com' => 'Recipient Name' ),
            'demo',
            array(),
            array( 'attachments' => array( __DIR__ . '/../Resources/views/Demo/sample.email.twig' ) )
        );

        $this->assertTrue($result);
    }

    public function testsendMessageTxt()
    {
        $extraMailer = new ExtraMailer( $this->container, $this->swift, $this->twig );
        $this->swift->expects($this->once())->method('send')->will($this->returnValue(0));
        $result = $extraMailer->sendMessage(
            array( 'recipient@example.com' => 'Recipient Name' ),
            'demoTXT',
            array(),
            array( 'attachments' => array( __DIR__ . '/../Resources/views/Demo/sample.email.twig' ) )
        );

        $this->assertTrue($result);
    }

}
