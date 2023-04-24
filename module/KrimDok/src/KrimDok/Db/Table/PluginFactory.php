<?php

namespace KrimDok\Db\Table;

class PluginFactory extends \TueFind\Db\Table\PluginFactory {
    public function __construct()
    {
        $this->defaultNamespace = 'KrimDok\Db\Table';
    }
}
