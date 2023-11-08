<?php

namespace TueFind\Controller;

use Laminas\Mail\Address;
use VuFind\Exception\Mail as MailException;

class FeedbackController extends \VuFind\Controller\FeedbackController
{
    /**
     * Handles rendering and submit of dynamic forms.
     * Form configurations are specified in FeedbackForms.yaml.
     *
     * @return mixed
     */
    public function formAction()
    {
        $formId = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
        if (!$formId) {
            $formId = 'FeedbackSite';
        }

        $user = $this->getUser();

        $form = $this->serviceLocator->get($this->formClass);
        $prefill = $this->params()->fromQuery();
        $params = [];
        if ($refererHeader = $this->getRequest()->getHeader('Referer')) {
            $params['referrer'] = $refererHeader->getFieldValue();
        }
        if ($userAgentHeader = $this->getRequest()->getHeader('User-Agent')) {
            $params['userAgent'] = $userAgentHeader->getFieldValue();
        }
        $form->setFormId($formId, $params, $prefill);

        if (!$form->isEnabled()) {
            throw new \VuFind\Exception\Forbidden("Form '$formId' is disabled");
        }

        if (!$user && $form->showOnlyForLoggedUsers()) {
            return $this->forceLogin();
        }

        $view = $this->createViewModel(compact('form', 'formId', 'user'));
        $view->useCaptcha
            = $this->captcha()->active('feedback') && $form->useCaptcha();

        $params = $this->params();
        $form->setData($params->fromPost());

        if (!$this->formWasSubmitted('submit', $view->useCaptcha)) {
            $form = $this->prefillUserInfo($form, $user);
            return $view;
        }

        if (! $form->isValid()) {
            return $view;
        }

        [$messageParams, $template]
            = $form->formatEmailMessage($this->params()->fromPost());
        $emailMessage = $this->getViewRenderer()->partial(
            $template,
            ['fields' => $messageParams]
        );
        $emailMessage .= "----------------------------------------------------------------------------------------------\n";
        $emailMessage .= "Aktuelle Seite: " . $this->getRequest()->getHeaders("Referer")->getUri() . "\n";
        $emailMessage .= "Browser:        " . htmlentities($this->getRequest()->getHeaders("User-Agent")->getFieldValue()) . "\n";
        $emailMessage .= "Cookies:        " . htmlentities($this->getRequest()->getCookie()->getFieldValue()) . "\n";
        $emailMessage .= "----------------------------------------------------------------------------------------------\n\n";

        [$senderName, $senderEmail] = $this->getSender($form);

        $replyToName = $params->fromPost(
            'name',
            $user ? trim($user->firstname . ' ' . $user->lastname) : null
        );
        $replyToEmail = $params->fromPost(
            'email',
            $user ? $user->email : null
        );

        // TueFind: Deny Spam-Mails from @ixtheo.de and other addresses
        if (preg_match('"@ixtheo.de$"i', $replyToEmail)) {
            $this->showResponse($view, $form, false, 'Invalid reply-to address: ' . $replyToEmail);
            return $view;
        }

        $recipients = $form->getRecipient($params->fromPost());

        $emailSubject = $form->getEmailSubject($params->fromPost());

        $sendSuccess = true;
        foreach ($recipients as $recipient) {
            [$success, $errorMsg] = $this->sendEmail(
                $recipient['name'],
                $recipient['email'],
                $senderName,
                $senderEmail,
                $replyToName,
                $replyToEmail,
                $emailSubject,
                $emailMessage,
                /*$enableSpamfilter=*/true
            );

            $sendSuccess = $sendSuccess && $success;
            if (!$success) {
                $this->showResponse(
                    $view,
                    $form,
                    false,
                    $errorMsg
                );
            }
        }

        if ($sendSuccess) {
            $this->showResponse($view, $form, true);
        }

        return $view;
    }

    /**
     * Send form data as email.
     *
     * @param string $recipientName    Recipient name
     * @param string $recipientEmail   Recipient email
     * @param string $senderName       Sender name
     * @param string $senderEmail      Sender email
     * @param string $replyToName      Reply-to name
     * @param string $replyToEmail     Reply-to email
     * @param string $emailSubject     Email subject
     * @param string $emailMessage     Email message
     * @param bool   $enableSpamfilter TueFind: Enable Spamfilter
     *
     * @return array with elements success:boolean, errorMessage:string (optional)
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
    ) {
        try {
            $mailer = $this->serviceLocator->get(\VuFind\Mailer\Mailer::class);
            $mailer->send(
                new Address($recipientEmail, $recipientName),
                new Address($senderEmail, $senderName),
                $emailSubject,
                $emailMessage,
                null,
                !empty($replyToEmail)
                    ? new Address($replyToEmail, $replyToName) : null,
                $enableSpamfilter
            );
            return [true, null];
        } catch (MailException $e) {
            return [false, $e->getMessage()];
        }
        return $form;
    }
}
