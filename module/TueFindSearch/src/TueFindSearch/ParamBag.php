<?php

namespace TueFindSearch;

class ParamBag extends \VuFindSearch\ParamBag {
    public function add($name, $value)
    {
        parent::add($name, $value);

        // Mitigate Duplicate Parameter Bug which will be fixed in VuFind 10,
        // see also: https://github.com/vufind-org/vufind/pull/3368
        // For some reason this especially happens during sitemap generation
        $this->params[$name] = array_values(array_unique($this->params[$name]));
    }
}
