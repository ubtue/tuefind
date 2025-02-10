<?php

namespace TueFind\Form\Handler;

use Laminas\Mail\Address;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;
use Laminas\Mime\Mime;
use VuFind\Db\Entity\UserEntityInterface;
use VuFind\Exception\Mail as MailException;

class Email extends \VuFind\Form\Handler\Email
{
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

        $formId = $params->fromRoute('id', $params->fromQuery('id'));
        if ($formId == "SelfArchivingMonographie" || $formId == "SelfArchivingAufsatz" || $formId == "SelfArchivingRezension" || $formId == "SelfArchivingLexikonartikel") {
            $newEmailMessage = new MimeMessage();

            $body_ = '';
            $title_ = '';
            $sub_title_ = '';

            foreach ($fields as $data) {
                if ($data['name'] == 'title' && trim($data['value']) != '') {
                    $title_ = $data['value'];
                }

                if ($data['name'] == 'untertitel' && trim($data['value']) != '') {
                    $sub_title_ = $data['value'];
                }

                if ($data['name'] == 'name' && $data['value'] != '') {
                    $body_ .= ("Sender: " . $data['value'] . PHP_EOL);
                }

                if ($data['name'] == 'email' && $data['value'] != '') {
                    $body_ .= ("email: " . $data['value'] . PHP_EOL);
                }


            }

            $emailSubject = $title_ . ($sub_title_ != '' ? " (Subtitle: $sub_title_)" : '');

            $email_body = new MimePart($body_);
            $email_body->type = Mime::TYPE_TEXT;
            $email_body->charset = 'utf-8';
            $email_body->encoding = Mime::ENCODING_QUOTEDPRINTABLE;


            $attachment_name = strtolower(substr($formId, strlen("SelfArchiving"), strlen($formId) - 1));
            $attachment = new MimePart($this->viewRenderer->partial(
                'Email/form-feedback-self-archiving.phtml',
                compact('fields')
            ));
            $attachment->type = Mime::TYPE_TEXT;
            $attachment->charset = 'utf-8';
            $attachment->filename = "$attachment_name.txt";
            $attachment->description = "$attachment_name.txt";
            $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

            $newEmailMessage->setParts([$email_body, $attachment]);
            $emailMessage = $newEmailMessage;

        }



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
                /*TueFind: $enableSpamfilter=*/
                true
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
