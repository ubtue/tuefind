<?php

namespace TueFind\Controller;

use VuFind\Exception\Mail as MailException;

class CartController extends \VuFind\Controller\CartController {

    public function emailAction()
    {
        // Retrieve ID list:
        $ids = $this->getSelectedIds();

        // Retrieve follow-up information if necessary:
        if (!is_array($ids) || empty($ids)) {
            $ids = $this->followup()->retrieveAndClear('cartIds') ?? [];
        }
        $actionLimit = $this->getBulkActionLimit('email');
        if (!is_array($ids) || empty($ids)) {
            if ($redirect = $this->redirectToSource('error', 'bulk_noitems_advice')) {
                return $redirect;
        }
            $submitDisabled = true;
        } elseif (count($ids) > $actionLimit) {
            $errorMsg = $this->translate(
                'bulk_limit_exceeded',
                ['%%count%%' => count($ids), '%%limit%%' => $actionLimit],
            );
            if ($redirect = $this->redirectToSource('error', $errorMsg)) {
                return $redirect;
            }
            $submitDisabled = true;
        }

        // Force login if necessary:
        $config = $this->getConfig();
        if (
            (!isset($config->Mail->require_login) || $config->Mail->require_login)
            && !$this->getUser()
        ) {
            return $this->forceLogin(
                null,
                ['cartIds' => $ids, 'cartAction' => 'Email']
            );
        }

        // TueFind: add site title
        $view = $this->createEmailViewModel(
            null, $this->translate('bulk_email_title', [ '%%siteTitle%%' => $config->Site->title ])
        );
        $view->records = $this->getRecordLoader()->loadBatch($ids);
        // Set up Captcha
        $view->useCaptcha = $this->captcha()->active('email');

        // Process form submission:
        if (!($submitDisabled ?? false) && $this->formWasSubmitted(useCaptcha: $view->useCaptcha)) {
            // Build the URL to share:
            $params = [];
            foreach ($ids as $current) {
                $params[] = urlencode('id[]') . '=' . urlencode($current);
            }
            $url = $this->getServerUrl('records-home') . '?' . implode('&', $params);

            // Attempt to send the email and show an appropriate flash message:
            try {
                // If we got this far, we're ready to send the email:
                $mailer = $this->serviceLocator->get(\VuFind\Mailer\Mailer::class);
                $mailer->setMaxRecipients($view->maxRecipients);
                $cc = $this->params()->fromPost('ccself') && $view->from != $view->to
                    ? $view->from : null;
                $mailer->sendLink(
                    $view->to,
                    $view->from,
                    $view->message,
                    $url,
                    $this->getViewRenderer(),
                    $view->subject,
                    $cc
                );
                return $this->redirectToSource('success', 'bulk_email_success', true);
            } catch (MailException $e) {
                $this->flashMessenger()->addMessage($e->getDisplayMessage(), 'error');
            }
        }

        return $view;
    }

}