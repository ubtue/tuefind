<?php

namespace TueFind\Form\Handler;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart as DataPart;
use VuFind\Db\Entity\UserEntityInterface;
use VuFind\Exception\Mail as MailException;

use function strlen;

class Email extends \VuFind\Form\Handler\Email
{
    protected function getAttachmentName($formId, $fields)
    {
        $name_candidate = strtolower(substr($formId, strlen('SelfArchiving'), strlen($formId) - 1));
        // articles can be contained in a journal or an anthology, so proper template has to be chosen
        if (preg_match('/(aufsatz|rezension)/', $name_candidate)) {
            foreach ($fields as $data) {
                if ($data['name'] != 'inwerkradio') {
                    continue;
                }
                if ($data['value'] == 'journal') {
                    return $name_candidate . '_zs';
                }
                if ($data['value'] == 'anthology') {
                    return $name_candidate . '_sb';
                }
            }
        }
        return $name_candidate;
    }

    protected function isSelfArchivingForm($formId)
    {
        return preg_match('/SelfArchiving(Monographie|Aufsatz|Rezension|Lexikonartikel)/', $formId);
    }

    protected function handleSelfArchivingForms($formId, $emailMessage, $fields)
    {
        if (!$this->isSelfArchivingForm($formId)) {
            return $emailMessage;
        }

        $body = '';
        $title = '';
        $subTitle = '';

        foreach ($fields as $data) {
            if ($data['name'] === 'title') {
                $title = trim($data['value']);
            }

            if ($data['name'] === 'untertitel') {
                $subTitle = trim($data['value']);
            }

            if ($data['name'] === 'name' && trim($data['value']) !== '') {
                $body .= 'Sender: ' . trim($data['value']) . PHP_EOL;
            }

            if ($data['name'] === 'email' && trim($data['value']) !== '') {
                $body .= 'email: ' . trim($data['value']) . PHP_EOL;
            }

            if ($data['name'] === 'comment' && trim($data['value']) !== '') {
                $body .= 'comment: ' . trim($data['value']) . PHP_EOL;
            }
        }

        $attachmentName = $this->getAttachmentName($formId, $fields) . '.txt';

        $attachmentContent = $this->viewRenderer->render(
            'Email/form-feedback-self-archiving.phtml',
            compact('fields')
        );

        // Build a proper Symfony Email object instead of hand-crafting MIME
        $email = $this->mailer->getNewMessage();

        // Body of the message
        $email->text($body);

        // Text file attachment
        $email->addPart(
            new DataPart(
                $attachmentContent,
                $attachmentName,
                'text/plain'
            )
        );

        return $email;
    }

    public function handle(
        \VuFind\Form\Form $form,
        \Laminas\Mvc\Controller\Plugin\Params $params,
        ?UserEntityInterface $user = null
    ): bool {
        $postParams = $params->fromPost();
        $fields = $form->mapRequestParamsToFieldValues($postParams);

        $emailMessage = $this->viewRenderer->render(
            'Email/form.phtml',
            compact('fields')
        );

        [$senderName, $senderEmail] = $this->getSender($form);

        $replyToName = $params->fromPost(
            'name',
            $user ? trim($user->getFirstname() . ' ' . $user->getLastname()) : ''
        );
        $replyToEmail = $params->fromPost('email', $user?->getEmail());

        // TueFind: Deny Spam from @ixtheo.de and other addresses
        if (preg_match('"@ixtheo.de$"i', (string)$replyToEmail)) {
            $this->logError("Invalid reply-to address (spam?): '$replyToEmail'");
            return false;
        }

        $recipients = $form->getRecipient($postParams);
        $emailSubject = $form->getEmailSubject($postParams);

        $formId = $params->fromRoute('id', $params->fromQuery('id'));
        $suppressSpamfilter = false;
        if ($formId) {
            $emailMessage = $this->handleSelfArchivingForms($formId, $emailMessage, $fields);
            $suppressSpamfilter = $this->isSelfArchivingForm($formId);
        }

        $result = true;
        foreach ($recipients as $recipient) {
            if (!empty($recipient['email'])) {
                $success = $this->sendEmail(
                    $recipient['name'] ?? '',
                    $recipient['email'],
                    $senderName,
                    $senderEmail,
                    $replyToName,
                    $replyToEmail,
                    $emailSubject,
                    $emailMessage,
                    /*TueFind: $enableSpamfilter=*/
                    $suppressSpamfilter ? false : true
                );
            } else {
                $this->logError('Form recipient email missing; check recipient_email in config.ini.');
                $success = false;
            }

            $result = $result && $success;
        }
        return $result;
    }

    /**
     * Send form data as email.
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
        $tuefindSpamfilter = true
    ): bool {
        try {
            $this->mailer->send(
                new Address($recipientEmail, $recipientName ?? ''),
                new Address($senderEmail, $senderName),
                $emailSubject,
                $emailMessage,
                null,
                !empty($replyToEmail) ? new Address($replyToEmail, $replyToName) : null,
                false,
                [],
                $tuefindSpamfilter
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
