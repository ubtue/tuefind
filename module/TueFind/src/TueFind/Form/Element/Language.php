<?php

namespace TueFind\Form\Element;

use Symfony\Component\Intl\Languages;

class Language extends \Laminas\Form\Element\Select
{
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $languageCodesAndNames = Languages::getAlpha3Names();
        $languageCodesAndNames = array_map(function ($code, $name) {
            $selected = $code === 'deu' ? true : false;
            return ['value' => $code, 'label' => $name . ' (' . $code . ')', 'selected' => $selected];
        }, array_keys($languageCodesAndNames), $languageCodesAndNames);

        $this->setValueOptions($languageCodesAndNames);
    }
}
