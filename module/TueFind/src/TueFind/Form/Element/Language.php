<?php

namespace TueFind\Form\Element;

use Symfony\Component\Intl\Languages;

const _6392T_to_6392B_map = [ 'sqi' => 'alb', 'hye' => 'arm', 'eus' => 'baq', 'bod' => 'tib', 'mya' => 'bur', 'ces' => 'cze', 'zho' => 'chi',
                               'cym' => 'wel', 'deu' => 'ger', 'nld' => 'dut', 'ell' => 'gre', 'eus' => 'baq', 'fas' => 'per', 'fra' => 'fre',
                               'kat' => 'geo', 'isl' => 'ice', 'mkd' => 'mac', 'mri' => 'mao', 'msa' => 'may', 'mya' => 'bur', 'ron' => 'rum',
                               'slk' => 'slo', 'zho' => 'chi' ];


class Language extends \Laminas\Form\Element\Select
{
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $languageCodesAndNames = Languages::getAlpha3Names();
        $languageCodesAndNames = array_map(function ($code, $name) {
            $code = array_key_exists($code, _6392T_to_6392B_map) ? _6392T_to_6392B_map[$code] : $code;
            $selected = $code === 'ger' ? true : false;
            return ['value' => $code, 'label' => $name . ' (' . $code . ')', 'selected' => $selected];
        }, array_keys($languageCodesAndNames), $languageCodesAndNames);

        $this->setValueOptions($languageCodesAndNames);
    }
}
