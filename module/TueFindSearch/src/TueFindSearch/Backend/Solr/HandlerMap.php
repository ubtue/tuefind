<?php

namespace TueFindSearch\Backend\Solr;

use VuFindSearch\ParamBag;

class HandlerMap extends \VuFindSearch\Backend\Solr\HandlerMap {
    protected function apply(
        ParamBag $params,
        array $defaults,
        array $appends,
        array $invariants
    ) {
        parent::apply($params, $defaults, $appends, $invariants);

        // We cannot put this logic to QueryBuilder since QueryBuilder
        // will only be executed if there is an actual query.
        // The changes here will also affect e.g. if only fields for a single record
        // are queried, e.g. when searching for record versions within the record full view.
        $final = $params->getArrayCopy();

        // Disable our custom multiLanguageQueryParser plugin (potentially on several occasions)
        if (isset($final['defType']) && in_array('multiLanguageQueryParser', $final['defType'])) {
            // When searching for other record versions using work_keys_str_mv
            if (isset($final['q'][0]) && strpos($final['q'][0], 'work_keys_str_mv') !== false) {
                unset($final['defType']);
            }
        }

        $params->exchangeArray($final);
    }
}
