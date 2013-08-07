<?php

namespace EE\ExtraMailerBundle\Service;

class ExtraMailer
{

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var \Twig_Environment
     */
    protected $twig;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->container    = $container;
        $this->mailer       = $mailer;
        $this->twig         = $twig;
    }

    /**
     * @param array $toEmail
     * @param string $templateName
     * @param array $context
     * @param array $options
     * @return bool
     */
    public function sendMessage(Array $toEmail, $templateName, $context = array(), $options = array())
    {
        $template = $this->twig->loadTemplate($templateName);

        $layoutTxt  = $this->twig->loadTemplate('EEExtraMailerBundle::layout.txt.twig');
        $layoutHtml = $this->twig->loadTemplate('EEExtraMailerBundle::layout.html.twig');

        $subject  = $template->renderBlock('subject', $context);
        $bodyText = $template->renderBlock('body_text', $context);
        $bodyHtml = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(
                $this->container->getParameter('ee_extra_mailer.from_email.address'),
                $this->container->getParameter('ee_extra_mailer.from_email.sender_name')
            )
            ->setTo($toEmail);

        if (!empty( $bodyHtml )) {

            $message->setBody(
                $layoutHtml->render(
                    array(
                        'subject'   => $subject,
                        'body_html' => $bodyHtml
                    )
                ),
                'text/html'
            )->addPart(
                $layoutTxt->render(
                    array(
                        'subject'   => $subject,
                        'body_text' => $bodyText
                    )
                ),
                'text/plain'
            );
        } else {
            $message->setBody(
                $layoutTxt->render(
                    array(
                        'subject'   => $subject,
                        'body_text' => $bodyText
                    )
                )
            );
        }

        if (isset($options['attachments'] )) {
            foreach ($options['attachments'] as $fileName => $attachmentWebPath) {
                $message->attach(\Swift_Attachment::fromPath($attachmentWebPath)->setFilename($fileName));
            }
        }

        $this->mailer->send($message);

        return true;
    }
}
