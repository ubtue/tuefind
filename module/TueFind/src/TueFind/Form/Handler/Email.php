<?php

namespace TueFind\Form\Handler;

use Laminas\Mail\Address;
use VuFind\Db\Entity\UserEntityInterface;
use VuFind\Exception\Mail as MailException;

class Email extends \VuFind\Form\Handler\Email {
    public function handle(
        \VuFind\Form\Form $form,
        \Laminas\Mvc\Controller\Plugin\Params $params,
        ?UserEntityInterface $user = null
    ): bool {
        $fields = $form->mapRequestParamsToFieldValues($params->fromPost());
        $emailMessage = $this->viewRenderer->partial(
            'Email/form.phtml',
            compact('fields')
        );

        [$senderName, $senderEmail] = $this->getSender($form);

        $replyToName = $params->fromPost(
            'name',
            $user ? trim($user->firstname . ' ' . $user->lastname) : null
        );
        $replyToEmail = $params->fromPost(
            'email',
            $user ? $user->email : null
        );

        // TueFind: Deny Spam from @ixtheo.de and other addresses
        if (preg_match('"@ixtheo.de$"i', $replyToEmail)) {
            $this->logError(
                "Invalid reply-to address (spam?): '$replyToEmail'"
            );
            return false;
        }

        $recipients = $form->getRecipient($params->fromPost());
        $emailSubject = $form->getEmailSubject($params->fromPost());

        $result = true;
        foreach ($recipients as $recipient) {
            $success = $this->sendEmail(
                $recipient['name'],
                $recipient['email'],
                $senderName,
                $senderEmail,
                $replyToName,
                $replyToEmail,
                $emailSubject,
                $emailMessage,
                /*TueFind: $enableSpamfilter=*/true
            );

            $result = $result && $success;
        }
        return $result;
    }

    /**
     * Send form data as email.
     *
     * @param string $recipientName    Recipient name
     * @param string $recipientEmail    Recipient email
     * @param string $senderName        Sender name
     * @param string $senderEmail       Sender email
     * @param string $replyToName       Reply-to name
     * @param string $replyToEmail      Reply-to email
     * @param string $emailSubject      Email subject
     * @param string $emailMessage      Email message
     * @param bool   $enableSpamfilter  TueFind: Enable Spamfilter
     *
     * @return bool
     */
    protected function sendEmail(
        $recipientName,
        $recipientEmail,
        $senderName,
        $senderEmail,
        $replyToName,
        $replyToEmail,
        $emailSubject,
        $emailMessage,
        $enableSpamfilter = false
    ): bool {
        try {
            $this->mailer->send(
                new Address($recipientEmail, $recipientName),
                new Address($senderEmail, $senderName),
                $emailSubject,
                $emailMessage,
                null,
                !empty($replyToEmail)
                    ? new Address($replyToEmail, $replyToName) : null,
                $enableSpamfilter
            );
            return true;
        } catch (MailException $e) {
            $this->logError(
                "Failed to send email to '$recipientEmail': " . $e->getMessage()
            );
            return false;
        }
    }
}
