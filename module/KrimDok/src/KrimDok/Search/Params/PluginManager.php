<?php

namespace KrimDok\Search\Params;

class PluginManager extends \TueFind\Search\Params\PluginManager {
    protected function _addAliasesAndFactories()
    {
        parent::_addAliasesAndFactories();
        $this->aliases['solr'] = \KrimDok\Search\Solr\Params::class;
    }
}