<?php

namespace TueFind\Form;


use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Callback;
use Laminas\Validator\Identical;
use Laminas\View\HelperPluginManager;
use VuFind\Config\YamlReader;
use Laminas\InputFilter\InputFilterInterface;
use VuFind\Form\Handler\PluginManager as HandlerManager;

class Form extends \VuFind\Form\Form
{
    public $defaultSiteConfig;

    // to map a form id to its (optional existing) config key in local overrides
    protected $emailReceiverLocalOverridesConfigKeys = ['AcquisitionRequest' => 'acquisition_request_receivers'];

    public function __construct(
        YamlReader $yamlReader,
        HelperPluginManager $viewHelperManager,
        HandlerManager $handlerManager,
        array $defaultFeedbackConfig = null,
        array $defaultSiteConfig = null
    ) {
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
                if (isset($this->defaultSiteConfig[$configKey])) {
                    $recipient['email'] = $this->defaultSiteConfig[$configKey];
                }
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
    public function getDisplayString($translationKey, $escape = null, $tokens = [])
    {
        $escape ??= substr($translationKey, -5) !== '_html';
        $helper = $this->viewHelperManager->get($escape ? 'transEsc' : 'translate');
        return $helper($translationKey, $tokens);
    }

    protected function getFormElementClass($type)
    {
        $map = [
            'language' => '\TueFind\Form\Element\Language',
            'multifieldtext' => '\Laminas\Form\Element\Text',
            'inclusiveSelect' => '\Laminas\Form\Element\Radio'
        ];

        return $map[$type] ?? parent::getFormElementClass($type);
    }

    protected function getFormElementSettingFields()
    {
        return [
            'format',
            'group',
            'help',
            'inputType',
            'maxValue',
            'minValue',
            'placeholder',
            'required',
            'requireOne',
            'value',
            'maxMultiFieldtext',
            'groupMultiFieldtext'
        ];
    }

    protected function getFormElements($config)
    {
        $elements = [];
        foreach ($config['fields'] as $field) {
            if (!isset($field['type'])) {
                continue;
            }
            if ($field['type'] == "multifieldtext") {
                $max_ = isset($field['maxMultiFieldtext']) ? $field['maxMultiFieldtext'] : 1;
                for ($i = 1; $i <= $max_; $i++) {
                    $new_field = $field;
                    $new_field['name'] .= "$i";
                    $new_field['required'] = isset($new_field['required']) ? ($i == 1 ? $new_field['required'] : 0) : 0;
                    $new_field['groupMultiFieldtext'] = "multifieldtext_group" . $field['name'];
                    $elements[] = $new_field;
                }
            } else {
                $elements[] = $field;
            }
        }
        return $elements;
    }

    protected function getFormElement($el)
    {
        $type = $el['type'];
        if (!($class = $this->getFormElementClass($type))) {
            return null;
        }

        $conf = [];
        $conf['name'] = $el['name'];

        $conf['type'] = $class;
        $conf['options'] = [];

        $attributes = [
            'id' => $this->getElementId($el['name']),
            'class' => [$el['settings']['class'] ?? null],
        ];

        if ($type !== 'submit') {
            $attributes['class'][] = 'form-control';
        }

        if (!empty($el['required'])) {
            $attributes['required'] = true;
        }
        if (!empty($el['settings'])) {
            $attributes += $el['settings'];
        }
        // Add aria-label only if not a hidden field and no aria-label specified:
        if (
            !empty($el['label']) && 'hidden' !== $type
            && !isset($attributes['aria-label'])
        ) {
            $attributes['aria-label'] = $this->translate($el['label']);
        }

        switch ($type) {
            case 'checkbox':
                $options = [];
                if (isset($el['options'])) {
                    $options = $el['options'];
                }
                $optionElements = [];
                foreach ($options as $key => $item) {
                    $optionElements[] = [
                        'label' => $this->translate($item['label']),
                        'value' => $key,
                        'attributes' => [
                            'id' => $this->getElementId($el['name'] . '_' . $key),
                        ],
                    ];
                }
                $conf['options'] = ['value_options' => $optionElements];
                break;
            case 'date':
                if (isset($el['minValue'])) {
                    $attributes['min'] = date('Y-m-d', strtotime($el['minValue']));
                }
                if (isset($el['maxValue'])) {
                    $attributes['max'] = date('Y-m-d', strtotime($el['maxValue']));
                }
                break;
            case 'radio':
                $options = [];
                if (isset($el['options'])) {
                    $options = $el['options'];
                }
                $optionElements = [];
                $first = true;
                foreach ($options as $key => $option) {
                    $elemId = $this->getElementId($el['name'] . '_' . $key);
                    $optionElements[] = [
                        'label' => $this->translate($option['label']),
                        'value' => $key,
                        'label_attributes' => ['for' => $elemId],
                        'attributes' => [
                            'id' => $elemId,
                        ],
                        'selected' => $first,
                    ];
                    $first = false;
                }
                $conf['options'] = ['value_options' => $optionElements];
                break;
            case 'inclusiveSelect':
                    $options = [];
                    if (isset($el['options'])) {
                        $options = $el['options'];
                    }
                    $optionElements = [];
                    $first = true;
                    foreach ($options as $key => $option) {
                        $elemId = $this->getElementId($el['name'] . '_' . $key);
                        $optionElements[] = [
                            'label' => $this->translate($option['label']),
                            'value' => $option['value'],
                            'label_attributes' => ['for' => $elemId],
                            'attributes' => [
                                'id' => $elemId,
                            ],
                            'selected' => $first,
                        ];
                        $first = false;
                    }
                    $conf['options'] = ['value_options' => $optionElements];
                    break;
            case 'select':
                if (isset($el['options'])) {
                    $options = $el['options'];
                    foreach ($options as $key => &$option) {
                        $option['value'] = $key;
                    }
                    // Unset reference:
                    unset($option);
                    $conf['options'] = ['value_options' => $options];
                } elseif (isset($el['optionGroups'])) {
                    $groups = $el['optionGroups'];
                    foreach ($groups as &$group) {
                        foreach ($group['options'] as $key => &$option) {
                            $option['value'] = $key;
                        }
                        // Unset reference:
                        unset($key);
                    }
                    // Unset reference:
                    unset($group);
                    $conf['options'] = ['value_options' => $groups];
                }
                break;
            case 'submit':
                $attributes['value'] = $el['label'];
                $attributes['class'][] = 'btn';
                $attributes['class'][] = 'btn-primary';
                break;
        }

        $attributes['class'] = trim(implode(' ', $attributes['class']));
        $conf['attributes'] = $attributes;

        return $conf;
    }

    protected function parseConfig($formId, $config, $params, $prefill)
    {
        $formConfig = [
           'id' => $formId,
           'title' => !empty($config['name']) ?: $formId,
        ];

        foreach ($this->getFormSettingFields() as $key) {
            if (isset($config[$key])) {
                $formConfig[$key] = $config[$key];
            }
        }

        $this->formConfig = $formConfig;

        $prefill = $this->sanitizePrefill($prefill);

        $elements = [];
        $configuredElements = $this->getFormElements($config);

        // Defaults for sender contact name & email fields:
        $senderName = [
            'name' => 'name',
            'type' => 'text',
            'label' => $this->translate('feedback_name'),
            'group' => '__sender__',
        ];
        $senderEmail = [
            'name' => 'email',
            'type' => 'email',
            'label' => $this->translate('feedback_email'),
            'group' => '__sender__',
        ];
        if ($formConfig['senderInfoRequired'] ?? false) {
            $senderEmail['required'] = $senderName['required'] = true;
        }
        if ($formConfig['senderNameRequired'] ?? false) {
            $senderName['required'] = true;
        }
        if ($formConfig['senderEmailRequired'] ?? false) {
            $senderEmail['required'] = true;
        }

        foreach ($configuredElements as $el) {
            $element = [];

            $required = ['type', 'name'];
            $optional = $this->getFormElementSettingFields();
            foreach (
                array_merge($required, $optional) as $field
            ) {
                if (!isset($el[$field])) {
                    continue;
                }
                $value = $el[$field];
                $element[$field] = $value;
            }

            if (
                in_array($element['type'], ['checkbox', 'radio', 'inclusiveSelect'])
                && !isset($element['group'])
            ) {
                $element['group'] = $element['name'];
            }

            $element['label'] = $el['label'] ?? '';

            $elementType = $element['type'];
            if (in_array($elementType, ['checkbox', 'radio', 'select', 'inclusiveSelect'])) {
                if ($options = $this->getElementOptions($el)) {
                    $element['options'] = $options;
                } elseif ($optionGroups = $this->getElementOptionGroups($el)) {
                    $element['optionGroups'] = $optionGroups;
                }
            }

            $settings = [];
            foreach ($el['settings'] ?? [] as $setting) {
                if (!is_array($setting)) {
                    continue;
                }
                // Allow both [key => value] and [key, value]:
                if (count($setting) !== 2) {
                    reset($setting);
                    $settingId = trim(key($setting));
                    $settingVal = trim(current($setting));
                } else {
                    $settingId = trim($setting[0]);
                    $settingVal = trim($setting[1]);
                }
                $settings[$settingId] = $settingVal;
            }
            $element['settings'] = $settings;

            // Merge sender fields with any existing field definitions:
            if ('name' === $element['name']) {
                $element = array_replace_recursive($senderName, $element);
                $senderName = null;
            } elseif ('email' === $element['name']) {
                $element = array_replace_recursive($senderEmail, $element);
                $senderEmail = null;
            }

            if ($elementType == 'textarea') {
                if (!isset($element['settings']['rows'])) {
                    $element['settings']['rows'] = 8;
                }
            }

            if (!empty($prefill[$element['name']])) {
                $element['settings']['value'] = $prefill[$element['name']];
            }

            $elements[] = $element;
        }

        // Add sender fields if they were not merged in the loop above:
        if ($senderName) {
            $elements[] = $senderName;
        }
        if ($senderEmail) {
            $elements[] = $senderEmail;
        }

        if ($this->reportReferrer()) {
            if ($referrer = ($params['referrer'] ?? false)) {
                $elements[] = [
                    'type' => 'hidden',
                    'name' => 'referrer',
                    'settings' => ['value' => $referrer],
                    'label' => 'Referrer',
                ];
            }
        }

        if ($this->reportUserAgent()) {
            if ($userAgent = ($params['userAgent'] ?? false)) {
                $elements[] = [
                    'type' => 'hidden',
                    'name' => 'useragent',
                    'settings' => ['value' => $userAgent],
                    'label' => 'User Agent',
                ];
            }
        }

        $elements[] = [
            'type' => 'submit',
            'name' => 'submitButton',
            'label' => 'Send',
        ];

        return $elements;
    }

    public function getInputFilter(): InputFilterInterface
    {

        $inclusiveSelect = [];
        foreach ($this->getFormElementConfig() as $el) {
            if($el['type'] == 'inclusiveSelect' && isset($this->data[$el['name']])) {
                $inclusiveSelect['activeGroup'] = $this->data[$el['name']];
                foreach($el['options'] as $option) {
                    if($option['value'] != $inclusiveSelect['activeGroup']) {
                        $inclusiveSelect['disabledGroup'][] = $option['value'];
                    }
                }
            }
        }

        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $validators = [
            'email' => [
                'name' => EmailAddress::class,
                'options' => [
                    'message' => $this->getValidationMessage('invalid_email'),
                ],
            ],
            'notEmpty' => [
                'name' => NotEmpty::class,
                'options' => [
                    'message' => [
                        NotEmpty::IS_EMPTY => $this->getValidationMessage('empty'),
                    ],
                ],
            ],
        ];

        $elementObjects = $this->getElements();
        foreach ($this->getFormElementConfig() as $el) {
            $isCheckbox = $el['type'] === 'checkbox';
            $requireOne = $isCheckbox && ($el['requireOne'] ?? false);
            $required = $el['required'] ?? $requireOne;

            if(isset($el['group']) && isset($inclusiveSelect['disabledGroup'])) {
                if (in_array($el['group'], $inclusiveSelect['disabledGroup'])) {
                    $required = false;
                }
            }

            $fieldValidators = [];
            if ($required || $requireOne) {
                $fieldValidators[] = $validators['notEmpty'];
            }

            if ($isCheckbox) {
                if ($requireOne) {
                    $fieldValidators[] = [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($value, $context) use ($el) {
                                return
                                    !empty(
                                        array_intersect(
                                            array_keys($el['options']),
                                            $value
                                        )
                                    );
                            },
                         ],
                    ];
                } elseif ($required) {
                    $fieldValidators[] = [
                        'name' => Identical::class,
                        'options' => [
                            'message' => [
                                Identical::MISSING_TOKEN
                                => $this->getValidationMessage('empty'),
                            ],
                            'strict' => true,
                            'token' => array_keys($el['options']),
                        ],
                    ];
                }
            }

            if ($el['type'] === 'email') {
                $fieldValidators[] = $validators['email'];
            }

            if (in_array($el['type'], ['checkbox', 'radio', 'select'])) {
                // Add InArray validator from element object instance
                $elementObject = $elementObjects[$el['name']];
                $elementSpec = $elementObject->getInputSpecification();
                $fieldValidators
                    = array_merge($fieldValidators, $elementSpec['validators']);
            }

            $inputFilter->add(
                [
                    'name' => $el['name'],
                    'required' => $required,
                    'validators' => $fieldValidators,
                ]
            );
        }

        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }

}