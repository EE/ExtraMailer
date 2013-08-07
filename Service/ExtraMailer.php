<?php

namespace EE\ExtraMailerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param \Swift_Mailer      $mailer
     * @param \Twig_Environment  $twig
     */
    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->container    = $container;
        $this->mailer       = $mailer;
        $this->twig         = $twig;
    }

    /**
     * @param array $recipients one or more, 'email' => 'name'
     * @param       $templateName
     * @param array $context Values to be used during template rendering passed as 'templateVarName' => value
     *                       Can be either one-dimensional in which case all recipients will receive identical messages
     *                       or two-dimensional. In the latter case number of 2nd dimension arrays must match number of
     *                       recipients and each recipient will receive a message according to given variables
     * @param array $options
     *
     * @return int Number of sent emails
     */

    public function sendMessage(Array $recipients, $templateName, $context = array(), $options = array())
    {
        $queuedMessages = $this->composeMessages($recipients, $templateName, $context, $options);

        $count = 0;
        foreach($queuedMessages as $message) {
            $count += $this->mailer->send($message);
        }
        return $count;

    }

    /**
     * @see ExtraMailer::sendMessage
     *
     * @param array $recipients
     * @param       $templateName
     * @param array $context
     * @param array $options
     *
     * @return array
     * @throws \LogicException
     *
     * @todo remove duplicated code
     */
    public function composeMessages(Array $recipients, $templateName, $context = array(), $options = array())
    {
        $queuedMessages = array();

        if (isset($context[0]) && is_array($context[0])) {

            if (count($context) !== count($recipients)) {
                throw new \LogicException('in multicontext mode number of recipients must match number of contexts');
            }

            $orderedRecipients = array();
            foreach($recipients as $email => $name) {
                $orderedRecipients[] = array($email => $name);
            }

            $template = $this->twig->loadTemplate($templateName);

            for ($i = 0; $i < count($recipients); $i++) {
                $subject = $this->getRenderedSubject($template, $context[$i]);

                $message = $this->prepareMessage()
                    ->setSubject($subject)
                    ->setTo($orderedRecipients[$i]);

                $message = $this->prepareContent($message, $template, $context[$i]);

                if (array_key_exists('attachments', $options)) {
                    $message = $this->attach($message, $options['attachments']);
                }

                $queuedMessages[] = $message;
            }

        } else {
            $template = $this->twig->loadTemplate($templateName);

            $subject = $this->getRenderedSubject($template, $context);

            $message = $this->prepareMessage()
                ->setSubject($subject)
                ->setTo($recipients);

            $message = $this->prepareContent($message, $template, $context);


            if (array_key_exists('attachments', $options)) {
                $message = $this->attach($message, $options['attachments']);
            }

            $queuedMessages[] = $message;
        }

        return $queuedMessages;
    }

    /**
     * @return \Swift_Message
     */
    public function prepareMessage()
    {
        return \Swift_Message::newInstance()
            ->setFrom(
                $this->container->getParameter('ee_extra_mailer.from_email.address'),
                $this->container->getParameter('ee_extra_mailer.from_email.sender_name')
            );
    }

    /**
     * @param \Swift_Message $message
     * @param                $template
     * @param array          $context
     *
     * @return \Swift_Message
     */
    public function prepareContent(\Swift_Message $message, $template, array $context)
    {

        $layoutTxt  = $this->twig->loadTemplate('EEExtraMailerBundle::layout.txt.twig');
        $layoutHtml = $this->twig->loadTemplate('EEExtraMailerBundle::layout.html.twig');

        $subject = $this->getRenderedSubject($template, $context);
        $bodyHtml = $this->getRenderedBodyHtml($template, $context);
        $bodyText = $this->getRenderedBodyText($template, $context);

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

        return $message;
    }

    /**
     * @param \Swift_Message $message
     * @param array          $attachments
     *
     * @return \Swift_Message
     */
    public function attach(\Swift_Message $message, array $attachments)
    {
        foreach ($attachments as $fileName => $attachmentWebPath) {
            $message->attach(\Swift_Attachment::fromPath($attachmentWebPath)->setFilename($fileName));
        }

        return $message;
    }

    /**
     * @param       $template
     * @param array $context
     *
     * @return mixed
     */
    public function getRenderedSubject($template, array $context)
    {
        return $template->renderBlock('subject', $context);
    }

    /**
     * @param       $template
     * @param array $context
     *
     * @return mixed
     */
    public function getRenderedBodyText($template, array $context)
    {
        return $template->renderBlock('body_text', $context);
    }

    /**
     * @param       $template
     * @param array $context
     *
     * @return mixed
     */
    public function getRenderedBodyHtml($template, array $context)
    {
        return $template->renderBlock('body_html', $context);
    }
}
