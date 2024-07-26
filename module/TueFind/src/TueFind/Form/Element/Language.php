<?php

namespace TueFind\Form\Element;

use Symfony\Component\Intl\Languages;

class Language extends \Laminas\Form\Element\Select {
    public function __construct() {
        parent::__construct(...func_get_args());

        $languageCodesAndNames = Languages::getAlpha3Names();
        sort($languageCodesAndNames, SORT_LOCALE_STRING);

        $this->setValueOptions($languageCodesAndNames);
    }
}
