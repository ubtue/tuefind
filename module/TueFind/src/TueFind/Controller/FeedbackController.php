<?php

namespace TueFind\Controller;

class FeedbackController extends \VuFind\Controller\FeedbackController
{
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

        if (!$this->formWasSubmitted(useCaptcha: $view->useCaptcha)) {
            $form = $this->prefillUserInfo($form, $user);
            return $view;
        }

        if (!$form->isValid()) {
            return $view;
        }

        $primaryHandler = $form->getPrimaryHandler();
        $success = $primaryHandler->handle($form, $params, $user);
        if ($success) {
            $view->setVariable('successMessage', $form->getSubmitResponse());
            $view->setTemplate('feedback/response');
        } else {
            $this->flashMessenger()->addErrorMessage(
                $this->translate('could_not_process_feedback')
            );
        }

        $handlers = $form->getSecondaryHandlers();
        foreach ($handlers as $handler) {
            try {
                $handler->handle($form, $params, $user);
            } catch (\Exception $e) {
                $this->logError($e->getMessage());
            }
        }

        return $view;
    }
}
