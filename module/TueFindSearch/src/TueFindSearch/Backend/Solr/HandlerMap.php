<?php

namespace TueFindSearch\Backend\Solr;

use VuFindSearch\ParamBag;

use function in_array;

class HandlerMap extends \VuFindSearch\Backend\Solr\HandlerMap
{
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

        // Since VuFind 11.1 the QueryBuilder sets the dismax handler as "defType"
        // (it used to set "qt"), which silently replaces our default
        // "multiLanguageQueryParser". Without it Solr does not map facets (and
        // other fields) to their language specific variants for the "lang" parameter,
        // e.g. era_facet would show raw MARC codes instead of era_facet_<lang>.
        // multiLanguageQueryParser handles the dismax parameters (qf etc.) itself,
        // so we restore it whenever the handler defines it as default.
        if (
            in_array('multiLanguageQueryParser', (array)($defaults['defType'] ?? []))
            && isset($final['defType'])
            && array_intersect((array)$final['defType'], ['dismax', 'edismax'])
        ) {
            $final['defType'] = ['multiLanguageQueryParser'];
        }

        // Disable our custom multiLanguageQueryParser plugin (potentially on several occasions)
        if (isset($final['defType']) && in_array('multiLanguageQueryParser', $final['defType'])) {
            // When searching for other record versions using work_keys_str_mv
            if (isset($final['q'][0]) && str_contains($final['q'][0], 'work_keys_str_mv')) {
                unset($final['defType']);
            }
        }

        $params->exchangeArray($final);
    }
}
