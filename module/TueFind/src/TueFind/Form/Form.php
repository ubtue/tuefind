<?php

namespace TueFind\Form;

use Laminas\View\HelperPluginManager;
use VuFind\Config\YamlReader;
use VuFind\Form\Handler\PluginManager as HandlerManager;


class Form extends \VuFind\Form\Form {
    public $defaultSiteConfig;

    // to map a form id to its (optional existing) config key in local overrides
    protected $emailReceiverLocalOverridesConfigKeys = ['AcquisitionRequest' => 'acquisition_request_receivers'];

    public function __construct(YamlReader $yamlReader, HelperPluginManager $viewHelperManager, HandlerManager $handlerManager,
                                array $defaultFeedbackConfig = null, array $defaultSiteConfig = null)
    {
        parent::__construct($yamlReader, $viewHelperManager, $handlerManager, $defaultFeedbackConfig);
        $this->defaultSiteConfig = $defaultSiteConfig;
    }

    public function getRecipient($postParams = null)
    {
        $recipient = $this->formConfig['recipient'] ?? [null];
        $recipients = isset($recipient['email']) || isset($recipient['name'])
            ? [$recipient] : $recipient;

        $formId = $this->formConfig['id'];
        foreach ($recipients as &$recipient) {
            $recipientEmail = $recipient['email'] ?? null;

            // TueFind: local overrides / special forms
            if (!isset($recipient['email']) && isset($this->emailReceiverLocalOverridesConfigKeys[$formId])) {
                $configKey = $this->emailReceiverLocalOverridesConfigKeys[$formId];
                if (isset($this->defaultSiteConfig[$configKey]))
                    $recipient['email'] = $this->defaultSiteConfig[$configKey];
            }

            // TueFind: local overrides / general email address
            $recipient['email'] = $recipient['email']
                ?? $this->defaultFormConfig['recipient_email'] ?? $this->defaultSiteConfig['email'] ?? null;

            $recipient['name'] = $recipient['name']
                ?? $this->defaultFormConfig['recipient_name'] ?? null;
        }

        return $recipients;
    }

    /**
     * We override this function in TueFind so we can also add placeholders (tokens)
     * which will be injected into the translation via the default mechanism.
     */
    public function getDisplayString($translationKey, $escape = null, $tokens=[])
    {
        $escape ??= substr($translationKey, -5) !== '_html';
        $helper = $this->viewHelperManager->get($escape ? 'transEsc' : 'translate');
        return $helper($translationKey, $tokens);
    }

    protected function getFormElementClass($type)
    {
        $map = [
            'language' => '\TueFind\Form\Element\Language',
        ];

        return $map[$type] ?? parent::getFormElementClass($type);
    }
}
