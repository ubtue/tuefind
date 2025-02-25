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
    protected function getAttachmentName($formId, $fields) {
        $name_candidate = strtolower(substr($formId, strlen("SelfArchiving"), strlen($formId) - 1));
        // articles can be contained in a journal or an anthology, so proper template has to be chosen
        if ($name_candidate == 'aufsatz') {
            foreach ($fields as $data) {
                if ($data['name'] != 'inwerkradio')
                    continue;
                if ($data['value'] == 'journal')
                   return $name_candidate . '_zs';
                if ($data['value'] == 'anthology')
                   return $name_candiate . '_sb';
            }
        }
        return $name_candidate;
    }

    protected function handleSelfArchivingForms($formId, $emailMessage, $fields) {

       if ($formId != "SelfArchivingMonographie" &&
           $formId != "SelfArchivingAufsatz" &&
           $formId != "SelfArchivingRezension" &&
           $formId != "SelfArchivingLexikonartikel")
               return $emailMessage;

       $newEmailMessage = new MimeMessage();

       $body_ = '';
       $title_ = '';
       $sub_title_ = '';

       foreach ($fields as $data) {
           if ($data['name'] == 'title') {
               $title_ = trim($data['value']);
           }

           if ($data['name'] == 'untertitel') {
               $sub_title_ = trim($data['value']);
           }

           if ($data['name'] == 'name' && trim($data['value']) != '') {
               $body_ .= ("Sender: " . trim($data['value']) . PHP_EOL);
           }

           if ($data['name'] == 'email' && trim($data['value']) != '') {
               $body_ .= ("email: " . trim($data['value']) . PHP_EOL);
           }

           if ($data['name'] == 'comment' && trim($data['value']) != '') {
               $body_ .= ("comment: " . trim($data['value']) . PHP_EOL);
           }
       }

       $emailSubject = $title_ . ($sub_title_ != '' ? " (Subtitle: $sub_title_)" : '');

       $email_body = new MimePart($body_);
       $email_body->type = Mime::TYPE_TEXT;
       $email_body->charset = 'utf-8';
       $email_body->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

       $attachment_name = $this->getAttachmentName($formId, $fields);
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
       return $newEmailMessage;
    }


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
        if ($formId)
            $emailMessage = $this->handleSelfArchivingForms($formId, $emailMessage, $fields);

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
